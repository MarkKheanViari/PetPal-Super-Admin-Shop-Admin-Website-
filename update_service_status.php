<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once "db.php";

// Handle preflight (OPTIONS) request
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(204);
    exit();
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id']) || !isset($data['status'])) {
    echo json_encode(["success" => false, "message" => "Invalid request - Missing parameters"]);
    exit();
}

$id = intval($data['id']);
$status = $conn->real_escape_string($data['status']);

// Debugging logs
error_log("Received ID: $id, Status: $status");

// Ensure only allowed statuses are updated
$allowedStatuses = ["pending", "confirmed", "declined"];
if (!in_array($status, $allowedStatuses)) {
    echo json_encode(["success" => false, "message" => "Invalid status value"]);
    exit();
}

// Check if the ID exists before updating
$checkQuery = "SELECT * FROM service_requests WHERE id = $id";
$checkResult = $conn->query($checkQuery);
if ($checkResult->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "No matching ID found"]);
    exit();
}

// Force the status update
$query = "UPDATE service_requests SET status = '$status' WHERE id = $id";
if ($conn->query($query) === TRUE) {
    echo json_encode(["success" => true, "message" => "Status updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Database update failed: " . $conn->error]);
}

$conn->close();
?>
