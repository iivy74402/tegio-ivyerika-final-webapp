<?php
include('../../dbconnection.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../record_expense.php");
    exit;
}

$production_id = (int) $_POST['production_id'];
$item_id = (int) $_POST['item_id'];
$quantity_used = (float) $_POST['quantity_used'];
$unit_price = (float) $_POST['unit_price'];
$remarks = trim($_POST['remarks'] ?? '');

if ($production_id <= 0 || $item_id <= 0) {
    echo "<script>alert('Please select valid production and item.'); window.history.back();</script>";
    exit;
}

try {
    $stmt = $conn->prepare("
        INSERT INTO production_expenses (production_id, item_id, quantity_used, unit_price, remarks)
        VALUES (:production_id, :item_id, :quantity_used, :unit_price, :remarks)
    ");
    $stmt->execute([
        ':production_id' => $production_id,
        ':item_id' => $item_id,
        ':quantity_used' => $quantity_used,
        ':unit_price' => $unit_price,
        ':remarks' => $remarks
    ]);

    echo "<script>alert('✅ Expense record added successfully!'); window.location='../record_expense.php';</script>";

} catch (PDOException $e) {
    echo "<script>alert('❌ Error adding expense: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
}
?>
