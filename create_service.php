<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$type = $conn->real_escape_string($data['type']);
$service_name = $conn->real_escape_string($data['service_name']);
$price = $conn->real_escape_string($data['price']);
$description = $conn->real_escape_string($data['description']);

$sql = "INSERT INTO services (type, service_name, price, description) 
        VALUES ('$type', '$service_name', '$price', '$description')";

if ($conn->query($sql)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$conn->close();
?>
