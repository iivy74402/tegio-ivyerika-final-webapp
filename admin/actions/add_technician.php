<?php
require '../../dbconnection.php';
session_start();

try {
    $first_name = trim($_POST['first_name']);
    $middle_initial = trim($_POST['middle_initial'] ?? '');
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $barangay_id = $_POST['barangay_id'];

    $conn->beginTransaction();

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role, first_name, middle_initial, last_name) VALUES (?, ?, ?, 'technician', ?, ?, ?)");
    $stmt->execute([$username, $email, $password, $first_name, $middle_initial, $last_name]);
    $user_id = $conn->lastInsertId();

    // Assign barangay
    $stmt = $conn->prepare("INSERT INTO technician_barangays (technician_id, barangay_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $barangay_id]);

    $conn->commit();

    $_SESSION['message'] = "✅ Technician added successfully!";
    header("Location: ../manage_technicians.php");
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = "❌ Error: " . $e->getMessage();
    header("Location: ../manage_technicians.php");
    exit;
}
?>
