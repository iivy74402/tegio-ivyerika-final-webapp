<?php
session_start();
require '../dbconnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

// Fetch technicians
$stmt = $conn->query("
    SELECT u.user_id, u.username, u.email, u.first_name, u.middle_initial, u.last_name,
           CONCAT(u.first_name, ' ', COALESCE(u.middle_initial, ''), ' ', u.last_name) AS fullname,
           b.barangay_name, tb.barangay_id
    FROM users u
    LEFT JOIN technician_barangays tb ON u.user_id = tb.technician_id
    LEFT JOIN barangays b ON tb.barangay_id = b.barangay_id
    WHERE u.role = 'technician'
    ORDER BY u.user_id DESC
");
$technicians = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch barangays
$barangays = $conn->query("SELECT barangay_id, barangay_name FROM barangays ORDER BY barangay_name")->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Technicians</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="main-content ml-64 p-6">
  <header class="bg-green-700 text-white py-4 px-6 rounded-lg mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold">👨🔧 Manage Technicians</h1>
    <button onclick="openModal()" class="bg-white text-green-700 px-4 py-2 rounded hover:bg-gray-100 transition">➕ Add Technician</button>
  </header>

  <?php if ($message): ?>
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4"><?= $message ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="bg-red-100 text-red-800 p-3 rounded mb-4"><?= $error ?></div>
  <?php endif; ?>

  <div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-lg font-semibold mb-4">Technicians List</h2>
    <table class="w-full border-collapse">
      <thead class="bg-green-700 text-white">
        <tr>
          <th class="px-4 py-2 text-left">Full Name</th>
          <th class="px-4 py-2 text-left">Username</th>
          <th class="px-4 py-2 text-left">Email</th>
          <th class="px-4 py-2 text-left">Assigned Barangay</th>
          <th class="px-4 py-2 text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($technicians as $tech): ?>
          <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-2"><?= htmlspecialchars($tech['fullname']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($tech['username']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($tech['email']) ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($tech['barangay_name'] ?? 'Not Assigned') ?></td>
            <td class="px-4 py-2">
              <div class="flex items-center justify-center gap-2">
                <button onclick='openEditModal(<?= json_encode($tech) ?>)' class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 whitespace-nowrap">✏ Edit</button>
                <form method="POST" action="actions/assign_technician.php" class="flex items-center gap-1 m-0">
                  <input type="hidden" name="technician_id" value="<?= $tech['user_id'] ?>">
                  <select name="barangay_id" class="border rounded p-1 text-sm">
                    <option value="">Select Barangay</option>
                    <?php foreach ($barangays as $b): ?>
                      <option value="<?= $b['barangay_id'] ?>" <?= ($tech['barangay_id'] == $b['barangay_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($b['barangay_name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 whitespace-nowrap">Assign</button>
                </form>
                <?php if ($tech['barangay_id']): ?>
                  <a href="actions/unassign_technician.php?id=<?= $tech['user_id'] ?>" 
                     onclick="return confirm('Unassign this technician?')" 
                     class="bg-yellow-600 text-white px-3 py-1 rounded text-sm hover:bg-yellow-700 whitespace-nowrap inline-block">Unassign</a>
                <?php else: ?>
                  <span class="invisible px-3 py-1 text-sm">Unassign</span>
                <?php endif; ?>
                <a href="actions/delete_technician.php?id=<?= $tech['user_id'] ?>" 
                   onclick="return confirm('Delete this technician?')" 
                   class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 whitespace-nowrap inline-block">Delete</a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div id="technicianModal" class="fixed inset-0 bg-black bg-opacity-40 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg p-6 w-11/12 md:w-1/2 shadow-lg relative">
    <h2 id="modalTitle" class="text-xl font-semibold text-green-700 mb-4">➕ Add New Technician</h2>
    <form method="POST" id="technicianForm" action="actions/add_technician.php" class="space-y-4">
      <input type="hidden" id="user_id" name="user_id">
      <div class="grid grid-cols-3 gap-3">
        <div>
          <label class="text-sm font-medium">First Name</label>
          <input type="text" id="first_name" name="first_name" class="w-full border rounded px-3 py-2" required>
        </div>
        <div>
          <label class="text-sm font-medium">Middle Initial</label>
          <input type="text" id="middle_initial" name="middle_initial" class="w-full border rounded px-3 py-2" maxlength="2">
        </div>
        <div>
          <label class="text-sm font-medium">Last Name</label>
          <input type="text" id="last_name" name="last_name" class="w-full border rounded px-3 py-2" required>
        </div>
      </div>
      <div>
        <label class="text-sm font-medium">Username</label>
        <input type="text" id="username" name="username" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="text-sm font-medium">Email</label>
        <input type="email" id="email" name="email" class="w-full border rounded px-3 py-2" required>
      </div>
      <div id="passwordField">
        <label class="text-sm font-medium">Password</label>
        <input type="password" id="password" name="password" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="text-sm font-medium">Barangay</label>
        <select id="barangay_id" name="barangay_id" class="w-full border rounded px-3 py-2" required>
          <option value="">Select Barangay</option>
          <?php foreach ($barangays as $b): ?>
            <option value="<?= $b['barangay_id'] ?>"><?= htmlspecialchars($b['barangay_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex justify-end gap-2 pt-4">
        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-green-700 text-white rounded hover:bg-green-800">💾 Save</button>
      </div>
    </form>
    <button onclick="closeModal()" class="absolute top-2 right-3 text-gray-400 hover:text-gray-600">✖</button>
  </div>
</div>

<script>
function openModal() {
  document.getElementById('modalTitle').innerText = '➕ Add New Technician';
  document.getElementById('technicianForm').action = 'actions/add_technician.php';
  document.getElementById('technicianForm').reset();
  document.getElementById('user_id').value = '';
  document.getElementById('password').required = true;
  document.getElementById('passwordField').style.display = 'block';
  document.getElementById('technicianModal').classList.remove('hidden');
  document.getElementById('technicianModal').classList.add('flex');
}

function openEditModal(tech) {
  document.getElementById('modalTitle').innerText = '✏ Edit Technician';
  document.getElementById('technicianForm').action = 'actions/edit_technician.php';
  document.getElementById('user_id').value = tech.user_id;
  document.getElementById('first_name').value = tech.first_name || '';
  document.getElementById('middle_initial').value = tech.middle_initial || '';
  document.getElementById('last_name').value = tech.last_name || '';
  document.getElementById('username').value = tech.username;
  document.getElementById('email').value = tech.email;
  document.getElementById('barangay_id').value = tech.barangay_id || '';
  document.getElementById('password').required = false;
  document.getElementById('passwordField').style.display = 'none';
  document.getElementById('technicianModal').classList.remove('hidden');
  document.getElementById('technicianModal').classList.add('flex');
}

function closeModal() {
  document.getElementById('technicianModal').classList.add('hidden');
  document.getElementById('technicianModal').classList.remove('flex');
}
</script>

</body>
</html>
