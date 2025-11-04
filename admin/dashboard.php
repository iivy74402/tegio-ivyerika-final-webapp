<?php
session_start();
require '../dbconnection.php';
include 'includes/header.php';
include 'includes/sidebar.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get statistics
$total_farmers = $conn->query("SELECT COUNT(*) FROM farmers")->fetchColumn();
$total_technicians = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'technician'")->fetchColumn();
$total_production = $conn->query("SELECT SUM(yield_kg) FROM production_records")->fetchColumn() ?? 0;
$total_income = $conn->query("SELECT SUM(yield_kg * selling_price) FROM production_records")->fetchColumn() ?? 0;

// Get production by barangay
$prodStmt = $conn->query("
  SELECT b.barangay_name, SUM(pr.yield_kg) as total_yield
  FROM production_records pr
  JOIN farms f ON pr.farm_id = f.farm_id
  JOIN addresses a ON f.address_id = a.address_id
  JOIN barangays b ON a.barangay_id = b.barangay_id
  GROUP BY b.barangay_id
  ORDER BY total_yield DESC
  LIMIT 10
");
$prodData = $prodStmt->fetchAll(PDO::FETCH_ASSOC);

// Get farmers by barangay
$farmersStmt = $conn->query("
  SELECT b.barangay_name, COUNT(f.farmer_id) as total_farmers
  FROM farmers f
  JOIN addresses a ON f.address_id = a.address_id
  JOIN barangays b ON a.barangay_id = b.barangay_id
  GROUP BY b.barangay_id
  ORDER BY total_farmers DESC
  LIMIT 10
");
$farmersData = $farmersStmt->fetchAll(PDO::FETCH_ASSOC);

// Get income by season
$incomeStmt = $conn->query("
  SELECT s.season_name, s.year, SUM(pr.yield_kg * pr.selling_price) as total_income
  FROM production_records pr
  JOIN seasons s ON pr.season_id = s.season_id
  GROUP BY s.season_id
  ORDER BY s.year, s.season_name
");
$incomeData = $incomeStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f6f8fb;
}
.main-content { padding: 25px 35px; }
h2 { font-size: 1.8rem; font-weight: 600; color: #333; margin-bottom: 20px; }
.summary-cards {
    display: flex; gap: 20px; margin-bottom: 35px; flex-wrap: wrap;
}
.card {
    flex: 1; min-width: 200px; background: #fff; border-radius: 14px;
    padding: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    text-align: center; transition: transform 0.2s;
}
.card:hover { transform: translateY(-4px); }
.card h3 { font-size: 1rem; color: #555; margin-bottom: 8px; }
.card p { font-size: 1.4rem; font-weight: 700; color: #222; }
.charts {
    display: flex;
    justify-content: space-between;
    align-items: stretch;
    flex-wrap: wrap;
    gap: 25px;
}
.chart-box {
    flex: 1 1 calc(33.333% - 25px);
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    padding: 20px;
    text-align: center;
    display: flex;
    flex-direction: column;
}
.chart-box h3 {
    font-size: 1rem;
    color: #333;
    margin-bottom: 12px;
}
.chart-container {
    position: relative;
    width: 100%;
    height: 300px;
}
</style>

<div class="main-content">
    <h2>📊 Admin Dashboard</h2>

    <div class="summary-cards">
        <div class="card" style="border-top: 4px solid #4CAF50;">
            <h3>Total Farmers</h3>
            <p><?= number_format($total_farmers) ?></p>
        </div>
        <div class="card" style="border-top: 4px solid #2196F3;">
            <h3>Total Technicians</h3>
            <p><?= number_format($total_technicians) ?></p>
        </div>
        <div class="card" style="border-top: 4px solid #F59E0B;">
            <h3>Total Production</h3>
            <p><?= number_format($total_production, 2) ?> kg</p>
        </div>
        <div class="card" style="border-top: 4px solid #10B981;">
            <h3>Total Income</h3>
            <p>₱<?= number_format($total_income, 2) ?></p>
        </div>
    </div>

    <div class="charts">
        <div class="chart-box">
            <h3>Production by Barangay</h3>
            <div class="chart-container"><canvas id="productionChart"></canvas></div>
        </div>

        <div class="chart-box">
            <h3>Farmers Distribution</h3>
            <div class="chart-container"><canvas id="farmersChart"></canvas></div>
        </div>

        <div class="chart-box">
            <h3>Income by Season</h3>
            <div class="chart-container"><canvas id="incomeChart"></canvas></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const chartOptions = {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: true, position: 'bottom' } },
    scales: { y: { beginAtZero: true } }
};

// Production Chart
new Chart(document.getElementById('productionChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($prodData, 'barangay_name')) ?>,
        datasets: [{ label: 'Yield (kg)', data: <?= json_encode(array_column($prodData, 'total_yield')) ?>, backgroundColor: '#10B981', borderRadius: 8 }]
    },
    options: chartOptions
});

// Farmers Chart
new Chart(document.getElementById('farmersChart'), {
    type: 'pie',
    data: {
        labels: <?= json_encode(array_column($farmersData, 'barangay_name')) ?>,
        datasets: [{ data: <?= json_encode(array_column($farmersData, 'total_farmers')) ?>, backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#14B8A6', '#F97316', '#6366F1', '#84CC16'] }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
});

// Income Chart
new Chart(document.getElementById('incomeChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(function($d) { return $d['season_name'] . ' ' . $d['year']; }, $incomeData)) ?>,
        datasets: [{ label: '₱ Income', data: <?= json_encode(array_column($incomeData, 'total_income')) ?>, borderColor: '#2196F3', backgroundColor: 'rgba(33,150,243,0.1)', borderWidth: 2, tension: 0.4, fill: true }]
    },
    options: chartOptions
});
</script>

<?php include 'includes/footer.php'; ?>
