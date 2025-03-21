<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: text/html");

include "db.php";

if (!isset($_GET['appointment_data'])) {
    error_log("❌ paymongo_appointment_success.php: Missing appointment data");
    echo json_encode(["success" => false, "message" => "❌ Missing appointment data"]);
    exit();
}

// Decode the appointment data
$encoded_appointment_data = $_GET['appointment_data'];
$appointment_data = json_decode(base64_decode($encoded_appointment_data), true);

if (!$appointment_data || !isset($appointment_data['mobile_user_id'], $appointment_data['service_type'], $appointment_data['service_name'], 
    $appointment_data['name'], $appointment_data['address'], $appointment_data['phone_number'], $appointment_data['pet_name'], 
    $appointment_data['pet_breed'], $appointment_data['appointment_date'], $appointment_data['payment_method'])) {
    error_log("❌ paymongo_appointment_success.php: Invalid or incomplete appointment data");
    echo json_encode(["success" => false, "message" => "❌ Invalid appointment data"]);
    exit();
}

$mobile_user_id = $appointment_data['mobile_user_id'];
$service_type = $appointment_data['service_type'];
$service_name = $appointment_data['service_name'];
$name = $appointment_data['name'];
$address = $appointment_data['address'];
$phone_number = $appointment_data['phone_number'];
$pet_name = $appointment_data['pet_name'];
$pet_breed = $appointment_data['pet_breed'];
$appointment_date = $appointment_data['appointment_date'];
$payment_method = $appointment_data['payment_method'];
$notes = $appointment_data['notes'] ?? "";
$status = "PAID"; // Set to PAID since payment is successful

// Start a transaction
$conn->begin_transaction();

try {
    // Insert into `appointments`
    $query = "INSERT INTO appointments (mobile_user_id, service_type, service_name, name, address, phone_number, pet_name, pet_breed, appointment_date, payment_method, notes, status) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssssssssss", $mobile_user_id, $service_type, $service_name, $name, $address, $phone_number, $pet_name, $pet_breed, $appointment_date, $payment_method, $notes, $status);
    $stmt->execute();
    $appointment_id = $stmt->insert_id;
    $stmt->close();

    // Insert into `mobile_appointments`
    $queryMobile = "INSERT INTO mobile_appointments (mobile_user_id, service_type, service_name, name, address, phone_number, pet_name, pet_breed, appointment_date, payment_method, notes, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtMobile = $conn->prepare($queryMobile);
    $stmtMobile->bind_param("isssssssssss", $mobile_user_id, $service_type, $service_name, $name, $address, $phone_number, $pet_name, $pet_breed, $appointment_date, $payment_method, $notes, $status);
    $stmtMobile->execute();
    $stmtMobile->close();

    // Commit the transaction
    $conn->commit();
    error_log("✅ paymongo_appointment_success.php: Appointment ID $appointment_id created with status PAID");

    // Redirect to deep link
    $deep_link = "petpal://appointment/success?appointment_id=$appointment_id";
    header("Location: $deep_link");
    exit();
} catch (Exception $e) {
    // Rollback on failure
    $conn->rollback();
    error_log("❌ paymongo_appointment_success.php: Failed to process appointment - " . $e->getMessage());
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();
?>