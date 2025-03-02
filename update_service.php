<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log'); // Log errors to php_error.log

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php';  // ✅ Now using MySQLi ($conn)

header("Content-Type: application/json");

// Get input data
$data = json_decode(file_get_contents("php://input"), true);
file_put_contents("debug.log", print_r($data, true), FILE_APPEND); // Log received data for debugging

// Validate input
if (!isset($data['service_id']) || !isset($data['price']) || !isset($data['description'])) {
    error_log("❌ Missing input data in update_service.php");
    echo json_encode(["success" => false, "error" => "Invalid input data", "received" => $data]);
    exit;
}

// Assign values
$service_id = $data['service_id'];
$price = $data['price'];
$description = $data['description'];

// Validate price
if (!is_numeric($price) || $price < 0) {
    error_log("❌ Invalid price value: " . $price);
    echo json_encode(["success" => false, "error" => "Invalid price value"]);
    exit;
}

// Debug log before running SQL
error_log("✅ Running SQL Update: service_id=$service_id, price=$price, description=$description");

// Execute SQL Update Query using MySQLi
$stmt = $conn->prepare("UPDATE services SET price = ?, description = ? WHERE service_id = ?");
if (!$stmt) {
    error_log("❌ SQL Prepare Error: " . $conn->error);
    echo json_encode(["success" => false, "error" => "Database prepare failed"]);
    exit;
}

$stmt->bind_param("dsi", $price, $description, $service_id);
$result = $stmt->execute();

if ($result) {
    error_log("✅ Service updated successfully!");
    echo json_encode(["success" => true, "message" => "Service updated successfully"]);
} else {
    error_log("❌ SQL Execute Error: " . $stmt->error);
    echo json_encode(["success" => false, "error" => "Database update failed"]);
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
