<?php
session_start();
require '../dbconnection.php';

$message = '';
$error = '';

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? '';

// Get technician's assigned barangays
$allowed_barangays = [];
if ($user_role === 'technician') {
    $stmt = $conn->prepare("SELECT barangay_id FROM technician_barangays WHERE technician_id = ?");
    $stmt->execute([$user_id]);
    $allowed_barangays = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

// --- Barangay list for dropdown ---
$barangayStmt = $conn->query("SELECT barangay_id, barangay_name FROM barangays ORDER BY barangay_name ASC");
$barangays = $barangayStmt->fetchAll(PDO::FETCH_ASSOC);

// --- DELETE FARMER ---
if (isset($_GET['delete'])) {
    $fid = intval($_GET['delete']);
    try {
        $conn->beginTransaction();
        $conn->prepare("DELETE FROM farms WHERE farmer_id=?")->execute([$fid]);
        $conn->prepare("DELETE FROM addresses WHERE address_id IN (SELECT address_id FROM farmers WHERE farmer_id=?)")->execute([$fid]);
        $conn->prepare("DELETE FROM farmers WHERE farmer_id=?")->execute([$fid]);
        $conn->commit();
        $message = "🗑️ Farmer record deleted successfully.";
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "❌ Delete failed: " . $e->getMessage();
    }
}

// --- Pagination & Search ---
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchQuery = "";
$params = [];

// Build WHERE clause for barangay filtering
if ($user_role === 'technician' && !empty($allowed_barangays)) {
    $placeholders = implode(',', array_fill(0, count($allowed_barangays), '?'));
    $searchQuery = "WHERE a.barangay_id IN ($placeholders)";
    $params = $allowed_barangays;
}

if (!empty($search)) {
    $searchQuery .= ($searchQuery ? " AND" : "WHERE") . " (f.first_name LIKE ? OR f.last_name LIKE ? OR f.id_number LIKE ? OR b.barangay_name LIKE ?)";
    $params = array_merge($params, ["%$search%", "%$search%", "%$search%", "%$search%"]);
}

$countSql = "
    SELECT COUNT(*) FROM farmers f
    LEFT JOIN addresses a ON f.address_id = a.address_id
    LEFT JOIN barangays b ON a.barangay_id = b.barangay_id
    $searchQuery
";
$countStmt = $conn->prepare($countSql);
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// --- Get Farmer Records ---
$sql = "
    SELECT 
        f.farmer_id,
        f.id_number,
        CONCAT(f.last_name, ', ', f.first_name, ' ', f.middle_initial) AS fullname,
        f.first_name,
        f.last_name,
        f.middle_initial,
        f.birthdate,
        TIMESTAMPDIFF(YEAR, f.birthdate, CURDATE()) AS age,
        f.cellphone,
        b.barangay_id,
        b.barangay_name,
        fr.farm_area,
        fr.tenurial_status
    FROM farmers f
    LEFT JOIN addresses a ON f.address_id = a.address_id
    LEFT JOIN barangays b ON a.barangay_id = b.barangay_id
    LEFT JOIN farms fr ON f.farmer_id = fr.farmer_id
    $searchQuery
    ORDER BY f.farmer_id DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$farmers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- UPDATE FORM ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_farmer'])) {
    $fid = $_POST['farmer_id'];
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $minit = $_POST['middle_initial'];
    $birthdate = $_POST['birthdate'];
    $cell = $_POST['cellphone'];
    $area = $_POST['farm_area'];
    $tenure = $_POST['tenurial_status'];
    $barangay_id = $_POST['barangay_id'];

    try {
        $conn->beginTransaction();
        $conn->prepare("UPDATE farmers SET first_name=?, last_name=?, middle_initial=?, birthdate=?, cellphone=? WHERE farmer_id=?")
             ->execute([$fname, $lname, $minit, $birthdate, $cell, $fid]);

        $conn->prepare("
            UPDATE addresses a
            JOIN farmers f ON a.address_id = f.address_id
            SET a.barangay_id=?
            WHERE f.farmer_id=?
        ")->execute([$barangay_id, $fid]);

        $conn->prepare("UPDATE farms SET farm_area=?, tenurial_status=? WHERE farmer_id=?")
             ->execute([$area, $tenure, $fid]);

        $conn->commit();
        $message = "✅ Farmer record updated successfully.";
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "❌ Update failed: " . $e->getMessage();
    }
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Farmers Records</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: { extend: { colors: { primary: '#047857', secondary: '#10B981' } } }
};
</script>
</head>

<body class="bg-gray-100 text-gray-800 font-sans">
<div class="main-content ml-64 p-6">

  <!-- HEADER -->
  <header class="bg-primary text-white py-4 px-6 shadow-md rounded-lg flex justify-between items-center">
    <a href="manage_farmers.php" class="bg-white text-primary px-4 py-2 rounded hover:bg-gray-100 transition">⬅ Back</a>
    <h1 class="text-2xl font-bold">📋 Farmers Records</h1>
  </header>

  <!-- SEARCH -->
  <div class="mt-6 flex flex-col md:flex-row justify-between items-center gap-3">
    <form method="GET" class="flex gap-2 w-full md:w-auto">
      <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>"
        placeholder="Search farmer..." class="border border-gray-300 rounded px-3 py-2 w-full md:w-72 focus:ring-green-200 focus:outline-none">
      <button class="bg-primary text-white px-4 py-2 rounded hover:bg-green-800 transition">Search</button>
    </form>
  </div>

  <?php if ($message): ?>
    <div class="mt-4 bg-green-100 text-green-700 px-4 py-2 rounded border border-green-300"><?= $message ?></div>
  <?php elseif ($error): ?>
    <div class="mt-4 bg-red-100 text-red-700 px-4 py-2 rounded border border-red-300"><?= $error ?></div>
  <?php endif; ?>

  <!-- TABLE -->
  <div class="bg-white mt-6 p-4 rounded-lg shadow-md overflow-x-auto">
    <table class="min-w-full border-collapse text-sm">
      <thead class="bg-primary text-white">
        <tr>
          <th class="px-4 py-2 text-left">ID Number</th>
          <th class="px-4 py-2 text-left">Farmer Name</th>
          <th class="px-4 py-2 text-left">Barangay</th>
          <th class="px-4 py-2 hidden md:table-cell">Birthdate</th>
          <th class="px-4 py-2 hidden md:table-cell">Age</th>
          <th class="px-4 py-2 hidden md:table-cell">Cellphone</th>
          <th class="px-4 py-2 hidden md:table-cell">Farm Area</th>
          <th class="px-4 py-2 hidden md:table-cell">Tenurial</th>
          <th class="px-4 py-2 text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!$farmers): ?>
          <tr><td colspan="9" class="text-center py-4 text-gray-500">No farmers found.</td></tr>
        <?php else: ?>
          <?php foreach ($farmers as $farmer): ?>
            <tr class="border-b hover:bg-gray-50">
              <td class="px-4 py-2"><?= htmlspecialchars($farmer['id_number']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($farmer['fullname']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($farmer['barangay_name']) ?></td>
              <td class="px-4 py-2 hidden md:table-cell"><?= htmlspecialchars($farmer['birthdate']) ?></td>
              <td class="px-4 py-2 hidden md:table-cell"><?= htmlspecialchars($farmer['age']) ?></td>
              <td class="px-4 py-2 hidden md:table-cell"><?= htmlspecialchars($farmer['cellphone']) ?></td>
              <td class="px-4 py-2 hidden md:table-cell"><?= htmlspecialchars($farmer['farm_area']) ?></td>
              <td class="px-4 py-2 hidden md:table-cell"><?= htmlspecialchars($farmer['tenurial_status']) ?></td>
              <td class="px-4 py-2 text-center space-y-1 md:space-x-2">
                <button onclick='openModal(<?= json_encode($farmer) ?>)' class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs block md:inline">✏️ Edit</button>
                <a href="?delete=<?= $farmer['farmer_id'] ?>" onclick="return confirm('Delete this farmer?')" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs block md:inline">🗑️ Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-40 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg p-6 w-11/12 md:w-1/2 shadow-lg relative">
    <h2 class="text-xl font-semibold text-primary mb-4">✏️ Edit Farmer Record</h2>
    <form method="POST" class="space-y-3">
      <input type="hidden" id="farmer_id" name="farmer_id">

      <div class="grid md:grid-cols-2 gap-3">
        <div><label class="text-sm font-medium">First Name</label><input type="text" id="first_name" name="first_name" class="w-full border rounded px-3 py-2"></div>
        <div><label class="text-sm font-medium">Last Name</label><input type="text" id="last_name" name="last_name" class="w-full border rounded px-3 py-2"></div>
      </div>

      <div class="grid md:grid-cols-2 gap-3">
        <div><label class="text-sm font-medium">Middle Initial</label><input type="text" id="middle_initial" name="middle_initial" maxlength="1" class="w-full border rounded px-3 py-2"></div>
        <div>
          <label class="text-sm font-medium">Birthdate</label>
          <input type="date" id="birthdate" name="birthdate" class="w-full border rounded px-3 py-2" onchange="updateAge()">
        </div>
      </div>

      <div class="grid md:grid-cols-2 gap-3">
        <div><label class="text-sm font-medium">Age</label><input type="text" id="age" name="age" readonly class="w-full border rounded px-3 py-2 bg-gray-100"></div>
        <div>
          <label class="text-sm font-medium">Barangay</label>
          <select id="barangay_id" name="barangay_id" class="w-full border rounded px-3 py-2">
            <?php foreach ($barangays as $b): ?>
              <option value="<?= $b['barangay_id'] ?>"><?= htmlspecialchars($b['barangay_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div><label class="text-sm font-medium">Cellphone</label><input type="text" id="cellphone" name="cellphone" class="w-full border rounded px-3 py-2"></div>
      <div><label class="text-sm font-medium">Farm Area (ha)</label><input type="text" id="farm_area" name="farm_area" class="w-full border rounded px-3 py-2"></div>

      <div>
        <label class="text-sm font-medium">Tenurial Status</label>
        <select id="tenurial_status" name="tenurial_status" class="w-full border rounded px-3 py-2">
          <option value="Owner">Owner</option>
          <option value="Tenant">Tenant</option>
          <option value="Leaseholder">Leaseholder</option>
          <option value="Others">Others</option>
        </select>
      </div>

      <div class="flex justify-end gap-2 pt-4">
        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
        <button type="submit" name="update_farmer" class="px-4 py-2 bg-primary text-white rounded hover:bg-green-800">💾 Save</button>
      </div>
    </form>
    <button onclick="closeModal()" class="absolute top-2 right-3 text-gray-400 hover:text-gray-600">✖</button>
  </div>
</div>

<script>
function openModal(farmer) {
  const modal = document.getElementById('editModal');
  modal.classList.remove('hidden'); modal.classList.add('flex');
  for (const key in farmer) if (document.getElementById(key)) document.getElementById(key).value = farmer[key] || '';
  updateAge();
}

function closeModal() {
  const modal = document.getElementById('editModal');
  modal.classList.add('hidden'); modal.classList.remove('flex');
}

function updateAge() {
  const bdate = document.getElementById('birthdate').value;
  const ageInput = document.getElementById('age');
  if (!bdate) { ageInput.value = ''; return; }
  const today = new Date();
  const birth = new Date(bdate);
  let age = today.getFullYear() - birth.getFullYear();
  const m = today.getMonth() - birth.getMonth();
  if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
  ageInput.value = age;
}
</script>
</body>
</html>
