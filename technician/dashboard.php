<?php
session_start();
require '../dbconnection.php';
include 'includes/header.php';
include 'includes/sidebar.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
    header("Location: ../login.php");
    exit;
}

$user_id   = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? '';

$allowed_barangays = [];
if ($user_role === 'technician') {
    $stmt = $conn->prepare("SELECT barangay_id FROM technician_barangays WHERE technician_id = ?");
    $stmt->execute([$user_id]);
    $allowed_barangays = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

/* Build barangay condition if technician */
$barangayCondition = '';
if ($user_role === 'technician' && !empty($allowed_barangays)) {
    $placeholders = implode(',', array_fill(0, count($allowed_barangays), '?'));
    $barangayCondition = "WHERE a.barangay_id IN ($placeholders)";
}

/* Income */
$sql = "
    SELECT s.year, s.season_name, SUM(p.gross_income) AS total_income
    FROM production_records p
    JOIN seasons s ON p.season_id = s.season_id
    JOIN farmers f ON p.farmer_id = f.farmer_id
    JOIN addresses a ON f.address_id = a.address_id
    $barangayCondition
    GROUP BY s.year, s.season_name
    ORDER BY s.year, s.season_name
";
$stmt = $conn->prepare($sql);
if (!empty($allowed_barangays)) $stmt->execute($allowed_barangays);
else $stmt->execute();
$income = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Production */
$sql = "
    SELECT s.year, s.season_name, SUM(p.yield_kg) AS total_yield
    FROM production_records p
    JOIN seasons s ON p.season_id = s.season_id
    JOIN farmers f ON p.farmer_id = f.farmer_id
    JOIN addresses a ON f.address_id = a.address_id
    $barangayCondition
    GROUP BY s.year, s.season_name
    ORDER BY s.year, s.season_name
";
$stmt = $conn->prepare($sql);
if (!empty($allowed_barangays)) $stmt->execute($allowed_barangays);
else $stmt->execute();
$production = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Expenses */
$sql = "
    SELECT ec.category_name AS category, SUM(pe.amount) AS total
    FROM production_expense pe
    LEFT JOIN expense_categories ec ON pe.category_id = ec.category_id
    JOIN production_records p ON pe.production_id = p.production_id
    JOIN farmers f ON p.farmer_id = f.farmer_id
    JOIN addresses a ON f.address_id = a.address_id
    $barangayCondition
    GROUP BY ec.category_name
";
$stmt = $conn->prepare($sql);
if (!empty($allowed_barangays)) $stmt->execute($allowed_barangays);
else $stmt->execute();
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* Totals */
$incomeSql = "
    SELECT SUM(p.gross_income)
    FROM production_records p
    JOIN farmers f ON p.farmer_id = f.farmer_id
    JOIN addresses a ON f.address_id = a.address_id
    $barangayCondition
";
$stmt = $conn->prepare($incomeSql);
if (!empty($allowed_barangays)) $stmt->execute($allowed_barangays);
else $stmt->execute();
$total_income = $stmt->fetchColumn() ?? 0;

$prodSql = "
    SELECT SUM(p.yield_kg)
    FROM production_records p
    JOIN farmers f ON p.farmer_id = f.farmer_id
    JOIN addresses a ON f.address_id = a.address_id
    $barangayCondition
";
$stmt = $conn->prepare($prodSql);
if (!empty($allowed_barangays)) $stmt->execute($allowed_barangays);
else $stmt->execute();
$total_production = $stmt->fetchColumn() ?? 0;

$expSql = "
    SELECT SUM(pe.amount)
    FROM production_expense pe
    JOIN production_records p ON pe.production_id = p.production_id
    JOIN farmers f ON p.farmer_id = f.farmer_id
    JOIN addresses a ON f.address_id = a.address_id
    $barangayCondition
";
$stmt = $conn->prepare($expSql);
if (!empty($allowed_barangays)) $stmt->execute($allowed_barangays);
else $stmt->execute();
$total_expenses = $stmt->fetchColumn() ?? 0;
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
    width: 82.6%;
    height: 500px;
}

</style>

<div class="main-content">
    <h2>📊 Technician Dashboard</h2>

    <div class="summary-cards">
        <div class="card" style="border-top: 4px solid #4CAF50;">
            <h3>Total Income</h3>
            <p>₱<?= number_format($total_income, 2) ?></p>
        </div>
        <div class="card" style="border-top: 4px solid #2196F3;">
            <h3>Total Production</h3>
            <p><?= number_format($total_production, 2) ?> kg</p>
        </div>
        <div class="card" style="border-top: 4px solid #F44336;">
            <h3>Total Expenses</h3>
            <p>₱<?= number_format($total_expenses, 2) ?></p>
        </div>
    </div>

    <div class="charts">
        <div class="chart-box">
            <h3>Income by Season</h3>
            <div class="chart-container"><canvas id="incomeChart"></canvas></div>
        </div>

        <div class="chart-box">
            <h3>Production Trends</h3>
            <div class="chart-container"><canvas id="productionChart"></canvas></div>
        </div>

        <div class="chart-box">
            <h3>Expenses by Category</h3>
            <div class="chart-container"><canvas id="expenseChart"></canvas></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const income = <?= json_encode($income) ?>;
const production = <?= json_encode($production) ?>;
const expenses = <?= json_encode($expenses) ?>;

const chartOptions = {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: true, position: 'bottom' } },
    scales: { y: { beginAtZero: true } }
};

// Income Chart
new Chart(document.getElementById('incomeChart'), {
    type: 'bar',
    data: {
        labels: income.map(d => d.season_name + ' ' + d.year),
        datasets: [{ label: '₱ Income', data: income.map(d => d.total_income), backgroundColor: '#4CAF50', borderRadius: 8 }]
    },
    options: chartOptions
});

// Production Chart
new Chart(document.getElementById('productionChart'), {
    type: 'line',
    data: {
        labels: production.map(d => d.season_name + ' ' + d.year),
        datasets: [{ label: 'Yield (kg)', data: production.map(d => d.total_yield), borderColor: '#2196F3', backgroundColor: 'rgba(33,150,243,0.1)', borderWidth: 2, tension: 0.4, fill: true }]
    },
    options: chartOptions
});

// Expense Chart
new Chart(document.getElementById('expenseChart'), {
    type: 'pie',
    data: {
        labels: expenses.map(d => d.category),
        datasets: [{ data: expenses.map(d => d.total), backgroundColor: ['#FF6384','#36A2EB','#FFCE56','#4CAF50','#9C27B0','#FF9800'] }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
</script>

<?php include 'includes/footer.php'; ?>
