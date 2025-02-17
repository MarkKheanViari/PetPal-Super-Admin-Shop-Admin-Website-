<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

include "db.php"; // Connect to database

$data = json_decode(file_get_contents("php://input"), true);
$response = array();

if (isset($data["id"])) {
    $id = $data["id"];
    $query = "UPDATE veterinary_appointments SET status = 'Approved' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $response["success"] = true;
        $response["message"] = "Appointment approved!";
    } else {
        $response["success"] = false;
        $response["message"] = "Failed to approve appointment.";
    }

    $stmt->close();
} else {
    $response["success"] = false;
    $response["message"] = "Invalid request.";
}

$conn->close();
echo json_encode($response);
?>
