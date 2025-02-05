<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once "db.php"; // Ensure database connection

// Read the request body
$data = json_decode(file_get_contents("php://input"), true);

// Check if service_id is provided
if (!isset($data['service_id'])) {
    echo json_encode(["success" => false, "message" => "Missing service_id"]);
    exit;
}

$service_id = intval($data['service_id']);

// Update the service request status to "canceled"
$query = "UPDATE service_requests SET status='canceled' WHERE id=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $service_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Service request canceled"]);
} else {
    echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>
