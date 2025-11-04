<?php
require '../../dbconnection.php';

session_start();

try {
    $technician_id = $_POST['technician_id'];
    $barangay_id = $_POST['barangay_id'];

    // Check if already assigned
    $stmt = $conn->prepare("SELECT * FROM technician_barangays WHERE technician_id = ?");
    $stmt->execute([$technician_id]);

    if ($stmt->rowCount() > 0) {
        // Update
        $update = $conn->prepare("UPDATE technician_barangays SET barangay_id = ? WHERE technician_id = ?");
        $update->execute([$barangay_id, $technician_id]);
    } else {
        // Insert new assignment
        $insert = $conn->prepare("INSERT INTO technician_barangays (technician_id, barangay_id) VALUES (?, ?)");
        $insert->execute([$technician_id, $barangay_id]);
    }

    $_SESSION['message'] = "✅ Technician successfully assigned to barangay.";
    header("Location: ../manage_technicians.php");
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = "❌ Error assigning technician: " . $e->getMessage();
    header("Location: ../manage_technicians.php");
    exit;
}
?>
