<?php
session_start();
require '../dbconnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get filter values
$barangay_filter = $_GET['barangay'] ?? '';
$season_filter = $_GET['season'] ?? '';

// Build query with filters
$query = "
    SELECT 
        f.farmer_id,
        CONCAT(f.last_name, ', ', f.first_name, ' ', COALESCE(f.middle_initial, '')) AS fullname,
        b.barangay_name,
        pr.production_id,
        pr.crop_type,
        pr.yield_kg,
        pr.selling_price,
        pr.harvest_date,
        pr.planting_date,
        pr.total_expense,
        s.season_name,
        (pr.yield_kg * pr.selling_price) AS gross_income,
        (pr.yield_kg * pr.selling_price - pr.total_expense) AS net_income
    FROM production_records pr
    JOIN farms fm ON pr.farm_id = fm.farm_id
    JOIN farmers f ON fm.farmer_id = f.farmer_id
    JOIN addresses a ON f.address_id = a.address_id
    JOIN barangays b ON a.barangay_id = b.barangay_id
    LEFT JOIN seasons s ON pr.season_id = s.season_id
    WHERE 1=1
";

$params = [];
if ($barangay_filter) {
    $query .= " AND b.barangay_id = ?";
    $params[] = $barangay_filter;
}
if ($season_filter) {
    $query .= " AND s.season_id = ?";
    $params[] = $season_filter;
}

$query .= " ORDER BY pr.production_id DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get barangays and seasons for filters
$barangays = $conn->query("SELECT barangay_id, barangay_name FROM barangays ORDER BY barangay_name")->fetchAll(PDO::FETCH_ASSOC);
$seasons = $conn->query("SELECT season_id, CONCAT(season_name, ' ', year) AS label FROM seasons ORDER BY year DESC")->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Production Records</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="main-content ml-64 p-6">
  <header class="bg-green-700 text-white py-4 px-6 rounded-lg mb-6">
    <h1 class="text-2xl font-bold">🌾 Production Records</h1>
  </header>

  <div class="bg-white p-4 rounded-lg shadow-md mb-6">
    <form method="GET" class="flex gap-4">
      <select name="barangay" class="border rounded p-2">
        <option value="">All Barangays</option>
        <?php foreach ($barangays as $b): ?>
          <option value="<?= $b['barangay_id'] ?>" <?= ($barangay_filter == $b['barangay_id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($b['barangay_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      
      <select name="season" class="border rounded p-2">
        <option value="">All Seasons</option>
        <?php foreach ($seasons as $s): ?>
          <option value="<?= $s['season_id'] ?>" <?= ($season_filter == $s['season_id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($s['label']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      
      <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Filter</button>
      <a href="view_production.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Reset</a>
    </form>
  </div>

  <div class="bg-white p-6 rounded-lg shadow-md overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-green-700 text-white">
        <tr>
          <th class="px-4 py-2 text-left">Farmer</th>
          <th class="px-4 py-2 text-left">Barangay</th>
          <th class="px-4 py-2 text-left">Crop</th>
          <th class="px-4 py-2 text-left">Season</th>
          <th class="px-4 py-2 text-left">Yield (kg)</th>
          <th class="px-4 py-2 text-left">Price (₱/kg)</th>
          <th class="px-4 py-2 text-left">Gross Income (₱)</th>
          <th class="px-4 py-2 text-left">Expenses (₱)</th>
          <th class="px-4 py-2 text-left">Net Income (₱)</th>
          <th class="px-4 py-2 text-left">Planting Date</th>
          <th class="px-4 py-2 text-left">Harvest Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($records)): ?>
          <?php foreach ($records as $r): ?>
            <tr class="border-b hover:bg-gray-50">
              <td class="px-4 py-2"><?= htmlspecialchars($r['fullname']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($r['barangay_name']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($r['crop_type']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($r['season_name'] ?? '—') ?></td>
              <td class="px-4 py-2"><?= number_format($r['yield_kg'], 2) ?></td>
              <td class="px-4 py-2"><?= number_format($r['selling_price'], 2) ?></td>
              <td class="px-4 py-2 text-green-600 font-semibold"><?= number_format($r['gross_income'], 2) ?></td>
              <td class="px-4 py-2 text-red-600"><?= number_format($r['total_expense'], 2) ?></td>
              <td class="px-4 py-2 text-blue-600 font-semibold"><?= number_format($r['net_income'], 2) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($r['planting_date']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($r['harvest_date']) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="11" class="text-center py-6 text-gray-500">No production records found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

</body>
</html>
