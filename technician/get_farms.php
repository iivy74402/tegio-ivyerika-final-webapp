<?php
require '../dbconnection.php';

if (isset($_GET['farmer_id'])) {
    $farmer_id = intval($_GET['farmer_id']);
    $stmt = $conn->prepare("SELECT farm_id FROM farms WHERE farmer_id = ? LIMIT 1");
    $stmt->execute([$farmer_id]);
    $farm = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($farm) {
        echo json_encode(['success' => true, 'farm_id' => $farm['farm_id']]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
