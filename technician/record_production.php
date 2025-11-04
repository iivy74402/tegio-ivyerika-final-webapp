<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
    header("Location: ../login.php");
    exit;
}

require '../dbconnection.php'; 

$message = '';
$error = '';

try {
    $user_id   = $_SESSION['user_id'] ?? 0;
    $user_role = $_SESSION['role'] ?? '';

    //  Base Query (common for all roles)
    $baseQuery = "
        SELECT 
            f.farmer_id,
            f.id_number,
            CONCAT(f.last_name, ', ', f.first_name, ' ', COALESCE(f.middle_initial, '')) AS fullname,
            fr.farm_id,
            fr.farm_area,
            b.barangay_name,
            pr.production_id,
            pr.season_id,
            pr.crop_type,
            pr.yield_kg,
            pr.selling_price,
            (pr.yield_kg * pr.selling_price) AS gross_income,
            (pr.yield_kg * pr.selling_price - pr.total_expense) AS net_income,
            pr.harvest_date,
            pr.planting_date,
            pr.sacks_harvested,
            pr.weight_per_sack,
            pr.planting_method,
            pr.irrigation_method,
            pr.total_expense,
            pr.notes,
            s.season_name,
            sa.sale_date,
            sa.quantity_sold,
            (sa.quantity_sold * pr.selling_price * pr.weight_per_sack) AS total_amount
        FROM farmers f
        LEFT JOIN addresses a ON f.address_id = a.address_id
        LEFT JOIN barangays b ON a.barangay_id = b.barangay_id
        LEFT JOIN farms fr ON f.farmer_id = fr.farmer_id
        LEFT JOIN production_records pr ON pr.farm_id = fr.farm_id
        LEFT JOIN seasons s ON pr.season_id = s.season_id
        LEFT JOIN sales sa ON pr.production_id = sa.production_id
    ";

    // Technician restriction (only their assigned barangays)
    $params = [];
    if ($user_role === 'technician' && $user_id) {
        $baseQuery .= "
            INNER JOIN technician_barangays tb 
                ON tb.barangay_id = b.barangay_id 
                AND tb.technician_id = :tech_id
        ";
        $params[':tech_id'] = $user_id;
    }

    $baseQuery .= " ORDER BY f.farmer_id DESC";

    $stmt = $conn->prepare($baseQuery);
    $stmt->execute($params);
    $farmers = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $error = "❌ Database error: " . htmlspecialchars($e->getMessage());
}

// Delete functionality
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    try {
        $stmt = $conn->prepare("DELETE FROM production_records WHERE production_id = ?");
        $stmt->execute([$del_id]);
        $message = "🗑️ Record deleted successfully.";
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
        exit;
    } catch (Exception $e) {
        $error = "❌ Failed to delete: " . htmlspecialchars($e->getMessage());
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';

 if (!empty($_SESSION['flash_success'])): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
        <?= $_SESSION['flash_success']; ?>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
        <?= $_SESSION['flash_error']; ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif;

?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Production Records</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

<div class="main-content ml-64 p-6">
  <header class="bg-green-700 text-white py-4 px-6 rounded-lg flex justify-between items-center">
    <h1 class="text-xl font-bold">🌾 Production Records</h1>

  
  </header>

  <?php if ($message): ?>
    <div class="mt-4 bg-green-100 text-green-800 p-3 rounded shadow"><?= $message ?></div>
  <?php elseif ($error): ?>
    <div class="mt-4 bg-red-100 text-red-800 p-3 rounded shadow"><?= $error ?></div>
  <?php endif; ?>

  <div class="bg-white mt-6 p-4 rounded-lg shadow-md overflow-x-auto max-w-[1900px] mx-auto">
    <table class="w-full text-sm border-collapse border border-gray-200">
      <thead class="bg-green-700 text-white">
        <tr>
          <th class="px-4 py-2">Farmer</th>
          <th class="px-4 py-2">Farm Area (ha)</th>
          <th class="px-4 py-2">Crop</th>
          <th class="px-4 py-2">Season</th>
          <th class="px-4 py-2">Sacks</th>
          <th class="px-4 py-2">Weight/Sack</th>
          <th class="px-4 py-2">Planting</th>
          <th class="px-4 py-2">Harvest</th>
          <th class="px-4 py-2">Yield (kg)</th>
          <th class="px-4 py-2">Price</th>
          <th class="px-4 py-2">Gross (₱)</th> 
          <th class="px-4 py-2">Expense (₱)</th>
          <th class="px-4 py-2">Net (₱)</th>
          <th class="px-4 py-2">Notes</th>
          <th class="px-4 py-2">Sale Date</th>
          <th class="px-4 py-2">Qty Sold</th>
          <th class="px-4 py-2">Total Amount (₱)</th>
          <th class="px-4 py-2 text-center">Action</th>
        </tr>
      </thead>

      <tbody>
        <?php if (!empty($farmers)): ?>
          <?php foreach ($farmers as $f): ?>
            <tr class="border-b hover:bg-gray-50 transition">
              <td class="px-4 py-2"><?= htmlspecialchars($f['fullname']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($f['farm_area'] ?? '—') ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($f['crop_type'] ?? '—') ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($f['season_name'] ?? '—') ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($f['sacks_harvested'] ?? '—') ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($f['weight_per_sack'] ?? '—') ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($f['planting_date'] ?? '—') ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($f['harvest_date'] ?? '—') ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($f['yield_kg'] ?? '—') ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars(number_format($f['selling_price'] ?? 0, 2)) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars(number_format($f['gross_income'] ?? 0, 2)) ?></td> 
              <td class="px-4 py-2"><?= htmlspecialchars(number_format($f['total_expense'] ?? 0, 2)) ?></td>
              <td class="px-4 py-2 font-semibold text-green-700"><?= htmlspecialchars(number_format($f['net_income'] ?? 0, 2)) ?></td> 
              <td class="px-4 py-2"><?= htmlspecialchars($f['notes'] ?? '—') ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($f['sale_date'] ?? '—') ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($f['quantity_sold'] ?? '—') ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars(number_format($f['total_amount'] ?? 0, 2)) ?></td>
             <td class="px-4 py-2 text-center">
              <?php if (!empty($f['production_id'])): ?>
                <button 
                  class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs"
                  onclick='openEditModal(<?= json_encode($f) ?>)'>✏️ Edit</button>
                
                <button 
                  onclick="confirmDelete(<?= (int)$f['production_id'] ?>)"
                  class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">🗑️ Delete</button>
              <?php else: ?>
                <button 
                  onclick="openAddModal(<?= (int)$f['farmer_id'] ?>, '<?= htmlspecialchars($f['fullname']) ?>', '<?= htmlspecialchars($f['farm_area']) ?>', <?= (int)$f['farm_id'] ?>)" 
                  class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs">➕ Add</button>
              <?php endif; ?>
            </td>

            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="18" class="text-center py-6 text-gray-500">No production records found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Production Record Modal -->
<div id="addProductionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl p-6 relative">
    <form id="addProductionForm" method="POST" action="actions/add_production.php" class="space-y-6">

      <!-- Header -->
      <div class="flex justify-between items-center border-b pb-3">
        <h2 class="text-xl font-semibold text-gray-800">Add Production Record</h2>
        <button type="button" onclick="closeAddModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
      </div>

      <!-- Hidden Inputs -->
      <input type="hidden" name="farmer_id" id="modal_farmer_id">
      <input type="hidden" name="farm_id" id="modal_farm_id">

      <!-- Farmer Info -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Farmer</label>
          <input type="text" id="modal_farmer_name" class="w-full border rounded-lg p-2.5 bg-gray-100" readonly>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Farm Area (ha)</label>
          <input type="text" id="modal_farm_area" class="w-full border rounded-lg p-2.5 bg-gray-100" readonly>
        </div>
      </div>

      <!-- Season & Crop Type -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Season</label>
          <select name="season_id" class="w-full border rounded-lg p-2.5">
            <option value="">Select Season</option>
            <?php
              $seasons = $conn->query("SELECT season_id, CONCAT(season_name, ' - ', year) AS label FROM seasons");
              while ($s = $seasons->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$s['season_id']}'>{$s['label']}</option>";
              }
            ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Crop Type</label>
          <select name="crop_type" class="w-full border rounded-lg p-2.5">
            <option value="Palay">Palay</option>
          </select>
        </div>
      </div>

      <!-- Planting & Irrigation Methods -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Planting Method</label>
          <select name="planting_method" class="w-full border rounded-lg p-2.5">
            <option value="">Select Method</option>
            <option value="Sabog">Sabog</option>
            <option value="Bunot at Talok">Bunot at Talok</option>
            <option value="Transplant">Transplant</option>
            <option value="Direct Seeding">Direct Seeding</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Irrigation Method</label>
          <select name="irrigation_method" class="w-full border rounded-lg p-2.5">
            <option value="">Select Irrigation</option>
            <option value="NIA">NIA</option>
            <option value="Bugsok/Water Pump">Bugsok/Water Pump</option>
            <option value="Manual">Manual</option>
            <option value="Rainfed">Rainfed</option>
          </select>
        </div>
      </div>

      <!-- Production Details -->
      <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Sacks Harvested</label>
          <input type="number" name="sacks_harvested" id="sacks_harvested" class="w-full border rounded-lg p-2.5" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Weight per Sack (kg)</label>
          <input type="number" name="weight_per_sack" id="weight_per_sack" step="0.01" class="w-full border rounded-lg p-2.5" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Yield (kg)</label>
          <input type="number" name="yield_kg" id="yield_kg" class="w-full border rounded-lg p-2.5 bg-gray-100" readonly>
        </div>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Selling Price (₱)</label>
          <input type="number" name="selling_price" id="selling_price" step="0.01" class="w-full border rounded-lg p-2.5" required>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Total Expense (₱)</label>
          <input type="number" name="total_expense" id="total_expense" step="0.01" class="w-full border rounded-lg p-2.5">
        </div>
      </div>

      <!-- Sales Info -->
      <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Quantity Sold (kg)</label>
          <input type="number" name="quantity_sold" step="0.01" class="w-full border rounded-lg p-2.5">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Date of Sale</label>
          <input type="date" name="sale_date" class="w-full border rounded-lg p-2.5">
        </div>
      </div>

      <!-- Dates -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Planting Date</label>
          <input type="date" name="planting_date" class="w-full border rounded-lg p-2.5">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Harvest Date</label>
          <input type="date" name="harvest_date" class="w-full border rounded-lg p-2.5">
        </div>
      </div>

      <!-- Notes -->
      <div>
        <label class="block text-sm font-medium text-gray-700">Notes</label>
        <textarea name="notes" rows="3" class="w-full border rounded-lg p-2.5"></textarea>
      </div>

      <!-- Footer -->
      <div class="flex justify-end gap-3 border-t pt-4">
        <button type="button" onclick="closeAddModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">Save Record</button>
      </div>
    </form>
  </div>
</div>

<script>
function toNumber(v){ 
  return Number(String(v).replace(/,/g,'')) || 0; 
}

// Compute Yield, Gross & Net
function computeGrossNet(){
  const sacks = toNumber(document.getElementById('sacks_harvested')?.value);
  const weight = toNumber(document.getElementById('weight_per_sack')?.value);
  const price = toNumber(document.getElementById('selling_price')?.value);
  const expense = toNumber(document.getElementById('total_expense')?.value);

  const yieldKg = sacks * weight;
  const gross = yieldKg * price;
  const net = gross - expense;

  const yieldField = document.getElementById('yield_kg');
  if (yieldField) yieldField.value = yieldKg.toFixed(2);
}

// Validate Quantity Sold
function validateQuantitySold() {
  const qtySold = toNumber(document.getElementById('quantity_sold')?.value);
  const sacks = toNumber(document.getElementById('sacks_harvested')?.value);
  
  const warning = document.getElementById('qty_warning');
  
  if (qtySold > sacks) {
    warning.textContent = "❌ Quantity sold cannot exceed sacks harvested.";
    warning.classList.remove('hidden');
    return false;
  } else {
    warning.classList.add('hidden');
    warning.textContent = "";
    return true;
  }
}

//  Attach live listeners
['sacks_harvested', 'weight_per_sack', 'selling_price', 'total_expense', 'quantity_sold'].forEach(id => {
  const el = document.getElementById(id);
  if (el) el.addEventListener('input', () => {
    computeGrossNet();
    validateQuantitySold();
  });
});

//  Modal open/close functions
function openAddModal(farmer_id, fullname, farm_area, farm_id) {
  document.getElementById('modal_farmer_id').value = farmer_id;
  document.getElementById('modal_farmer_name').value = fullname;
  document.getElementById('modal_farm_area').value = farm_area;
  document.getElementById('modal_farm_id').value = farm_id;

  const modal = document.getElementById('addProductionModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}

function closeAddModal() {
  const modal = document.getElementById('addProductionModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

//  Click outside to close
window.onclick = function(e) {
  const modal = document.getElementById('addProductionModal');
  if (e.target === modal) closeAddModal();
};

//  Prevent form submission if invalid
document.getElementById('addProductionForm')?.addEventListener('submit', function(e) {
  if (!validateQuantitySold()) {
    e.preventDefault();
    alert("Please correct the errors before submitting the form.");
  }
});

//  Delete confirmation
function confirmDelete(id) {
  if (confirm('Are you sure you want to delete this production record?')) {
    window.location.href = '?delete=' + id;
  }
}

// Edit modal function (prefills form)
function openEditModal(data) {
  openAddModal(data.farmer_id, data.fullname, data.farm_area, data.farm_id);

  document.querySelector('form#addProductionForm').action = 'actions/update_production.php';
  document.querySelector('form#addProductionForm').insertAdjacentHTML(
    'beforeend',
    `<input type="hidden" name="production_id" value="${data.production_id}">`
  );

  // Prefill fields
  document.querySelector('select[name="season_id"]').value = data.season_id || '';
  document.querySelector('select[name="crop_type"]').value = data.crop_type || 'Palay';
  document.querySelector('select[name="planting_method"]').value = data.planting_method || '';
  document.querySelector('select[name="irrigation_method"]').value = data.irrigation_method || '';

  document.querySelector('input[name="sacks_harvested"]').value = data.sacks_harvested || '';
  document.querySelector('input[name="weight_per_sack"]').value = data.weight_per_sack || '';
  document.querySelector('input[name="yield_kg"]').value = data.yield_kg || '';
  document.querySelector('input[name="selling_price"]').value = data.selling_price || '';
  document.querySelector('input[name="total_expense"]').value = data.total_expense || '';
  document.querySelector('input[name="quantity_sold"]').value = data.quantity_sold || '';
  document.querySelector('input[name="sale_date"]').value = data.sale_date || '';
  document.querySelector('input[name="planting_date"]').value = data.planting_date || '';
  document.querySelector('input[name="harvest_date"]').value = data.harvest_date || '';
  document.querySelector('textarea[name="notes"]').value = data.notes || '';

  const expenseInput = document.querySelector('input[name="total_expense"]');
  if (expenseInput) {
    expenseInput.disabled = true;
  }

  // Change modal title
  document.querySelector('#addProductionModal h2').textContent = 'Edit Production Record';
}

</script>


</body>
</html>
