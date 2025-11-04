<?php
include('../../dbconnection.php');
session_start();

if (!isset($_GET['id'])) {
    header("Location: ../manage_farmers.php");
    exit;
}

$farmer_id = (int) $_GET['id'];

try {
    $stmt = $conn->prepare("DELETE FROM farmers WHERE farmer_id = :farmer_id");
    $stmt->execute([':farmer_id' => $farmer_id]);

    echo "<script>alert('✅ Farmer deleted successfully!'); window.location='../manage_farmers.php';</script>";

} catch (PDOException $e) {
    echo "<script>alert('❌ Error deleting farmer: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
}
?>
