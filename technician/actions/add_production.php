<?php
require '../../dbconnection.php';
session_start();

if (!isset($_SESSION['role'])) {
    echo json_encode(['status' => 'error', 'message' => '❌ Unauthorized access.']);
    exit;
}

if (empty($_POST['farmer_id']) || empty($_POST['farm_id'])) {
    echo json_encode(['status' => 'error', 'message' => '❌ Missing farmer or farm ID.']);
    exit;
}

try {
    $farmer_id = (int) $_POST['farmer_id'];
    $farm_id = (int) $_POST['farm_id'];
    $season_id = (int) ($_POST['season_id'] ?? 0);
    $crop_type = $_POST['crop_type'] ?? 'Palay';

    $sacks_harvested = (float) ($_POST['sacks_harvested'] ?? 0);
    $weight_per_sack = (float) ($_POST['weight_per_sack'] ?? 0);
    $yield_kg = $sacks_harvested * $weight_per_sack;

    $selling_price = (float) ($_POST['selling_price'] ?? 0);
    $total_expense = (float) ($_POST['total_expense'] ?? 0);
    $planting_date = !empty($_POST['planting_date']) ? $_POST['planting_date'] : null;
    $harvest_date = !empty($_POST['harvest_date']) ? $_POST['harvest_date'] : null;
    $notes = trim($_POST['notes'] ?? '');

    $planting_method = $_POST['planting_method'] ?? null;
    $irrigation_method = $_POST['irrigation_method'] ?? null;

    $sale_date = !empty($_POST['sale_date']) ? $_POST['sale_date'] : null;
    $quantity_sold = (float) ($_POST['quantity_sold'] ?? 0);

    // Validation: quantity_sold must not exceed sacks_harvested
    if ($quantity_sold > $sacks_harvested) {
    echo "<script>
        alert('❌ Quantity sold cannot exceed sacks harvested.');
        window.location.href = '../record_production.php';
    </script>";
    exit;
}


    // ✅ Verify season exists
    if ($season_id <= 0) {
         echo "<script>
        alert('❌ Please select a valid season')";
        exit;
    }

    // ✅ Technician restriction check
    if ($_SESSION['role'] === 'technician' && isset($_SESSION['technician_id'])) {
        $technician_id = (int) $_SESSION['technician_id'];

        $check = $conn->prepare("
            SELECT COUNT(*) 
            FROM farmers f
            INNER JOIN farms fr ON f.farmer_id = fr.farmer_id
            INNER JOIN addresses a ON f.address_id = a.address_id
            INNER JOIN technician_barangays tb ON a.barangay_id = tb.barangay_id
            WHERE f.farmer_id = :farmer_id
              AND tb.technician_id = :tech_id
        ");
        $check->execute([
            ':farmer_id' => $farmer_id,
            ':tech_id' => $technician_id
        ]);

        if ($check->fetchColumn() == 0) {
            echo json_encode(['status' => 'error', 'message' => '🚫 Access denied: You are not assigned to this farmer\'s barangay.']);
            exit;
        }
    }

    // ✅ Insert into production_records
    $stmt = $conn->prepare("
        INSERT INTO production_records 
        (farmer_id, farm_id, season_id, crop_type, sacks_harvested, weight_per_sack, 
         planting_method, irrigation_method, yield_kg, selling_price, planting_date, 
         harvest_date, total_expense, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $farmer_id, $farm_id, $season_id, $crop_type,
        $sacks_harvested, $weight_per_sack,
        $planting_method, $irrigation_method,
        $yield_kg, $selling_price,
        $planting_date, $harvest_date,
        $total_expense, $notes
    ]);

    $production_id = $conn->lastInsertId();

    // ✅ Optional: add sales record if provided
    if (!empty($sale_date) && $quantity_sold > 0) {
        $stmt = $conn->prepare("
            INSERT INTO sales (production_id, sale_date, quantity_sold)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$production_id, $sale_date, $quantity_sold]);
    }

    // ✅ Redirect normally (no JSON needed when coming from form)
    $_SESSION['flash'] = '✅ Production record added successfully.';
    header('Location: ../record_production.php');
    exit;

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => '❌ Error: ' . htmlspecialchars($e->getMessage())]);
    exit;
require '../../dbconnection.php';
session_start();

if (!isset($_SESSION['role'])) {
    $_SESSION['flash_error'] = '❌ Unauthorized access.';
    header('Location: ../record_production.php');
    exit;
}

if (empty($_POST['farmer_id']) || empty($_POST['farm_id'])) {
    $_SESSION['flash_error'] = '❌ Missing farmer or farm ID.';
    header('Location: ../record_production.php');
    exit;
}

try {
    $farmer_id = (int) $_POST['farmer_id'];
    $farm_id = (int) $_POST['farm_id'];
    $season_id = (int) ($_POST['season_id'] ?? 0);
    $crop_type = $_POST['crop_type'] ?? 'Palay';

    $sacks_harvested = (float) ($_POST['sacks_harvested'] ?? 0);
    $weight_per_sack = (float) ($_POST['weight_per_sack'] ?? 0);
    $yield_kg = $sacks_harvested * $weight_per_sack;

    $selling_price = (float) ($_POST['selling_price'] ?? 0);
    $total_expense = (float) ($_POST['total_expense'] ?? 0);
    $planting_date = !empty($_POST['planting_date']) ? $_POST['planting_date'] : null;
    $harvest_date = !empty($_POST['harvest_date']) ? $_POST['harvest_date'] : null;
    $notes = trim($_POST['notes'] ?? '');

    $planting_method = $_POST['planting_method'] ?? null;
    $irrigation_method = $_POST['irrigation_method'] ?? null;

    $sale_date = !empty($_POST['sale_date']) ? $_POST['sale_date'] : null;
    $quantity_sold = (float) ($_POST['quantity_sold'] ?? 0);

    // ⚠️ Validation
    if ($quantity_sold > $sacks_harvested) {
        $_SESSION['flash_error'] = '❌ Quantity sold cannot exceed sacks harvested.';
        header('Location: ../record_production.php');
        exit;
    }

    if ($season_id <= 0) {
        $_SESSION['flash_error'] = '❌ Please select a valid season.';
        header('Location: ../record_production.php');
        exit;
    }

    // ✅ Technician restriction check
    if ($_SESSION['role'] === 'technician' && isset($_SESSION['technician_id'])) {
        $technician_id = (int) $_SESSION['technician_id'];

        $check = $conn->prepare("
            SELECT COUNT(*) 
            FROM farmers f
            INNER JOIN farms fr ON f.farmer_id = fr.farmer_id
            INNER JOIN addresses a ON f.address_id = a.address_id
            INNER JOIN technician_barangays tb ON a.barangay_id = tb.barangay_id
            WHERE f.farmer_id = :farmer_id
              AND tb.technician_id = :tech_id
        ");
        $check->execute([
            ':farmer_id' => $farmer_id,
            ':tech_id' => $technician_id
        ]);

        if ($check->fetchColumn() == 0) {
            $_SESSION['flash_error'] = '🚫 Access denied: You are not assigned to this farmer\'s barangay.';
            header('Location: ../record_production.php');
            exit;
        }
    }

    // ✅ Insert production record
    $stmt = $conn->prepare("
        INSERT INTO production_records 
        (farmer_id, farm_id, season_id, crop_type, sacks_harvested, weight_per_sack, 
         planting_method, irrigation_method, yield_kg, selling_price, planting_date, 
         harvest_date, total_expense, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $farmer_id, $farm_id, $season_id, $crop_type,
        $sacks_harvested, $weight_per_sack,
        $planting_method, $irrigation_method,
        $yield_kg, $selling_price,
        $planting_date, $harvest_date,
        $total_expense, $notes
    ]);

    $production_id = $conn->lastInsertId();

    // ✅ Optional: Add sales record
    if (!empty($sale_date) && $quantity_sold > 0) {
        $stmt = $conn->prepare("
            INSERT INTO sales (production_id, sale_date, quantity_sold)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$production_id, $sale_date, $quantity_sold]);
    }

    $_SESSION['flash_success'] = '✅ Production record added successfully.';
    header('Location: ../record_production.php');
    exit;

} catch (Exception $e) {
    $_SESSION['flash_error'] = '❌ Error: ' . htmlspecialchars($e->getMessage());
    header('Location: ../record_production.php');
    exit;
}
}
?>


