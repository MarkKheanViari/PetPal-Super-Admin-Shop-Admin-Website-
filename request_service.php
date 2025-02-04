<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

// Check if request is POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

// Read request body
$data = json_decode(file_get_contents("php://input"), true);

// Debugging: Log received data
file_put_contents("debug.log", print_r($data, true), FILE_APPEND);

// Check if required fields are present
if (!isset($data["service_id"], $data["user_id"], $data["selected_date"])) {
    echo json_encode(["success" => false, "message" => "Missing parameters"]);
    exit;
}

// Connect to database
require_once "db.php";

// Get variables from request
$service_id = $data["service_id"];
$user_id = $data["user_id"];
$selected_date = $data["selected_date"];

// Debugging: Log SQL query attempt
file_put_contents("debug.log", "Attempting to insert request into database\n", FILE_APPEND);

$query = "INSERT INTO service_requests (service_id, user_id, selected_date, status) VALUES (?, ?, ?, 'pending')";
$stmt = $conn->prepare($query);

if (!$stmt) {
    file_put_contents("debug.log", "Prepare failed: " . $conn->error . "\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Prepare statement failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("iis", $service_id, $user_id, $selected_date);

// Execute query
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Service request added"]);
} else {
    file_put_contents("debug.log", "Execution failed: " . $stmt->error . "\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
}

// Close connection
$stmt->close();
$conn->close();
?>
