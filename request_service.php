<?php
include 'db.php'; 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['customer_id'], $data['service_id'], $data['selected_date'])) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit();
}

$customer_id = intval($data['customer_id']);
$service_id = intval($data['service_id']);
$selected_date = $data['selected_date'];
$status = "Pending";

$sql = "INSERT INTO service_requests (customer_id, service_id, selected_date, status) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $customer_id, $service_id, $selected_date, $status);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Service request submitted"]);
} else {
    echo json_encode(["success" => false, "error" => "Database error"]);
}

$stmt->close();
$conn->close();
?>
