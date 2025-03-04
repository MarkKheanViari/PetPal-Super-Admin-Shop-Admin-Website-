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

// Validate required fields
$requiredFields = ["mobile_user_id", "name", "address", "phone_number", "pet_name", "pet_breed", "service_type", "service_name", "appointment_date", "payment_method"];
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
$address = $conn->real_escape_string($data["address"]);
$phone_number = $conn->real_escape_string($data["phone_number"]);
$pet_name = $conn->real_escape_string($data["pet_name"]);
$pet_breed = $conn->real_escape_string($data["pet_breed"]);
$service_type = $conn->real_escape_string($data["service_type"]);
$service_name = $conn->real_escape_string($data["service_name"]);
$notes = isset($data["notes"]) ? $conn->real_escape_string($data["notes"]) : "";
$appointment_date = $conn->real_escape_string($data["appointment_date"]);
$payment_method = $conn->real_escape_string($data["payment_method"]);

// SQL Query to insert into appointments table
$sql = "INSERT INTO appointments (mobile_user_id, name, address, phone_number, pet_name, pet_breed, service_type, service_name, notes, appointment_date, payment_method)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

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
