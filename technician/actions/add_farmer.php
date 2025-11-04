<?php
include('../../dbconnection.php');
session_start();

if (!isset($_POST['add_farmer'])) {
    header("Location: ../manage_farmers.php");
    exit;
}

$id_number = trim($_POST['id_number']);
$first_name = trim($_POST['first_name']);
$middle_initial = trim($_POST['middle_initial']);
$last_name = trim($_POST['last_name']);
$sex = trim($_POST['sex']);
$barangay_id = (int) $_POST['barangay_id'];
$street = trim($_POST['street']);
$cellphone = trim($_POST['cellphone']);

try {
    // Start transaction
    $conn->beginTransaction();

    // Step 1: Insert address
    $stmtAddress = $conn->prepare("
        INSERT INTO addresses (street, barangay_id)
        VALUES (:street, :barangay_id)
    ");
    $stmtAddress->execute([
        ':street' => $street,
        ':barangay_id' => $barangay_id
    ]);
    $address_id = $conn->lastInsertId();

    // Insert farmer
    $stmtFarmer = $conn->prepare("
        INSERT INTO farmers (id_number, first_name, middle_initial, last_name, sex, address_id, cellphone)
        VALUES (:id_number, :first_name, :middle_initial, :last_name, :sex, :address_id, :cellphone)
    ");
    $stmtFarmer->execute([
        ':id_number' => $id_number,
        ':first_name' => $first_name,
        ':middle_initial' => $middle_initial,
        ':last_name' => $last_name,
        ':sex' => $sex,
        ':address_id' => $address_id,
        ':cellphone' => $cellphone
    ]);

    // Commit the transaction
    $conn->commit();

    echo "<script>alert('✅ Farmer added successfully!'); window.location='../manage_farmers.php';</script>";

} catch (PDOException $e) {
    $conn->rollBack();
    echo "<script>alert('❌ Error inserting data: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
}
?>
