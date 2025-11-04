<?php
require '../../dbconnection.php';
session_start();

//  Security check
if (!isset($_SESSION['role'])) {
    echo "<script>alert('❌ Unauthorized access.'); window.location.href='../record_production.php';</script>";
    exit;
}

//  Basic input validation
if (empty($_POST['production_id']) || empty($_POST['farmer_id']) || empty($_POST['farm_id'])) {
    echo "<script>alert('❌ Missing required identifiers.'); window.location.href='../record_production.php';</script>";
    exit;
}

try {
    $production_id = (int) $_POST['production_id'];
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

    // Validation: Quantity sold must not exceed harvested sacks
    if ($quantity_sold > $sacks_harvested) {
        echo "<script>alert('❌ Quantity sold cannot exceed sacks harvested.'); window.location.href='../record_production.php';</script>";
        exit;
    }

    // Validate season_id
    if ($season_id <= 0) {
        echo "<script>alert('❌ Please select a valid season.'); window.location.href='../record_production.php';</script>";
        exit;
    }

    //  Verify that the season exists
    $checkSeason = $conn->prepare("SELECT COUNT(*) FROM seasons WHERE season_id = ?");
    $checkSeason->execute([$season_id]);
    if ($checkSeason->fetchColumn() == 0) {
        echo "<script>alert('❌ Selected season does not exist.'); window.location.href='../record_production.php';</script>";
        exit;
    }

    // Technician access check
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
            echo "<script>alert('🚫 Access denied: You are not assigned to this farmer\'s barangay.'); window.location.href='../record_production.php';</script>";
            exit;
        }
    }

    // Update the production record
    $stmt = $conn->prepare("
        UPDATE production_records SET
            season_id = ?, crop_type = ?, sacks_harvested = ?, weight_per_sack = ?, 
            planting_method = ?, irrigation_method = ?, yield_kg = ?, selling_price = ?, 
            planting_date = ?, harvest_date = ?, total_expense = ?, notes = ?
        WHERE production_id = ?
    ");
    $stmt->execute([
        $season_id, $crop_type, $sacks_harvested, $weight_per_sack,
        $planting_method, $irrigation_method, $yield_kg, $selling_price,
        $planting_date, $harvest_date, $total_expense, $notes, $production_id
    ]);

    // Check if there’s already a sales record for this production
    $checkSale = $conn->prepare("SELECT COUNT(*) FROM sales WHERE production_id = ?");
    $checkSale->execute([$production_id]);
    $hasSale = $checkSale->fetchColumn();

    if (!empty($sale_date) && $quantity_sold > 0) {
        if ($hasSale > 0) {
            // Update existing sale
            $stmt = $conn->prepare("
                UPDATE sales 
                SET sale_date = ?, quantity_sold = ?
                WHERE production_id = ?
            ");
            $stmt->execute([$sale_date, $quantity_sold, $production_id]);
        } else {
            // Insert new sale
            $stmt = $conn->prepare("
                INSERT INTO sales (production_id, sale_date, quantity_sold)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$production_id, $sale_date, $quantity_sold]);
        }
    }

    echo "<script>alert('✅ Production record updated successfully.'); window.location.href='../record_production.php';</script>";
    exit;

} catch (Exception $e) {
    $error = htmlspecialchars($e->getMessage());
    echo "<script>alert('❌ Error: $error'); window.location.href='../record_production.php';</script>";
    exit;
}
?>
