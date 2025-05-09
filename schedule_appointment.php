<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php'; // Database connection

$response = array();

// Check if request is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response["success"] = false;
    $response["message"] = "Invalid request method";
    echo json_encode($response);
    exit();
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields except address & phone_number
$requiredFields = ["mobile_user_id", "name", "pet_name", "pet_breed", "service_type", "service_name", "appointment_date", "payment_method"];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        $response["success"] = false;
        $response["message"] = "Missing required field: $field";
        echo json_encode($response);
        exit();
    }
}

// Assign values
$mobile_user_id = intval($data["mobile_user_id"]);
$name = $conn->real_escape_string($data["name"]);
$pet_name = $conn->real_escape_string($data["pet_name"]);
$pet_breed = $conn->real_escape_string($data["pet_breed"]);
$service_type = $conn->real_escape_string($data["service_type"]);
$service_name = $conn->real_escape_string($data["service_name"]);
$notes = isset($data["notes"]) ? $conn->real_escape_string($data["notes"]) : "";
$appointment_date = $conn->real_escape_string($data["appointment_date"]);
$payment_method = $conn->real_escape_string($data["payment_method"]);

// Default Address & Phone Number (Empty for now)
$address = isset($data["address"]) ? $conn->real_escape_string($data["address"]) : "";
$phone_number = isset($data["phone_number"]) ? $conn->real_escape_string($data["phone_number"]) : "";

// 🔹 Fetch Address & Contact Number from `mobile_users` if empty
if (empty($address) || empty($phone_number)) {
    $query = "SELECT location, contact_number FROM mobile_users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $mobile_user_id);
    $stmt->execute();
    $stmt->bind_result($db_location, $db_contact_number);
    $stmt->fetch();
    $stmt->close();

    if (empty($address)) {
        $address = $db_location;
    }
    if (empty($phone_number)) {
        $phone_number = $db_contact_number;
    }
}

// SQL Query to insert into appointments table
$sql = "INSERT INTO appointments (mobile_user_id, name, address, phone_number, pet_name, pet_breed, service_type, service_name, notes, appointment_date, payment_method, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    $response["success"] = false;
    $response["message"] = "Database error: " . $conn->error;
    echo json_encode($response);
    exit();
}

// Bind parameters and execute
$stmt->bind_param("issssssssss", $mobile_user_id, $name, $address, $phone_number, $pet_name, $pet_breed, $service_type, $service_name, $notes, $appointment_date, $payment_method);

if ($stmt->execute()) {
    $response["success"] = true;
    $response["message"] = "Appointment scheduled successfully";
    $response["appointment_id"] = $stmt->insert_id;
} else {
    $response["success"] = false;
    $response["message"] = "Failed to schedule appointment: " . $stmt->error;
}

$stmt->close();
$conn->close();

// Return JSON response
echo json_encode($response);
?>
