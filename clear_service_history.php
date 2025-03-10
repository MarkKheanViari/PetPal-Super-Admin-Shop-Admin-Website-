<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php'; // Ensure this file contains your DB connection

// Get the JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Debug: Log the incoming request
file_put_contents("debug_clear_history.txt", print_r($data, true), FILE_APPEND);

// Check if required field is present
if (!isset($data['mobile_user_id'])) {
    echo json_encode(["success" => false, "message" => "Missing required field: mobile_user_id"]);
    exit();
}

$mobileUserId = $data['mobile_user_id'];

// Debug: Log received user ID
file_put_contents("debug_clear_history.txt", "Received UserID=$mobileUserId\n", FILE_APPEND);

// ✅ Instead of deleting, update the status to "Cleared"
$query = "UPDATE appointments SET status = 'Cleared' WHERE mobile_user_id = ? AND status IN ('Pending', 'Approved', 'Declined')";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $mobileUserId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Service history cleared successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "No appointments found to update"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Database error"]);
}

// Close connections
$stmt->close();
$conn->close();
?>
