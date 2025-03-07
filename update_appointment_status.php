<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$response = ["success" => false];

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["appointment_id"]) || !isset($data["status"])) {
    $response["message"] = "Missing required fields";
    echo json_encode($response);
    exit();
}

$appointment_id = intval($data["appointment_id"]);
$status = $data["status"];

// Update the status in the database
$sql = "UPDATE appointments SET status = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $status, $appointment_id);

if ($stmt->execute()) {
    $response["success"] = true;
} else {
    $response["message"] = "Failed to update status";
}

echo json_encode($response);
?>
