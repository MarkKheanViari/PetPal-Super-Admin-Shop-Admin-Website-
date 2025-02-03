<?php
include 'db.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

error_reporting(E_ALL);
ini_set('display_errors', 1);

$sql = "SELECT id, shop_owner_id, service_name, description, price, status FROM services WHERE status = 'Available'";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "error" => "SQL Prepare failed: " . $conn->error]);
    exit();
}

$stmt->execute();
$result = $stmt->get_result();

$services = [];
while ($row = $result->fetch_assoc()) {
    $services[] = $row;
}

echo json_encode($services, JSON_PRETTY_PRINT);

$stmt->close();
$conn->close();
?>
