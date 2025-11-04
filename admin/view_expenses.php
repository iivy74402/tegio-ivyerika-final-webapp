<?php
session_start();
require '../dbconnection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$categories = [
  'Seeds','Fertilizer','Pesticide','Labor','Irrigation',
  'Fuel','Machinery','Herbicide','Insecticide',
  'Molluscicide','Rodenticide','Fungicide','Others'
];

// Get filter values
$barangay_filter = $_GET['barangay'] ?? '';
$season_filter = $_GET['season'] ?? '';

// Fetch records with expense summary
$sql = "
  SELECT 
    pr.production_id,
    f.id_number,
    CONCAT(f.last_name, ', ', f.first_name, ' ', f.middle_initial) AS fullname,
    b.barangay_name,
    SUM(CASE WHEN pe.expense_item = 'Seeds' THEN pe.amount ELSE 0 END) AS Seeds,
    SUM(CASE WHEN pe.expense_item = 'Fertilizer' THEN pe.amount ELSE 0 END) AS Fertilizer,
    SUM(CASE WHEN pe.expense_item = 'Pesticide' THEN pe.amount ELSE 0 END) AS Pesticide,
    SUM(CASE WHEN pe.expense_item = 'Labor' THEN pe.amount ELSE 0 END) AS Labor,
    SUM(CASE WHEN pe.expense_item = 'Irrigation' THEN pe.amount ELSE 0 END) AS Irrigation,
    SUM(CASE WHEN pe.expense_item = 'Fuel' THEN pe.amount ELSE 0 END) AS Fuel,
    SUM(CASE WHEN pe.expense_item = 'Machinery' THEN pe.amount ELSE 0 END) AS Machinery,
    SUM(CASE WHEN pe.expense_item = 'Herbicide' THEN pe.amount ELSE 0 END) AS Herbicide,
    SUM(CASE WHEN pe.expense_item = 'Insecticide' THEN pe.amount ELSE 0 END) AS Insecticide,
    SUM(CASE WHEN pe.expense_item = 'Molluscicide' THEN pe.amount ELSE 0 END) AS Molluscicide,
    SUM(CASE WHEN pe.expense_item = 'Rodenticide' THEN pe.amount ELSE 0 END) AS Rodenticide,
    SUM(CASE WHEN pe.expense_item = 'Fungicide' THEN pe.amount ELSE 0 END) AS Fungicide,
    SUM(CASE WHEN pe.expense_item = 'Others' THEN pe.amount ELSE 0 END) AS Others,
    COALESCE(SUM(pe.amount), 0) AS total_expense
  FROM production_records pr
  JOIN farms fm ON pr.farm_id = fm.farm_id
  JOIN farmers f ON fm.farmer_id = f.farmer_id
  JOIN addresses a ON f.address_id = a.address_id
  JOIN barangays b ON a.barangay_id = b.barangay_id
  LEFT JOIN production_expense pe ON pr.production_id = pe.production_id
  LEFT JOIN seasons s ON pr.season_id = s.season_id
  WHERE 1=1
";

$params = [];
if ($barangay_filter) {
    $sql .= " AND b.barangay_id = ?";
    $params[] = $barangay_filter;
}
if ($season_filter) {
    $sql .= " AND s.season_id = ?";
    $params[] = $season_filter;
}

$sql .= " GROUP BY pr.production_id ORDER BY pr.production_id DESC";

$stmt = $conn->prepare($sql);
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
<title>Expense Records</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800 font-sans">
<div class="main-content ml-64 p-6">

<header class="bg-green-700 text-white py-4 px-6 shadow-md rounded-lg flex justify-between items-center">
  <h1 class="text-2xl font-bold">💰 Expense Records</h1>
  <a href="dashboard.php" class="bg-white text-green-700 px-4 py-2 rounded hover:bg-gray-100 transition">⬅ Back</a>
</header>

<div class="bg-white mt-6 p-4 rounded-lg shadow-md">
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
    <a href="view_expenses.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Reset</a>
  </form>
</div>

<div class="bg-white mt-6 p-4 rounded-lg shadow-md overflow-x-auto">
  <table class="min-w-full border-collapse text-sm">
    <thead class="bg-green-700 text-white text-center">
      <tr>
        <th class="px-4 py-2 whitespace-nowrap">ID Number</th>
        <th class="px-4 py-2 whitespace-nowrap">Farmer</th>
        <th class="px-4 py-2 whitespace-nowrap">Barangay</th>
        <?php foreach ($categories as $cat): ?>
          <th class="px-3 py-2 whitespace-nowrap"><?= htmlspecialchars($cat) ?></th>
        <?php endforeach; ?>
        <th class="px-4 py-2 whitespace-nowrap">Total (₱)</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$records): ?>
        <tr><td colspan="17" class="text-center py-4 text-gray-500">No production records found.</td></tr>
      <?php else: ?>
        <?php foreach ($records as $r): ?>
          <tr class="border-b hover:bg-gray-50 text-center">
            <td class="px-3 py-2"><?= htmlspecialchars($r['id_number']) ?></td>
            <td class="px-3 py-2 text-left"><?= htmlspecialchars($r['fullname']) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($r['barangay_name']) ?></td>
            <?php foreach ($categories as $cat): 
              $val = $r[$cat] ?? 0; ?>
              <td class="px-2 py-2"><?= $val > 0 ? '₱ '.number_format($val,2) : '—' ?></td>
            <?php endforeach; ?>
            <td class="px-3 py-2 font-semibold text-green-700">
              ₱ <?= number_format($r['total_expense'], 2) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</div>

</body>
</html>
