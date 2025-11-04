<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
    header("Location: ../login.php");
    exit;
}

require '../dbconnection.php';

if (empty($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['role'] = 'admin';
}


$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? '';

$message = '';
$error = '';
$edit_farmer = null;


$allowed_barangays = [];
if ($user_role === 'technician') {
    $stmt = $conn->prepare("SELECT barangay_id FROM technician_barangays WHERE technician_id = ?");
    $stmt->execute([$user_id]);
    $allowed_barangays = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}


if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    try {
        $conn->beginTransaction();
        $conn->prepare("DELETE FROM farms WHERE farmer_id = ?")->execute([$delete_id]);
        $conn->prepare("DELETE FROM farmers WHERE farmer_id = ?")->execute([$delete_id]);
        $conn->commit();
        $message = "✅ Farmer deleted successfully!";
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "❌ Error deleting farmer: " . $e->getMessage();
    }
}


if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $sql = "SELECT f.*, fr.farm_area, fr.tenurial_status, fr.farm_owner_name, fr.farm_owner_cell,
                   fa.street AS farm_street, fa.barangay_id AS farm_barangay_id,
                   ha.street AS home_street, ha.barangay_id AS home_barangay_id
            FROM farmers f
            LEFT JOIN farms fr ON f.farmer_id = fr.farmer_id
            LEFT JOIN addresses ha ON f.address_id = ha.address_id
            LEFT JOIN addresses fa ON fr.address_id = fa.address_id
            WHERE f.farmer_id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$edit_id]);
    $edit_farmer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$edit_farmer) {
        $error = "❌ Farmer not found.";
        $edit_farmer = null;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $farmer_id = $_POST['farmer_id'] ?? null;
    $last_name = trim($_POST['last_name']);
    $first_name = trim($_POST['first_name']);
    $middle_initial = $_POST['middle_initial'] ?? '';
    $birthdate = $_POST['birthdate'] ?: null;
    $place_of_birth = trim($_POST['place_of_birth']);
    $occupation = trim($_POST['occupation']);
    $civil_status = trim($_POST['civil_status']);
    $citizenship = trim($_POST['citizenship']);
    $sex = ucfirst(strtolower($_POST['sex'] ?? 'Male'));
    if (!in_array($sex, ['Male', 'Female'])) $sex = 'Male';
    $cellphone = trim($_POST['cellphone']);
    $home_street = trim($_POST['home_street']);
    $home_barangay_id = (int)($_POST['home_barangay_id'] ?? 0);
    $farm_area = $_POST['farm_area'] ?? 0;
    $tenurial_status = $_POST['tenurial_status'] ?? null;
    $farm_owner_name = $_POST['farm_owner_name'] ?? null;
    $farm_owner_cell = $_POST['farm_owner_cell'] ?? null;
    $farm_street = trim($_POST['farm_street']);
    $farm_barangay_id = (int)($_POST['farm_barangay_id'] ?? 0);

    try {
        $conn->beginTransaction();

        if ($farmer_id) {
            // UPDATE
            $conn->prepare("
                UPDATE addresses a
                JOIN farmers f ON f.address_id = a.address_id
                SET a.street = ?, a.barangay_id = ?
                WHERE f.farmer_id = ?
            ")->execute([$home_street, $home_barangay_id, $farmer_id]);

            $conn->prepare("
                UPDATE farmers
                SET last_name=?, first_name=?, middle_initial=?, birthdate=?, place_of_birth=?,
                    occupation=?, civil_status=?, citizenship=?, sex=?, cellphone=?
                WHERE farmer_id = ?
            ")->execute([$last_name, $first_name, $middle_initial, $birthdate, $place_of_birth,
                         $occupation, $civil_status, $citizenship, $sex, $cellphone, $farmer_id]);

            $conn->prepare("
                UPDATE addresses a
                JOIN farms fr ON fr.address_id = a.address_id
                SET a.street = ?, a.barangay_id = ?
                WHERE fr.farmer_id = ?
            ")->execute([$farm_street, $farm_barangay_id, $farmer_id]);

            $conn->prepare("
                UPDATE farms
                SET farm_area=?, tenurial_status=?, farm_owner_name=?, farm_owner_cell=?
                WHERE farmer_id = ?
            ")->execute([$farm_area, $tenurial_status, $farm_owner_name, $farm_owner_cell, $farmer_id]);

            $_SESSION['last_edited_farmer'] = $farmer_id;
            $message = "✅ Farmer updated successfully!";
        } else {
            // INSERT
            $conn->prepare("INSERT INTO addresses (street, barangay_id) VALUES (?, ?)")->execute([$home_street, $home_barangay_id]);
            $home_address_id = $conn->lastInsertId();

            $id_number = 'FRM-' . date('Ymd') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
            $conn->prepare("
                INSERT INTO farmers (id_number, last_name, first_name, middle_initial, birthdate, place_of_birth,
                                     occupation, civil_status, citizenship, sex, cellphone, address_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ")->execute([$id_number, $last_name, $first_name, $middle_initial, $birthdate, $place_of_birth,
                         $occupation, $civil_status, $citizenship, $sex, $cellphone, $home_address_id]);
            $new_farmer_id = $conn->lastInsertId();

            $conn->prepare("INSERT INTO addresses (street, barangay_id) VALUES (?, ?)")->execute([$farm_street, $farm_barangay_id]);
            $farm_address_id = $conn->lastInsertId();

            $conn->prepare("
                INSERT INTO farms (farmer_id, address_id, farm_area, tenurial_status, farm_owner_name, farm_owner_cell)
                VALUES (?, ?, ?, ?, ?, ?)
            ")->execute([$new_farmer_id, $farm_address_id, $farm_area, $tenurial_status, $farm_owner_name, $farm_owner_cell]);

            $_SESSION['last_edited_farmer'] = $new_farmer_id;
            $message = "✅ Farmer added successfully!";
        }

        $conn->commit();

        // Instead of redirect, reload same page showing the new farmer
        header("Location: manage_farmers.php?recent=1");
        exit;

    } catch (Exception $e) {
        $conn->rollBack();
        $error = "❌ Error saving farmer: " . $e->getMessage();
    }
}


$farmers = [];
if ($user_role === 'technician' && !empty($allowed_barangays)) {
    $placeholders = implode(',', array_fill(0, count($allowed_barangays), '?'));
    $sql = "
        SELECT f.farmer_id, f.id_number, CONCAT(f.last_name, ', ', f.first_name) AS fullname,
               f.birthdate, f.cellphone, b.barangay_name, fr.farm_area, fr.tenurial_status
        FROM farmers f
        LEFT JOIN farms fr ON f.farmer_id = fr.farmer_id
        LEFT JOIN addresses fa ON fr.address_id = fa.address_id
        LEFT JOIN addresses a ON f.address_id = a.address_id
        LEFT JOIN barangays b ON a.barangay_id = b.barangay_id
        WHERE fa.barangay_id IN ($placeholders)
        ORDER BY f.farmer_id DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute($allowed_barangays);
    $farmers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sql = "
        SELECT f.farmer_id, f.id_number, CONCAT(f.last_name, ', ', f.first_name) AS fullname,
               f.birthdate, f.cellphone, b.barangay_name, fr.farm_area, fr.tenurial_status
        FROM farmers f
        LEFT JOIN farms fr ON f.farmer_id = fr.farmer_id
        LEFT JOIN addresses fa ON fr.address_id = fa.address_id
        LEFT JOIN addresses a ON f.address_id = a.address_id
        LEFT JOIN barangays b ON a.barangay_id = b.barangay_id
        ORDER BY f.farmer_id DESC
    ";
    $farmers = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}


$barangay_list = $conn->query("SELECT barangay_id, barangay_name FROM barangays ORDER BY barangay_name")->fetchAll(PDO::FETCH_ASSOC);

/* include header/sidebar below as before */
include 'includes/header.php';
include 'includes/sidebar.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Farmers</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: {
    extend: {
      colors: { primary: '#047857', secondary: '#10B981' }
    }
  }
};
</script>
<script>
function toggleOwnerFields() {
  const val = document.getElementById('tenurial_status').value;
  document.getElementById('ownerFields').style.display = (val === 'Owner') ? 'none' : 'grid';
}
function calcAge() {
  const bday = document.getElementById('birthdate').value;
  if (bday) {
    const dob = new Date(bday);
    const diff = Date.now() - dob.getTime();
    const ageDt = new Date(diff);
    document.getElementById('age').value = Math.abs(ageDt.getUTCFullYear() - 1970);
  }
}
</script>
</head>

<body class="bg-gray-100 text-gray-800 font-sans">
<div class="main-content sm:ml-64 p-4 sm:p-6">

  <!-- Farmers Records Button -->
  <div class="flex justify-end mt-2 sm:mt-4">
    <a href="farmers_records.php" 
       class="bg-secondary text-white px-4 sm:px-5 py-2 rounded-lg hover:bg-green-700 transition text-sm sm:text-base">
      📋 Farmers Records
    </a>
  </div>

  <!-- Page Header -->
  <header class="bg-primary text-white py-3 sm:py-4 px-4 sm:px-6 shadow-md rounded-lg mt-4">
    <h1 class="text-xl sm:text-2xl font-bold">👨‍🌾 Manage Farmers</h1>
  </header>

  <main class="mt-6">
    <!-- Messages -->
    <?php if($message): ?>
      <div class="bg-green-50 border border-green-300 text-green-800 px-4 py-3 rounded mb-4 text-sm sm:text-base"><?= $message ?></div>
    <?php endif; ?>

    <?php if($error): ?>
      <div class="bg-red-50 border border-red-300 text-red-800 px-4 py-3 rounded mb-4 text-sm sm:text-base"><?= $error ?></div>
    <?php endif; ?>

    <!-- FORM -->
    <form method="POST" class="bg-white p-4 sm:p-6 rounded-lg shadow-md mb-10">
      <input type="hidden" name="farmer_id" value="<?= htmlspecialchars($edit_farmer['farmer_id'] ?? '') ?>">

      <h2 class="text-lg font-semibold text-primary mb-3">Personal Information</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <input class="border p-2 rounded" name="last_name" placeholder="Last Name" value="<?= htmlspecialchars($edit_farmer['last_name'] ?? '') ?>" required>
        <input class="border p-2 rounded" name="first_name" placeholder="First Name" value="<?= htmlspecialchars($edit_farmer['first_name'] ?? '') ?>" required>
        <input class="border p-2 rounded" name="middle_initial" placeholder="M.I." value="<?= htmlspecialchars($edit_farmer['middle_initial'] ?? '') ?>">
        <input class="border p-2 rounded" type="date" id="birthdate" name="birthdate" value="<?= htmlspecialchars($edit_farmer['birthdate'] ?? '') ?>" onchange="calcAge()">
        <input class="border p-2 rounded bg-gray-100" id="age" placeholder="Age" readonly>
        <input class="border p-2 rounded" name="place_of_birth" placeholder="Place of Birth" value="<?= htmlspecialchars($edit_farmer['place_of_birth'] ?? '') ?>">
        <input class="border p-2 rounded" name="occupation" placeholder="Occupation" value="<?= htmlspecialchars($edit_farmer['occupation'] ?? '') ?>">
        <select class="border p-2 rounded" name="civil_status">
          <option value="">Civil Status</option>
          <?php foreach(['Single','Married','Widowed','Separated'] as $s): ?>
            <option value="<?= $s ?>" <?= ($edit_farmer['civil_status'] ?? '')==$s?'selected':'' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
        <input class="border p-2 rounded" name="citizenship" placeholder="Citizenship" value="<?= htmlspecialchars($edit_farmer['citizenship'] ?? '') ?>">
        <select class="border p-2 rounded" name="sex">
          <option value="">Sex</option>
          <option value="Male" <?= ($edit_farmer['sex'] ?? '')=='Male'?'selected':'' ?>>Male</option>
          <option value="Female" <?= ($edit_farmer['sex'] ?? '')=='Female'?'selected':'' ?>>Female</option>
        </select>
        <input class="border p-2 rounded" name="cellphone" placeholder="Cellphone" value="<?= htmlspecialchars($edit_farmer['cellphone'] ?? '') ?>">
      </div>

      <h2 class="text-lg font-semibold text-primary mt-6 mb-3">Home Address</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <input class="border p-2 rounded" name="home_street" placeholder="Street" value="<?= htmlspecialchars($edit_farmer['home_street'] ?? '') ?>">
        <select class="border p-2 rounded" name="home_barangay_id" required>
          <option value="">Select Barangay</option>
          <?php foreach($barangay_list as $b): ?>
            <option value="<?= $b['barangay_id'] ?>" <?= ($edit_farmer['home_barangay_id'] ?? '')==$b['barangay_id']?'selected':'' ?>><?= htmlspecialchars($b['barangay_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <h2 class="text-lg font-semibold text-primary mt-6 mb-3">Farm Details</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <input class="border p-2 rounded" type="number" step="0.01" name="farm_area" placeholder="Farm Area (ha)" value="<?= htmlspecialchars($edit_farmer['farm_area'] ?? '') ?>">
        <select class="border p-2 rounded" id="tenurial_status" name="tenurial_status" onchange="toggleOwnerFields()">
          <option value="">Tenurial Status</option>
          <?php foreach(['Owner','Tenant','Lessee','Others'] as $t): ?>
            <option value="<?= $t ?>" <?= ($edit_farmer['tenurial_status'] ?? '')==$t?'selected':'' ?>><?= $t ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div id="ownerFields" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4" <?= (($edit_farmer['tenurial_status'] ?? '')=='Owner')?'style="display:none;"':'' ?>>
        <input class="border p-2 rounded" name="farm_owner_name" placeholder="Farm Owner Name" value="<?= htmlspecialchars($edit_farmer['farm_owner_name'] ?? '') ?>">
        <input class="border p-2 rounded" name="farm_owner_cell" placeholder="Farm Owner Cell" value="<?= htmlspecialchars($edit_farmer['farm_owner_cell'] ?? '') ?>">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
        <input class="border p-2 rounded" name="farm_street" placeholder="Farm Street" value="<?= htmlspecialchars($edit_farmer['farm_street'] ?? '') ?>">
        <select class="border p-2 rounded" name="farm_barangay_id" required>
          <option value="">Select Barangay</option>
          <?php foreach($barangay_list as $b): ?>
            <option value="<?= $b['barangay_id'] ?>" <?= ($edit_farmer['farm_barangay_id'] ?? '')==$b['barangay_id']?'selected':'' ?>><?= htmlspecialchars($b['barangay_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mt-6 flex flex-wrap gap-3">
        <button type="submit" class="bg-primary text-white px-6 py-2 rounded hover:bg-green-700"><?= $edit_farmer ? 'Update Farmer' : 'Add Farmer' ?></button>
        <?php if ($edit_farmer): ?>
          <a href="manage_farmers.php" class="text-gray-600 hover:underline mt-2 sm:mt-0">Cancel</a>
        <?php endif; ?>
      </div>
    </form>

<div class="bg-white mt-6 p-4 rounded-lg shadow-md overflow-x-auto">
  <table class="min-w-full border-collapse text-sm">
    <thead class="bg-primary text-white">
      <tr>
        <th class="px-4 py-2 text-left">ID Number</th>
        <th class="px-4 py-2 text-left">Farmer Name</th>
        <th class="px-4 py-2 text-left">Barangay</th>
        <th class="px-4 py-2 text-left">Birthdate</th>
        <th class="px-4 py-2 text-left">Age</th>
        <th class="px-4 py-2 text-left">Cellphone</th>
        <th class="px-4 py-2 text-left">Farm Area</th>
        <th class="px-4 py-2 text-left">Tenurial Status</th>
        <th class="px-4 py-2 text-center">Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($farmers as $farmer): ?>
      <?php
        // Compute age
        $age = '';
        if (!empty($farmer['birthdate'])) {
            $dob = new DateTime($farmer['birthdate']);
            $now = new DateTime();
            $age = $dob->diff($now)->y;
        }
      ?>
      <tr class="border-b hover:bg-gray-50">
        <td class="px-4 py-2 text-gray-800"><?= htmlspecialchars($farmer['id_number']) ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($farmer['fullname']) ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($farmer['barangay_name'] ?? '—') ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($farmer['birthdate'] ?? '—') ?></td>
        <td class="px-4 py-2"><?= $age !== '' ? $age : '—' ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($farmer['cellphone']) ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($farmer['farm_area'] ?? '0') ?></td>
        <td class="px-4 py-2"><?= htmlspecialchars($farmer['tenurial_status'] ?? '-') ?></td>
        <td class="px-4 py-2 text-center space-x-2">
          <a href="?edit=<?= $farmer['farmer_id'] ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs">✏️ Edit</a>
          <a href="?delete=<?= $farmer['farmer_id'] ?>" onclick="return confirm('Are you sure you want to delete this farmer?')" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">🗑️ Delete</a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
  </main>
</div>
</body>
</html>
