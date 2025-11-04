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

// Get search query
$search = $_GET['search'] ?? '';

// Fetch barangays
$sql = "SELECT * FROM barangays";
if ($search) {
    $sql .= " WHERE barangay_name LIKE ?";
    $stmt = $conn->prepare($sql . " ORDER BY barangay_name");
    $stmt->execute(["%$search%"]);
} else {
    $stmt = $conn->query($sql . " ORDER BY barangay_name");
}
$barangays = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $barangay_name = trim($_POST['barangay_name']);
    $stmt = $conn->prepare("INSERT INTO barangays (barangay_name) VALUES (?)");
    $stmt->execute([$barangay_name]);
    $_SESSION['message'] = "✅ Barangay added successfully!";
    header("Location: manage_barangays.php");
    exit;
}

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $barangay_id = $_POST['barangay_id'];
    $barangay_name = trim($_POST['barangay_name']);
    $stmt = $conn->prepare("UPDATE barangays SET barangay_name = ? WHERE barangay_id = ?");
    $stmt->execute([$barangay_name, $barangay_id]);
    $_SESSION['message'] = "✅ Barangay updated successfully!";
    header("Location: manage_barangays.php");
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM barangays WHERE barangay_id = ?");
    $stmt->execute([$_GET['delete']]);
    $_SESSION['message'] = "✅ Barangay deleted successfully!";
    header("Location: manage_barangays.php");
    exit;
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Barangays</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="main-content ml-64 p-6">
  <header class="bg-green-700 text-white py-4 px-6 rounded-lg mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold">📍 Manage Barangays</h1>
    <button onclick="openAddModal()" class="bg-white text-green-700 px-4 py-2 rounded hover:bg-gray-100 transition">➕ Add Barangay</button>
  </header>

  <?php if ($message): ?>
    <div class="bg-green-100 text-green-800 p-3 rounded mb-4"><?= $message ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="bg-red-100 text-red-800 p-3 rounded mb-4"><?= $error ?></div>
  <?php endif; ?>

  <div class="bg-white p-4 rounded-lg shadow-md mb-6">
    <form method="GET" class="flex gap-4">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search barangay..." class="flex-1 border rounded p-2">
      <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">🔍 Search</button>
      <a href="manage_barangays.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Reset</a>
    </form>
  </div>

  <div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-lg font-semibold mb-4">Barangays List</h2>
    <table class="w-full border-collapse">
      <thead class="bg-green-700 text-white">
        <tr>
          <th class="px-4 py-2 text-left">ID</th>
          <th class="px-4 py-2 text-left">Barangay Name</th>
          <th class="px-4 py-2 text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($barangays as $b): ?>
          <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-2"><?= $b['barangay_id'] ?></td>
            <td class="px-4 py-2"><?= htmlspecialchars($b['barangay_name']) ?></td>
            <td class="px-4 py-2 text-center">
              <button onclick='openEditModal(<?= json_encode($b) ?>)' class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">✏ Edit</button>
              <a href="?delete=<?= $b['barangay_id'] ?>" 
                 onclick="return confirm('Delete this barangay?')" 
                 class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700">🗑 Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div id="barangayModal" class="fixed inset-0 bg-black bg-opacity-40 hidden justify-center items-center z-50">
  <div class="bg-white rounded-lg p-6 w-11/12 md:w-1/3 shadow-lg relative">
    <h2 id="modalTitle" class="text-xl font-semibold text-green-700 mb-4">➕ Add Barangay</h2>
    <form method="POST" id="barangayForm" class="space-y-4">
      <input type="hidden" id="barangay_id" name="barangay_id">
      <div>
        <label class="text-sm font-medium">Barangay Name</label>
        <input type="text" id="barangay_name" name="barangay_name" class="w-full border rounded px-3 py-2" required>
      </div>
      <div class="flex justify-end gap-2 pt-4">
        <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
        <button type="submit" id="submitBtn" name="add" class="px-4 py-2 bg-green-700 text-white rounded hover:bg-green-800">💾 Save</button>
      </div>
    </form>
    <button onclick="closeModal()" class="absolute top-2 right-3 text-gray-400 hover:text-gray-600">✖</button>
  </div>
</div>

<script>
function openAddModal() {
  document.getElementById('modalTitle').innerText = '➕ Add Barangay';
  document.getElementById('barangayForm').reset();
  document.getElementById('barangay_id').value = '';
  document.getElementById('submitBtn').name = 'add';
  document.getElementById('barangayModal').classList.remove('hidden');
  document.getElementById('barangayModal').classList.add('flex');
}

function openEditModal(barangay) {
  document.getElementById('modalTitle').innerText = '✏ Edit Barangay';
  document.getElementById('barangay_id').value = barangay.barangay_id;
  document.getElementById('barangay_name').value = barangay.barangay_name;
  document.getElementById('submitBtn').name = 'edit';
  document.getElementById('barangayModal').classList.remove('hidden');
  document.getElementById('barangayModal').classList.add('flex');
}

function closeModal() {
  document.getElementById('barangayModal').classList.add('hidden');
  document.getElementById('barangayModal').classList.remove('flex');
}
</script>

</body>
</html>
