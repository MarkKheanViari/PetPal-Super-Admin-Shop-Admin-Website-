<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require_once "db.php";

// Handle preflight request
if ($_SERVER["REQUEST_METHOD"] == "OPTIONS") {
    http_response_code(204);
    exit();
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['id'])) {
    echo json_encode(["success" => false, "message" => "Missing request ID"]);
    exit();
}

$id = intval($data['id']);

// ✅ Cancel by deleting the request (or update status to 'canceled' instead)
$query = "DELETE FROM service_requests WHERE id = $id"; 
if ($conn->query($query) === TRUE) {
    echo json_encode(["success" => true, "message" => "Request cancelled successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to cancel request: " . $conn->error]);
}

$conn->close();
?>
