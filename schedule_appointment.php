<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php'; // Ensure this file connects to the database

// Get the JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Debugging: Log request data
file_put_contents("debug_schedule_appointment.txt", print_r($data, true), FILE_APPEND);

// Check required fields
if (!isset($data['mobile_user_id'], $data['service_type'], $data['service_name'], $data['name'], $data['address'], 
           $data['phone_number'], $data['pet_name'], $data['pet_breed'], $data['appointment_date'], $data['payment_method'])) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit();
}

$mobileUserId = $data['mobile_user_id'];
$serviceType = $data['service_type'];
$serviceName = $data['service_name'];
$name = $data['name'];
$address = $data['address'];
$phoneNumber = $data['phone_number'];
$petName = $data['pet_name'];
$petBreed = $data['pet_breed'];
$appointmentDate = $data['appointment_date'];
$paymentMethod = $data['payment_method'];
$notes = $data['notes'] ?? "";

// Insert into `appointments`
$query = "INSERT INTO appointments (mobile_user_id, service_type, service_name, name, address, phone_number, pet_name, pet_breed, appointment_date, payment_method, notes, status) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
$stmt = $conn->prepare($query);
$stmt->bind_param("isssssssssss", $mobileUserId, $serviceType, $serviceName, $name, $address, $phoneNumber, $petName, $petBreed, $appointmentDate, $paymentMethod, $notes);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Database error: Could not insert into appointments"]);
    exit();
}

// Insert into `mobile_appointments`
$queryMobile = "INSERT INTO mobile_appointments (mobile_user_id, service_type, service_name, name, address, phone_number, pet_name, pet_breed, appointment_date, payment_method, notes, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
$stmtMobile = $conn->prepare($queryMobile);
$stmtMobile->bind_param("isssssssssss", $mobileUserId, $serviceType, $serviceName, $name, $address, $phoneNumber, $petName, $petBreed, $appointmentDate, $paymentMethod, $notes);

if (!$stmtMobile->execute()) {
    echo json_encode(["success" => false, "message" => "Database error: Could not insert into mobile_appointments"]);
    exit();
}

// Success response
echo json_encode(["success" => true, "message" => "Appointment scheduled successfully"]);

// Close connections
$stmt->close();
$stmtMobile->close();
$conn->close();
?>