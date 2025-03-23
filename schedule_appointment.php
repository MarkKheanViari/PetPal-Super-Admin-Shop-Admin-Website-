<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php'; // Your database connection file

// Get JSON input from the app
$data = json_decode(file_get_contents("php://input"), true);

// Check for required fields
if (!isset($data['mobile_user_id'], $data['service_type'], $data['service_name'], $data['name'], $data['address'], 
           $data['phone_number'], $data['pet_name'], $data['pet_breed'], $data['appointment_date'], 
           $data['appointment_time'], $data['payment_method'])) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit();
}

// Extract data from the JSON
$mobileUserId = $data['mobile_user_id'];
$serviceType = $data['service_type'];
$serviceName = $data['service_name'];
$name = $data['name'];
$address = $data['address'];
$phoneNumber = $data['phone_number'];
$petName = $data['pet_name'];
$petBreed = $data['pet_breed'];
$appointmentDate = $data['appointment_date'];
$appointmentTime = $data['appointment_time'];
$paymentMethod = $data['payment_method'];
$notes = $data['notes'] ?? "";

// Insert into appointments table
$query = "INSERT INTO appointments (mobile_user_id, service_type, service_name, name, address, phone_number, pet_name, pet_breed, appointment_date, appointment_time, payment_method, notes, status) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
    exit();
}
// Corrected bind_param with 12 types for 12 variables
$stmt->bind_param("isssssssssss", $mobileUserId, $serviceType, $serviceName, $name, $address, $phoneNumber, $petName, $petBreed, $appointmentDate, $appointmentTime, $paymentMethod, $notes);
if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Execute failed: " . $stmt->error]);
    exit();
}

// Insert into mobile_appointments table
$queryMobile = "INSERT INTO mobile_appointments (mobile_user_id, service_type, service_name, name, address, phone_number, pet_name, pet_breed, appointment_date, appointment_time, payment_method, notes, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
$stmtMobile = $conn->prepare($queryMobile);
if (!$stmtMobile) {
    echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
    exit();
}
// Corrected bind_param with 12 types for 12 variables
$stmtMobile->bind_param("isssssssssss", $mobileUserId, $serviceType, $serviceName, $name, $address, $phoneNumber, $petName, $petBreed, $appointmentDate, $appointmentTime, $paymentMethod, $notes);
if (!$stmtMobile->execute()) {
    echo json_encode(["success" => false, "message" => "Execute failed: " . $stmtMobile->error]);
    exit();
}

// Send success response
echo json_encode(["success" => true, "message" => "Appointment scheduled successfully"]);

// Clean up
$stmt->close();
$stmtMobile->close();
$conn->close();
?>