<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include "db.php"; // Ensure database connection

$response = array();

// ✅ Fetch and format the date properly before sending to the frontend
$query = "SELECT id, name, address, phone_number, pet_name, pet_breed, checkup_type, notes, 
                 DATE_FORMAT(appointment_date, '%m/%d/%Y') AS appointment_date, payment_method 
          FROM veterinary_appointments";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    $appointments = array();
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    $response["success"] = true;
    $response["appointments"] = $appointments;
} else {
    $response["success"] = false;
    $response["message"] = "No veterinary appointments found.";
}

echo json_encode($response);
$conn->close();
?>
