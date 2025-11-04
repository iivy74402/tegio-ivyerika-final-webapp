<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'technician') {
    header("Location: ../login.php");
    exit;
}

// Technician’s assigned barangay (used for filters)
$barangay_id = $_SESSION['barangay_id'];
$barangay_name = $_SESSION['barangay_name'];
?>
