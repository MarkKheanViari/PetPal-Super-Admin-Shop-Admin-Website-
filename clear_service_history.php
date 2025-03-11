<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php'; // Ensure this file contains your DB connection

// Get the JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Debug: Log the incoming request
file_put_contents("debug_clear_history.txt", "Incoming request: " . print_r($data, true) . "\n", FILE_APPEND);

// ✅ Check if `mobile_user_id` is provided
if (!isset($data['mobile_user_id'])) {
    file_put_contents("debug_clear_history.txt", "Error: Missing mobile_user_id\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Missing required field: mobile_user_id"]);
    exit();
}

$mobileUserId = intval($data['mobile_user_id']);

// ✅ Debug: Log received user ID
file_put_contents("debug_clear_history.txt", "Processing UserID=$mobileUserId\n", FILE_APPEND);

// ✅ DELETE only `Approved` and `Declined` appointments from `mobile_appointments`
$query = "DELETE FROM mobile_appointments WHERE mobile_user_id = ? AND status IN ('Approved', 'Declined')";
$stmt = $conn->prepare($query);

if (!$stmt) {
    file_put_contents("debug_clear_history.txt", "Error preparing statement: " . $conn->error . "\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Database error (prepare failed)"]);
    exit();
}

$stmt->bind_param("i", $mobileUserId);

if ($stmt->execute()) {
    file_put_contents("debug_clear_history.txt", "Query executed successfully. Rows affected: " . $stmt->affected_rows . "\n", FILE_APPEND);
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Approved & Declined appointments cleared successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "No approved or declined appointments found to delete"]);
    }
} else {
    file_put_contents("debug_clear_history.txt", "Error executing query: " . $stmt->error . "\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Database error while clearing history"]);
}

// Close connections
$stmt->close();
$conn->close();
?>
