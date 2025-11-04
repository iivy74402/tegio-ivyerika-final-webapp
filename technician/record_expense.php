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

//  Categories (same for Add/Edit)
$categories = [
  'Seeds','Fertilizer','Pesticide','Labor','Irrigation',
  'Fuel','Machinery','Herbicide','Insecticide',
  'Molluscicide','Rodenticide','Fungicide','Others'
];

// Add Expense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
  try {
    $production_id = $_POST['production_id'];

    $stmt = $conn->prepare("
      INSERT INTO production_expense (production_id, category_id, expense_item, amount, remarks)
      VALUES (?, ?, ?, ?, ?)
    ");
    $total = 0;
    foreach ($categories as $index => $cat) {
      $key = strtolower($cat);
      $amount = floatval($_POST[$key] ?? 0);
      if ($amount > 0) {
        $stmt->execute([$production_id, $index + 1, $cat, $amount, '']);
        $total += $amount;
      }
    }

    $update = $conn->prepare("UPDATE production_records SET total_expense = ? WHERE production_id = ?");
    $update->execute([$total, $production_id]);

    $message = "✅ Expense record added successfully.";
  } catch (Exception $e) {
    $error = "❌ Failed to add expense: " . $e->getMessage();
  }
}

// Edit Expense
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_expense'])) {
  try {
    $production_id = $_POST['production_id'];

    $conn->prepare("DELETE FROM production_expense WHERE production_id = ?")->execute([$production_id]);

    $stmt = $conn->prepare("
      INSERT INTO production_expense (production_id, category_id, expense_item, amount, remarks)
      VALUES (?, ?, ?, ?, ?)
    ");
    $total = 0;
    foreach ($categories as $index => $cat) {
      $key = strtolower($cat);
      $amount = floatval($_POST[$key] ?? 0);
      if ($amount > 0) {
        $stmt->execute([$production_id, $index + 1, $cat, $amount, '']);
        $total += $amount;
      }
    }

    $update = $conn->prepare("UPDATE production_records SET total_expense = ? WHERE production_id = ?");
    $update->execute([$total, $production_id]);

    $message = "✅ Expense record updated successfully.";
  } catch (Exception $e) {
    $error = "❌ Failed to update expense: " . $e->getMessage();
  }
}

// Delete Expense
if (isset($_GET['delete'])) {
  try {
    $del_id = intval($_GET['delete']);
    $conn->prepare("DELETE FROM production_expense WHERE production_id = ?")->execute([$del_id]);
    $conn->prepare("UPDATE production_records SET total_expense = 0 WHERE production_id = ?")->execute([$del_id]);
    $message = "🗑 Expense record deleted successfully.";
  } catch (Exception $e) {
    $error = "❌ Failed to delete expense: " . $e->getMessage();
  }
}

// Fetch records with expense summary
$sql = "
  SELECT 
    pr.production_id,
    f.id_number,
    CONCAT(f.last_name, ', ', f.first_name, ' ', f.middle_initial) AS fullname,
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
  LEFT JOIN production_expense pe ON pr.production_id = pe.production_id
";

if ($user_role === 'technician' && !empty($allowed_barangays)) {
    $placeholders = implode(',', array_fill(0, count($allowed_barangays), '?'));
    $sql .= " LEFT JOIN addresses a ON f.address_id = a.address_id WHERE a.barangay_id IN ($placeholders)";
}

$sql .= " GROUP BY pr.production_id ORDER BY pr.production_id DESC";

if ($user_role === 'technician' && !empty($allowed_barangays)) {
    $stmt = $conn->prepare($sql);
    $stmt->execute($allowed_barangays);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $records = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

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

          <?php if ($message): ?>
            <div class="mt-4 bg-green-100 text-green-700 px-4 py-2 rounded border border-green-300"><?= $message ?></div>
          <?php elseif ($error): ?>
            <div class="mt-4 bg-red-100 text-red-700 px-4 py-2 rounded border border-red-300"><?= $error ?></div>
          <?php endif; ?>

          <div class="bg-white mt-6 p-6 rounded-lg shadow-md overflow-x-auto">
            <table class="w-full border-collapse text-xs">
              <thead class="bg-green-700 text-white">
                <tr>
                  <th class="px-2 py-3 text-left sticky left-0 bg-green-700">ID</th>
                  <th class="px-2 py-3 text-left sticky left-0 bg-green-700">Farmer</th>
                  <?php foreach ($categories as $cat): ?>
                    <th class="px-1 py-3 text-center whitespace-nowrap"><?= htmlspecialchars($cat) ?></th>
                  <?php endforeach; ?>
                  <th class="px-2 py-3 text-center whitespace-nowrap">Total</th>
                  <th class="px-2 py-3 text-center sticky right-0 bg-green-700">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$records): ?>
                  <tr><td colspan="17" class="text-center py-4 text-gray-500">No production records found.</td></tr>
                <?php else: ?>
                  <?php foreach ($records as $r): ?>
                    <tr class="border-b hover:bg-gray-50">
                      <td class="px-3 py-2"><?= htmlspecialchars($r['id_number']) ?></td>
                      <td class="px-3 py-2"><?= htmlspecialchars($r['fullname']) ?></td>
                      <?php foreach ($categories as $cat): 
                        $val = $r[$cat] ?? 0; ?>
                        <td class="px-2 py-2"><?= $val > 0 ? '₱ '.number_format($val,2) : '—' ?></td>
                      <?php endforeach; ?>
                      <td class="px-3 py-2 font-semibold text-green-700">
                        ₱ <?= number_format($r['total_expense'], 2) ?>
                      </td>
                      <td class="px-3 py-2">
                        <div class="flex items-center justify-center gap-2">
                          <?php if ($r['total_expense'] > 0): ?>
                            <button onclick='openEditModal(<?= json_encode($r) ?>)'
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs whitespace-nowrap">✏ Edit</button>
                            <a href="?delete=<?= $r['production_id'] ?>" 
                              onclick="return confirm('Are you sure you want to delete this expense record?');"
                              class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs whitespace-nowrap inline-block">🗑 Delete</a>
                          <?php else: ?>
                            <button onclick="openAddModal(<?= (int)$r['production_id'] ?>, '<?= htmlspecialchars($r['fullname'], ENT_QUOTES) ?>')" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs whitespace-nowrap">➕ Add</button>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          </div>

          <!-- ADD / EDIT MODAL -->
          <div id="expenseModal" class="fixed inset-0 bg-black bg-opacity-40 hidden justify-center items-center z-50">
            <div class="bg-white rounded-lg p-6 w-11/12 md:w-1/2 shadow-lg relative">
              <h2 id="modalTitle" class="text-xl font-semibold text-green-700 mb-4"></h2>
              <form method="POST" id="expenseForm" class="space-y-3">
                <input type="hidden" id="production_id" name="production_id">
                <p class="text-gray-700 text-sm mb-2">👨‍🌾 <span id="farmer_name"></span></p>
                <div class="grid md:grid-cols-2 gap-3">
                  <?php foreach ($categories as $cat): $key = strtolower($cat); ?>
                    <div>
                      <label class="text-sm font-medium"><?= htmlspecialchars($cat) ?> (₱)</label>
                      <input type="number" step="0.01" name="<?= $key ?>" id="<?= $key ?>" 
                            class="w-full border rounded px-3 py-2 category-input" oninput="calcTotal()">
                    </div>
                  <?php endforeach; ?>
                </div>
                <div>
                  <label class="text-sm font-medium">💰 Total Expense (₱)</label>
                  <input type="text" id="totalExpense" name="total_expense" readonly 
                        class="w-full border rounded px-3 py-2 bg-gray-100 font-semibold text-green-700">
                </div>
                <div class="flex justify-end gap-2 pt-4">
                  <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</button>
                  <button type="submit" id="submitBtn" name="add_expense" class="px-4 py-2 bg-green-700 text-white rounded hover:bg-green-800">💾 Save</button>
                </div>
              </form>
              <button onclick="closeModal()" class="absolute top-2 right-3 text-gray-400 hover:text-gray-600">✖</button>
            </div>
          </div>

          <script>
          function openAddModal(id, name){
            document.getElementById('modalTitle').innerText = '➕ Add Expense Record';
            document.getElementById('submitBtn').name = 'add_expense';
            document.getElementById('production_id').value = id;
            document.getElementById('farmer_name').innerText = name;
            document.querySelectorAll('.category-input').forEach(i=>i.value='');
            document.getElementById('totalExpense').value='';
            document.getElementById('expenseModal').classList.remove('hidden');
            document.getElementById('expenseModal').classList.add('flex');
          }

          function openEditModal(record){
            document.getElementById('modalTitle').innerText = '✏ Edit Expense Record';
            document.getElementById('submitBtn').name = 'edit_expense';
            document.getElementById('production_id').value = record.production_id;
            document.getElementById('farmer_name').innerText = record.fullname;
            <?php foreach ($categories as $cat): $key = strtolower($cat); ?>
              document.getElementById('<?= $key ?>').value = record['<?= $cat ?>'] || '';
            <?php endforeach; ?>
            calcTotal();
            document.getElementById('expenseModal').classList.remove('hidden');
            document.getElementById('expenseModal').classList.add('flex');
          }

          function closeModal(){
            const modal = document.getElementById('expenseModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
          }

          function calcTotal(){
            let total=0;
            document.querySelectorAll('.category-input').forEach(inp=>{
              total += parseFloat(inp.value||0);
            });
            document.getElementById('totalExpense').value = total.toFixed(2);
          }
          </script>
          </body>
          </html>
