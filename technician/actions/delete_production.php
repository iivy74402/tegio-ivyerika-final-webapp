<?php
require '../../dbconnection.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM production_records WHERE production_id = ?");
    $stmt->execute([$id]);
}

header("Location: ../record_production.php");
exit;
?>
