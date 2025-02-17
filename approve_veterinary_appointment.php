<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "db.php"; // Database connection

// Get the raw input
$data = json_decode(file_get_contents("php://input"), true);

// ✅ Debugging: Log received data
file_put_contents("debug_log.txt", print_r($data, true));

// Check if appointment ID and status exist
if (!isset($data["appointment_id"]) || !isset($data["status"])) {
    echo json_encode(["success" => false, "message" => "Missing appointment ID or status"]);
    exit();
}

$appointment_id = $data["appointment_id"];
$status = $data["status"];

// Update appointment status in database
$query = "UPDATE veterinary_appointments SET status = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $status, $appointment_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Appointment status updated to $status"]);
} else {
    echo json_encode(["success" => false, "message" => "Database update failed"]);
}

$stmt->close();
$conn->close();
?>
