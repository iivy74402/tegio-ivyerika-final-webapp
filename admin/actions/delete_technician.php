<?php
require '../../dbconnection.php';
session_start();

try {
    $user_id = $_GET['id'];

    $conn->beginTransaction();

    // Delete barangay assignment
    $stmt = $conn->prepare("DELETE FROM technician_barangays WHERE technician_id = ?");
    $stmt->execute([$user_id]);

    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);

    $conn->commit();

    $_SESSION['message'] = "✅ Technician deleted successfully!";
    header("Location: ../manage_technicians.php");
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = "❌ Error: " . $e->getMessage();
    header("Location: ../manage_technicians.php");
    exit;
}
?>
