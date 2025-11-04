<?php
require '../../dbconnection.php';
session_start();

try {
    $user_id = $_GET['id'];

    // Delete barangay assignment
    $stmt = $conn->prepare("DELETE FROM technician_barangays WHERE technician_id = ?");
    $stmt->execute([$user_id]);

    $_SESSION['message'] = "✅ Technician unassigned successfully!";
    header("Location: ../manage_technicians.php");
    exit;
} catch (Exception $e) {
    $_SESSION['error'] = "❌ Error: " . $e->getMessage();
    header("Location: ../manage_technicians.php");
    exit;
}
?>
