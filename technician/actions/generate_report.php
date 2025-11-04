<?php
include('../../dbconnection.php');
session_start();

try {
    // Prepare and execute query
    $stmt = $conn->prepare("SELECT * FROM farmer_summary");
    $stmt->execute();

    // Fetch all results as associative array
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output JSON
    header("Content-Type: application/json");
    echo json_encode($data);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "error" => "❌ Error generating report: " . $e->getMessage()
    ]);
}
?>
