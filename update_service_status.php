<?php
include 'db.php'; 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['shop_owner_id'], $data['service_id'], $data['status'])) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit();
}

$shop_owner_id = intval($data['shop_owner_id']);
$service_id = intval($data['service_id']);
$status = $data['status'];

$sql = "UPDATE services SET status = ? WHERE id = ? AND shop_owner_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $status, $service_id, $shop_owner_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Service status updated"]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to update service status"]);
}

$stmt->close();
$conn->close();
?>
