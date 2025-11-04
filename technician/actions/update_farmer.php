<?php
include('../../dbconnection.php');
session_start();

if (!isset($_POST['update_farmer'])) {
    header("Location: ../manage_farmers.php");
    exit;
}

$farmer_id = (int) $_POST['farmer_id'];
$first_name = trim($_POST['first_name']);
$middle_initial = trim($_POST['middle_initial']);
$last_name = trim($_POST['last_name']);
$cellphone = trim($_POST['cellphone']);
$street = trim($_POST['street']);
$barangay_id = (int) $_POST['barangay_id'];

try {
    // Start transaction
    $conn->beginTransaction();

    // Get address_id of the farmer
    $stmtAddressId = $conn->prepare("SELECT address_id FROM farmers WHERE farmer_id = :farmer_id");
    $stmtAddressId->execute([':farmer_id' => $farmer_id]);
    $address = $stmtAddressId->fetch(PDO::FETCH_ASSOC);

    if (!$address) {
        throw new Exception("Farmer not found.");
    }

    $address_id = $address['address_id'];

    //  Update address
    $stmtUpdateAddress = $conn->prepare("
        UPDATE addresses 
        SET street = :street, barangay_id = :barangay_id
        WHERE address_id = :address_id
    ");
    $stmtUpdateAddress->execute([
        ':street' => $street,
        ':barangay_id' => $barangay_id,
        ':address_id' => $address_id
    ]);

    //  Update farmer details
    $stmtUpdateFarmer = $conn->prepare("
        UPDATE farmers 
        SET first_name = :first_name,
            middle_initial = :middle_initial,
            last_name = :last_name,
            cellphone = :cellphone
        WHERE farmer_id = :farmer_id
    ");
    $stmtUpdateFarmer->execute([
        ':first_name' => $first_name,
        ':middle_initial' => $middle_initial,
        ':last_name' => $last_name,
        ':cellphone' => $cellphone,
        ':farmer_id' => $farmer_id
    ]);

    // Commit transaction
    $conn->commit();

    echo "<script>alert('✅ Farmer updated successfully!'); window.location='../manage_farmers.php';</script>";

} catch (Exception $e) {
    $conn->rollBack();
    echo "<script>alert('❌ Error updating farmer: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
}
?>
