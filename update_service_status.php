<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "db.php"; // Ensure database connection

// Read raw POST data
$inputJSON = file_get_contents("php://input");
file_put_contents("debug_log.txt", "[" . date("Y-m-d H:i:s") . "] Raw Input: " . $inputJSON . PHP_EOL, FILE_APPEND);

$input = json_decode($inputJSON, true);

// Check if JSON is valid
if (!$input || !isset($input["id"]) || !isset($input["status"])) {
    die(json_encode(["success" => false, "message" => "Invalid JSON input"]));
}

$requestId = intval($input["id"]);
$status = $conn->real_escape_string($input["status"]);

// Check if request ID exists
$checkQuery = "SELECT * FROM service_requests WHERE id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $requestId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die(json_encode(["success" => false, "message" => "Service request not found"]));
}

// Update status
$updateQuery = "UPDATE service_requests SET status = ? WHERE id = ?";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param("si", $status, $requestId);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Service request updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update request"]);
}

$conn->close();
?>
