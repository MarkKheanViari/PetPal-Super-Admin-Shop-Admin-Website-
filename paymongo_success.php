<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

include "db.php"; // Include your database connection

if (!isset($_GET['order_id'])) {
    echo json_encode(["success" => false, "message" => "❌ Missing order ID"]);
    exit();
}

$order_id = intval($_GET['order_id']);

// ✅ Update order status to "Paid"
$updateOrderQuery = "UPDATE orders SET status = 'Paid' WHERE id = ?";
$stmt = $conn->prepare($updateOrderQuery);
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "✅ Payment successful! Order marked as paid."]);
} else {
    echo json_encode(["success" => false, "message" => "❌ Failed to update order status"]);
}

$stmt->close();
$conn->close();
?>
