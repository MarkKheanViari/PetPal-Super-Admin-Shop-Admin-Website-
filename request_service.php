<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'db.php'; // Ensure database connection

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// ✅ Capture and log raw JSON input
$input = file_get_contents("php://input");
file_put_contents("debug_log.txt", "[" . date("Y-m-d H:i:s") . "] RAW INPUT: " . $input . PHP_EOL, FILE_APPEND);

$data = json_decode($input, true);

// ✅ Check for JSON errors
if (json_last_error() !== JSON_ERROR_NONE) {
    file_put_contents("debug_log.txt", "❌ JSON ERROR: " . json_last_error_msg() . PHP_EOL, FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Invalid JSON format"]);
    exit;
}

// ✅ Validate required fields
if (!isset($data['mobile_user_id']) || !isset($data['service_id']) || !isset($data['selected_date']) || !isset($data['status'])) {
    file_put_contents("debug_log.txt", "❌ MISSING PARAMETERS" . PHP_EOL, FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Missing parameters"]);
    exit;
}

$mobile_user_id = intval($data['mobile_user_id']);
$service_id = intval($data['service_id']);
$selected_date = $data['selected_date'];
$status = $data['status'];

// ✅ Check if service exists
$checkService = $conn->prepare("SELECT id FROM services WHERE id = ?");
$checkService->bind_param("i", $service_id);
$checkService->execute();
$serviceResult = $checkService->get_result();

if ($serviceResult->num_rows == 0) {
    file_put_contents("debug_log.txt", "❌ SERVICE NOT FOUND: ID $service_id" . PHP_EOL, FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Service not found"]);
    exit;
}

// ✅ Insert into service_requests table
$insertService = $conn->prepare("INSERT INTO service_requests (mobile_user_id, service_id, selected_date, status) VALUES (?, ?, ?, ?)");
$insertService->bind_param("iiss", $mobile_user_id, $service_id, $selected_date, $status);

if (!$insertService->execute()) {
    file_put_contents("debug_log.txt", "❌ INSERT SERVICE ERROR: " . $conn->error . PHP_EOL, FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Failed to request service"]);
    exit;
}

file_put_contents("debug_log.txt", "✅ SERVICE REQUEST SUCCESS: User $mobile_user_id, Service $service_id, Date $selected_date" . PHP_EOL, FILE_APPEND);
echo json_encode(["success" => true, "message" => "Service requested successfully"]);

$conn->close();
?>
