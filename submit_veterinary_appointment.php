<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

include "db.php"; 

$response = array();

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    $response["success"] = false;
    $response["message"] = "Invalid JSON data!";
    echo json_encode($response);
    exit();
}

if (!isset($data['user_id'], $data['name'], $data['address'], $data['phone_number'], 
          $data['pet_name'], $data['pet_breed'], $data['checkup_type'], 
          $data['notes'], $data['appointment_date'], $data['payment_method'])) {
    $response["success"] = false;
    $response["message"] = "Missing required fields!";
    echo json_encode($response);
    exit();
}

$user_id = $data['user_id'];
$name = $data['name'];
$address = $data['address'];
$phone_number = $data['phone_number'];
$pet_name = $data['pet_name'];
$pet_breed = $data['pet_breed'];
$checkup_type = $data['checkup_type'];
$notes = $data['notes'];
$appointment_date = $data['appointment_date'];
$payment_method = $data['payment_method'];

$query = "INSERT INTO veterinary_appointments 
          (user_id, name, address, phone_number, pet_name, pet_breed, checkup_type, notes, appointment_date, payment_method) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("isssssssss", $user_id, $name, $address, $phone_number, $pet_name, $pet_breed, $checkup_type, $notes, $appointment_date, $payment_method);

if ($stmt->execute()) {
    $response["success"] = true;
    $response["message"] = "Appointment scheduled successfully!";
} else {
    $response["success"] = false;
    $response["message"] = "Error scheduling appointment: " . $stmt->error;
}

$stmt->close();
$conn->close();
echo json_encode($response);
?>
