<?php
include 'db.php'; // Ensure database connection

header("Content-Type: application/json");

// Read incoming request
$data = json_decode(file_get_contents("php://input"), true);

// Log received data
error_log("📥 Received Service Request: " . print_r($data, true));

// Check if required fields are present
if (!isset($data["service_id"], $data["user_id"], $data["selected_date"])) {
    error_log("❌ Error: Missing Fields");
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit;
}

$service_id = $data["service_id"];
$user_id = $data["user_id"];
$selected_date = $data["selected_date"];

// ✅ Debugging - Check database connection
if (!$conn) {
    error_log("❌ Database Connection Failed: " . mysqli_connect_error());
    echo json_encode(["success" => false, "message" => "Database connection error"]);
    exit;
}

// ✅ Insert into service_requests table
$query = "INSERT INTO service_requests (service_id, user_id, selected_date, status) VALUES (?, ?, ?, 'pending')";
$stmt = $conn->prepare($query);

if (!$stmt) {
    error_log("❌ SQL Prepare Error: " . $conn->error);
    echo json_encode(["success" => false, "message" => "SQL Prepare Error: " . $conn->error]);
    exit;
}

$stmt->bind_param("iis", $service_id, $user_id, $selected_date);

if ($stmt->execute()) {
    error_log("✅ Service Request Stored: Service ID = $service_id, User ID = $user_id, Date = $selected_date");
    echo json_encode(["success" => true, "message" => "Service request submitted"]);
} else {
    error_log("❌ Database Insert Error: " . $stmt->error);
    echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
}
?>
