<?php
include 'db.php'; // Ensure database connection

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// ✅ Enable error reporting for debugging (Remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'debug.log'); // Log errors to `debug.log`

// ✅ Log incoming request
file_put_contents("debug.log", "🔍 Incoming Request: " . file_get_contents("php://input") . "\n", FILE_APPEND);

// ✅ Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
$service_name = $data["service_name"] ?? null;

if (!$service_name) {
    error_log("❌ Error: Invalid service name received.");
    echo json_encode(["success" => false, "error" => "Invalid service name"]);
    exit();
}

// ✅ Check if the service exists
$checkStmt = $conn->prepare("SELECT COUNT(*) FROM services WHERE service_name = ?");
$checkStmt->bind_param("s", $service_name);
$checkStmt->execute();
$checkStmt->bind_result($count);
$checkStmt->fetch();
$checkStmt->close();

if ($count == 0) {
    error_log("❌ Error: Service '$service_name' not found in database.");
    echo json_encode(["success" => false, "error" => "Service not found"]);
    exit();
}

// ✅ Update service to be marked as removed instead of deleting
$stmt = $conn->prepare("UPDATE services SET removed = 1 WHERE service_name = ?");
if (!$stmt) {
    error_log("❌ Error: SQL Prepare Failed - " . $conn->error);
    echo json_encode(["success" => false, "error" => "SQL error"]);
    exit();
}

$stmt->bind_param("s", $service_name);
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Service removed successfully"]);
    error_log("✅ Service '$service_name' removed successfully.");
} else {
    error_log("❌ Error: SQL Execution Failed - " . $stmt->error);
    echo json_encode(["success" => false, "error" => "Failed to remove service"]);
}

$stmt->close();
$conn->close();
?>
