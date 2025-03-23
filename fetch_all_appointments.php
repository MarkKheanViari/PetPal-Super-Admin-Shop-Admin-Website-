<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$response = ["success" => false];

// Include columns from `mobile_appointments` (alias `m`)
$sql = "
  SELECT 
    a.id,
    a.name,
    a.pet_name,
    a.pet_breed,
    a.service_type,
    a.service_name,
    a.appointment_date,
    a.appointment_time, -- Add this line if the field exists
    s.price,
    a.status,
    m.address,
    m.phone_number,
    m.notes,
    m.payment_method
  FROM appointments AS a
  JOIN services AS s 
    ON a.service_name = s.service_name
  LEFT JOIN mobile_appointments AS m
    ON a.id = m.id
  ORDER BY a.appointment_date DESC
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
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
$conn->close();
?>
