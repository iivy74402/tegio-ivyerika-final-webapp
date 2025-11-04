<?php
session_start();
require '../dbconnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get filter values
$search = $_GET['search'] ?? '';
$barangay_filter = $_GET['barangay'] ?? '';

// Fetch all farmers with filters
$sql = "
    SELECT f.farmer_id, f.id_number, f.first_name, f.middle_initial, f.last_name,
           CONCAT(f.last_name, ', ', f.first_name, ' ', COALESCE(f.middle_initial, '')) AS fullname,
           f.cellphone, b.barangay_name, b.barangay_id, fr.farm_area
    FROM farmers f
    LEFT JOIN addresses a ON f.address_id = a.address_id
    LEFT JOIN barangays b ON a.barangay_id = b.barangay_id
    LEFT JOIN farms fr ON f.farmer_id = fr.farmer_id
    WHERE 1=1
";

$params = [];
if ($search) {
    $sql .= " AND (f.id_number LIKE ? OR f.first_name LIKE ? OR f.last_name LIKE ? OR f.cellphone LIKE ?)";
    $searchParam = "%$search%";
    $params = array_fill(0, 4, $searchParam);
}
if ($barangay_filter) {
    $sql .= " AND b.barangay_id = ?";
    $params[] = $barangay_filter;
}

$sql .= " ORDER BY f.farmer_id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$farmers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get barangays for filter
$barangays = $conn->query("SELECT barangay_id, barangay_name FROM barangays ORDER BY barangay_name")->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Farmers</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="main-content ml-64 p-6">
  <header class="bg-green-700 text-white py-4 px-6 rounded-lg mb-6">
    <h1 class="text-2xl font-bold">👨🌾 All Farmers</h1>
  </header>

  <div class="bg-white p-4 rounded-lg shadow-md mb-6">
    <form method="GET" class="flex gap-4">
      <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name, ID, or phone..." class="flex-1 border rounded p-2">
      <select name="barangay" class="border rounded p-2">
        <option value="">All Barangays</option>
        <?php foreach ($barangays as $b): ?>
          <option value="<?= $b['barangay_id'] ?>" <?= ($barangay_filter == $b['barangay_id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($b['barangay_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">🔍 Filter</button>
      <a href="manage_farmers.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Reset</a>
    </form>
  </div>

  <div class="bg-white p-6 rounded-lg shadow-md">
    <table class="w-full">
      <thead class="bg-green-700 text-white">
        <tr>
          <th class="px-4 py-2 text-left">ID Number</th>
          <th class="px-4 py-2 text-left">Farmer Name</th>
          <th class="px-4 py-2 text-left">Barangay</th>
          <th class="px-4 py-2 text-left">Cellphone</th>
          <th class="px-4 py-2 text-left">Farm Area (ha)</th>
          <th class="px-4 py-2 text-center">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($farmers)): ?>
          <tr>
            <td colspan="6" class="text-center py-6 text-gray-500">
              ❌ No farmers found. <?= ($search || $barangay_filter) ? 'Try adjusting your search criteria.' : '' ?>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($farmers as $f): ?>
            <tr class="border-b hover:bg-gray-50">
              <td class="px-4 py-2"><?= htmlspecialchars($f['id_number']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($f['fullname']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($f['barangay_name'] ?? '—') ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($f['cellphone']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($f['farm_area'] ?? '0') ?></td>
              <td class="px-4 py-2 text-center">
                <button onclick='viewDetails(<?= json_encode($f) ?>)' class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">👁 View Details</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-xl shadow-2xl w-11/12 md:w-2/3 lg:w-1/2 relative overflow-hidden">
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white px-6 py-4">
      <h2 class="text-2xl font-bold flex items-center gap-2">
        <span>👨🌾</span>
        <span>Farmer Information</span>
      </h2>
    </div>
    <div class="p-6">
      <div class="bg-gray-50 rounded-lg p-4 mb-4">
        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3 flex items-center gap-2">
          <span>👤</span> Personal Details
        </h3>
        <div class="grid grid-cols-3 gap-4 mb-3">
          <div class="bg-white p-3 rounded-lg shadow-sm">
            <label class="text-xs font-medium text-gray-500 block mb-1">First Name</label>
            <p class="text-sm font-semibold text-gray-800" id="detail_first_name"></p>
          </div>
          <div class="bg-white p-3 rounded-lg shadow-sm">
            <label class="text-xs font-medium text-gray-500 block mb-1">Middle Initial</label>
            <p class="text-sm font-semibold text-gray-800" id="detail_middle_initial"></p>
          </div>
          <div class="bg-white p-3 rounded-lg shadow-sm">
            <label class="text-xs font-medium text-gray-500 block mb-1">Last Name</label>
            <p class="text-sm font-semibold text-gray-800" id="detail_last_name"></p>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-white p-3 rounded-lg shadow-sm">
            <label class="text-xs font-medium text-gray-500 block mb-1">🎫 ID Number</label>
            <p class="text-sm font-semibold text-gray-800" id="detail_id_number"></p>
          </div>
          <div class="bg-white p-3 rounded-lg shadow-sm">
            <label class="text-xs font-medium text-gray-500 block mb-1">📞 Cellphone</label>
            <p class="text-sm font-semibold text-gray-800" id="detail_cellphone"></p>
          </div>
        </div>
      </div>
      <div class="bg-green-50 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3 flex items-center gap-2">
          <span>🌾</span> Farm Details
        </h3>
        <div class="grid grid-cols-2 gap-4">
          <div class="bg-white p-3 rounded-lg shadow-sm">
            <label class="text-xs font-medium text-gray-500 block mb-1">📍 Barangay</label>
            <p class="text-sm font-semibold text-gray-800" id="detail_barangay"></p>
          </div>
          <div class="bg-white p-3 rounded-lg shadow-sm">
            <label class="text-xs font-medium text-gray-500 block mb-1">📏 Farm Area (ha)</label>
            <p class="text-sm font-semibold text-green-700" id="detail_farm_area"></p>
          </div>
        </div>
      </div>
    </div>
    <div class="bg-gray-50 px-6 py-4 flex justify-end gap-2">
      <button onclick="closeModal()" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-medium">Close</button>
    </div>
    <button onclick="closeModal()" class="absolute top-4 right-4 text-white hover:text-gray-200 transition">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
  </div>
</div>

<script>
function viewDetails(farmer) {
  document.getElementById('detail_first_name').innerText = farmer.first_name || '—';
  document.getElementById('detail_middle_initial').innerText = farmer.middle_initial || '—';
  document.getElementById('detail_last_name').innerText = farmer.last_name || '—';
  document.getElementById('detail_id_number').innerText = farmer.id_number || '—';
  document.getElementById('detail_cellphone').innerText = farmer.cellphone || '—';
  document.getElementById('detail_barangay').innerText = farmer.barangay_name || '—';
  document.getElementById('detail_farm_area').innerText = farmer.farm_area || '0';
  
  document.getElementById('detailsModal').classList.remove('hidden');
  document.getElementById('detailsModal').classList.add('flex');
}

function closeModal() {
  document.getElementById('detailsModal').classList.add('hidden');
  document.getElementById('detailsModal').classList.remove('flex');
}
</script>

</body>
</html>
