<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

include "db.php";

$response = ["success" => false, "message" => "Unknown error"];

$rawData = file_get_contents("php://input");
error_log("📦 Raw Input: " . $rawData);
$data = json_decode($rawData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["success" => false, "message" => "❌ JSON Decode Error: " . json_last_error_msg()]);
    exit();
}

// Validate required field
if (!isset($data['mobile_user_id'])) {
    echo json_encode(["success" => false, "message" => "❌ Missing mobile_user_id"]);
    exit();
}

$mobile_user_id = intval($data['mobile_user_id']);

// Delete all cart items for the user
$query = "DELETE FROM cart WHERE mobile_user_id = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "❌ Prepare Error: " . $conn->error]);
    exit();
}

$stmt->bind_param("i", $mobile_user_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "✅ Cart cleared successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "❌ Error clearing cart: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>