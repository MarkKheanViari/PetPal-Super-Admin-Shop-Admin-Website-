<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include "db.php"; // Assumes this file sets up the $conn database connection

// Check if order_id is provided
if (!isset($_GET["order_id"])) {
    echo json_encode(["success" => false, "message" => "Missing order_id"]);
    exit();
}

$order_id = intval($_GET["order_id"]);

// Verify the order exists and is in 'Pending' status
$query = "SELECT status FROM orders WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Order not found"]);
    exit();
}

$row = $result->fetch_assoc();
if ($row['status'] != 'Pending') {
    echo json_encode(["success" => false, "message" => "Only pending orders can be canceled"]);
    exit();
}

// Delete associated order_items
$delete_items_query = "DELETE FROM order_items WHERE order_id = ?";
$stmt_items = $conn->prepare($delete_items_query);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();

// Delete the order
$delete_order_query = "DELETE FROM orders WHERE id = ?";
$stmt_order = $conn->prepare($delete_order_query);
$stmt_order->bind_param("i", $order_id);

if ($stmt_order->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to delete order"]);
}

$conn->close();
?>