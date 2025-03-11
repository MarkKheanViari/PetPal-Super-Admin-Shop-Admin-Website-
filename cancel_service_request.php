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

// ✅ Check if required fields are present
if (!isset($data['mobile_user_id'], $data['service_name'], $data['service_type'], $data['appointment_date'])) {
    file_put_contents("debug_cancel_request.txt", "❌ Missing required fields\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit();
}

$mobileUserId = $data['mobile_user_id'];
$serviceName = $data['service_name'];
$serviceType = $data['service_type'];
$appointmentDate = $data['appointment_date'];

// ✅ Debug: Log received values
file_put_contents("debug_cancel_request.txt", "Received: UserID=$mobileUserId, Service=$serviceName, Type=$serviceType, Date=$appointmentDate\n", FILE_APPEND);

// ✅ Begin transaction to ensure both deletions happen together
$conn->begin_transaction();

try {
    // ✅ Delete from `appointments`
    $query_appointments = "DELETE FROM appointments WHERE mobile_user_id = ? AND service_name = ? AND service_type = ? AND appointment_date = ?";
    $stmt_appointments = $conn->prepare($query_appointments);
    $stmt_appointments->bind_param("isss", $mobileUserId, $serviceName, $serviceType, $appointmentDate);
    
    if (!$stmt_appointments->execute()) {
        throw new Exception("Error deleting from `appointments`: " . $stmt_appointments->error);
    }
    
    // ✅ Delete from `mobile_appointments`
    $query_mobile = "DELETE FROM mobile_appointments WHERE mobile_user_id = ? AND service_name = ? AND service_type = ? AND appointment_date = ?";
    $stmt_mobile = $conn->prepare($query_mobile);
    $stmt_mobile->bind_param("isss", $mobileUserId, $serviceName, $serviceType, $appointmentDate);
    
    if (!$stmt_mobile->execute()) {
        throw new Exception("Error deleting from `mobile_appointments`: " . $stmt_mobile->error);
    }

    // ✅ Check if at least one record was deleted from both tables
    if ($stmt_appointments->affected_rows > 0 && $stmt_mobile->affected_rows > 0) {
        $conn->commit(); // ✅ Commit transaction
        echo json_encode(["success" => true, "message" => "Appointment deleted from both tables"]);
    } else {
        throw new Exception("❌ No matching appointment found in one or both tables");
    }
} catch (Exception $e) {
    $conn->rollback(); // ❌ Rollback changes on failure
    file_put_contents("debug_cancel_request.txt", "❌ Error: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

// ✅ Close connections
$stmt_appointments->close();
$stmt_mobile->close();
$conn->close();
?>
