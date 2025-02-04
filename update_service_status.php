<?php
header("Access-Control-Allow-Origin: *"); // Allow any domain to access
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Allow POST requests
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "db.php";

// Validate request
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['status'])) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit();
}

$id = intval($data['id']);
$status = $conn->real_escape_string($data['status']);

// Update service request status
$query = "UPDATE service_requests SET status = '$status' WHERE id = $id";
$result = $conn->query($query);

if ($result) {
    echo json_encode(["success" => true, "message" => "Status updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update status"]);
}

$conn->close();
?>
