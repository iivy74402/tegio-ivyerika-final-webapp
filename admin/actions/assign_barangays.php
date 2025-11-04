<?php
require '../../dbconnection.php';
session_start();

try {
    $technician_id = $_POST['technician_id'];
    $barangay_ids = $_POST['barangay_ids'] ?? [];

    if (count($barangay_ids) < 1) {
        $_SESSION['error'] = "❌ A technician must be assigned to at least 1 barangay.";
        header("Location: ../manage_technicians.php");
        exit;
    }

    $conn->beginTransaction();

    // Remove existing assignments
    $conn->prepare("DELETE FROM technician_barangays WHERE technician_id = ?")->execute([$technician_id]);

    // Add new assignments
    $stmt = $conn->prepare("INSERT INTO technician_barangays (technician_id, barangay_id) VALUES (?, ?)");
    foreach ($barangay_ids as $barangay_id) {
        $stmt->execute([$technician_id, $barangay_id]);
    }

    $conn->commit();

    $_SESSION['message'] = "✅ Barangay assignments updated successfully!";
    header("Location: ../manage_technicians.php");
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = "❌ Error: " . $e->getMessage();
    header("Location: ../manage_technicians.php");
    exit;
}
?>
