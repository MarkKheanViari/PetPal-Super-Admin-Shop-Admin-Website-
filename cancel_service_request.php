<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php'; // Ensure this file contains your database connection

// Get the JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Debug: Log the incoming request
file_put_contents("debug_cancel_request.txt", print_r($data, true), FILE_APPEND);

// Check if required fields are present
if (!isset($data['mobile_user_id'], $data['service_name'], $data['service_type'], $data['appointment_date'])) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit();
}

$mobileUserId = $data['mobile_user_id'];
$serviceName = $data['service_name'];
$serviceType = $data['service_type'];
$appointmentDate = $data['appointment_date'];

// Debug: Log received values
file_put_contents("debug_cancel_request.txt", "Received: UserID=$mobileUserId, Service=$serviceName, Type=$serviceType, Date=$appointmentDate\n", FILE_APPEND);

// ✅ Delete the appointment
$query = "DELETE FROM appointments WHERE mobile_user_id = ? AND service_name = ? AND service_type = ? AND appointment_date = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("isss", $mobileUserId, $serviceName, $serviceType, $appointmentDate);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Appointment deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "No matching appointment found"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Database error"]);
}

// Close connections
$stmt->close();
$conn->close();
?>
