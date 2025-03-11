<?php
// 🔹 Enable error reporting to catch PHP issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

include "db.php"; // Ensure database connection

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["order_id"], $data["status"])) {
    echo json_encode(["success" => false, "message" => "Missing order_id or status"]);
    exit();
}

$order_id = intval($data["order_id"]);
$status = $data["status"];

// Ensure status is one of the allowed values
$allowed_statuses = ["Pending", "To Ship", "Shipped", "Delivered"];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(["success" => false, "message" => "Invalid status value"]);
    exit();
}

// Update order status in database
$query = "UPDATE orders SET status = ? WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("si", $status, $order_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Order status updated successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update order status: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
