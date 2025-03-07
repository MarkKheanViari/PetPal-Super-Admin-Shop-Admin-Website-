<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$response = ["success" => false];

// Fetch all appointments
$sql = "SELECT a.id, a.name, a.pet_name, a.pet_breed, a.service_type, 
               a.service_name, a.appointment_date, s.price, a.status 
        FROM appointments a 
        JOIN services s ON a.service_name = s.service_name";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    $response["success"] = true;
    $response["appointments"] = $appointments;
} else {
    $response["message"] = "No appointments found";
}

echo json_encode($response);
?>
