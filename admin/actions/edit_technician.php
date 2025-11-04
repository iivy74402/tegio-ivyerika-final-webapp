<?php
require '../../dbconnection.php';
session_start();

try {
    $user_id = $_POST['user_id'];
    $first_name = trim($_POST['first_name']);
    $middle_initial = trim($_POST['middle_initial'] ?? '');
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $barangay_id = $_POST['barangay_id'];

    $conn->beginTransaction();

    // Update user
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, first_name = ?, middle_initial = ?, last_name = ? WHERE user_id = ?");
    $stmt->execute([$username, $email, $first_name, $middle_initial, $last_name, $user_id]);

    // Update barangay assignment
    $conn->prepare("DELETE FROM technician_barangays WHERE technician_id = ?")->execute([$user_id]);
    if ($barangay_id) {
        $stmt = $conn->prepare("INSERT INTO technician_barangays (technician_id, barangay_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $barangay_id]);
    }

    $conn->commit();

    $_SESSION['message'] = "✅ Technician updated successfully!";
    header("Location: ../manage_technicians.php");
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = "❌ Error: " . $e->getMessage();
    header("Location: ../manage_technicians.php");
    exit;
}
?>
