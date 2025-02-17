<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include "db.php"; // Ensure database connection

$response = array();

$query = "SELECT id, name, address, phone_number, pet_name, pet_breed, groom_type, notes, 
                 DATE_FORMAT(appointment_date, '%m/%d/%Y') AS appointment_date, payment_method 
          FROM grooming_appointments";

$result = $conn->query($query);

if (!$result) {
    $response["success"] = false;
    $response["message"] = "Database query failed: " . $conn->error;
    echo json_encode($response);
    exit();
}

if ($result->num_rows > 0) {
    $appointments = array();
    while ($row = $result->fetch_assoc()) {
        // Log actual row data
        file_put_contents("debug_log.txt", "Row Data: " . print_r($row, true), FILE_APPEND);

        // Ensure appointment_date is not empty
        if (!empty($row["appointment_date"])) {
            $row["appointment_date"] = strval($row["appointment_date"]);
        } else {
            $row["appointment_date"] = "N/A";
        }

        $appointments[] = $row;
    }
    $response["success"] = true;
    $response["appointments"] = $appointments;
} else {
    $response["success"] = false;
    $response["message"] = "No grooming appointments found.";
}

// Log the final response
file_put_contents("debug_log.txt", "Final Response: " . print_r($response, true), FILE_APPEND);

echo json_encode($response);
$conn->close();

?>
