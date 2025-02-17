<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

include "db.php"; // Ensure database connection

$response = array();

// Get raw POST data and decode it
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    $response["success"] = false;
    $response["message"] = "Invalid JSON data!";
    echo json_encode($response);
    exit();
}

// Debug: Log received data
file_put_contents("grooming_debug_log.txt", print_r($data, true));

if (!isset($data['name'], $data['address'], $data['phone_number'], 
          $data['pet_name'], $data['pet_breed'], $data['groom_type'], 
          $data['notes'], $data['appointment_date'], $data['payment_method'])) {
    $response["success"] = false;
    $response["message"] = "Missing required fields!";
    echo json_encode($response);
    exit();
}

// Collect data
$name = $data['name'];
$address = $data['address'];
$phone_number = $data['phone_number'];
$pet_name = $data['pet_name'];
$pet_breed = $data['pet_breed'];
$groom_type = $data['groom_type'];
$notes = $data['notes'];
$appointment_date = $data['appointment_date'];
$payment_method = $data['payment_method'];

// Convert MM/DD/YYYY to YYYY-MM-DD format (for MySQL)
$dateParts = explode("/", $appointment_date);
if (count($dateParts) == 3) {
    $appointment_date = $dateParts[2] . "-" . $dateParts[0] . "-" . $dateParts[1]; // Convert to MySQL format
} else {
    $response["success"] = false;
    $response["message"] = "Invalid date format!";
    echo json_encode($response);
    exit();
}

// Debug: Log formatted date
file_put_contents("grooming_debug_log.txt", "\nFormatted Date: " . $appointment_date, FILE_APPEND);

// Insert data into database
$query = "INSERT INTO grooming_appointments 
          (name, address, phone_number, pet_name, pet_breed, groom_type, notes, appointment_date, payment_method) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("sssssssss", $name, $address, $phone_number, $pet_name, $pet_breed, $groom_type, $notes, $appointment_date, $payment_method);

if ($stmt->execute()) {
    $response["success"] = true;
    $response["message"] = "Appointment scheduled successfully!";
} else {
    $response["success"] = false;
    $response["message"] = "Error scheduling appointment: " . $stmt->error;
}

// Debug: Log database response
file_put_contents("grooming_debug_log.txt", "\nDB Response: " . json_encode($response), FILE_APPEND);

$stmt->close();
$conn->close();
echo json_encode($response);
?>
