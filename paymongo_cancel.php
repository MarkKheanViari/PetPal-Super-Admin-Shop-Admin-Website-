<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

include "db.php";

if (!isset($_GET['order_id'])) {
    error_log("❌ paymongo_cancel.php: Missing order ID");
    echo json_encode(["success" => false, "message" => "❌ Missing order ID"]);
    exit();
}

$order_id = intval($_GET['order_id']);
error_log("🔍 paymongo_cancel.php: Processing order_id=$order_id");

// Check if order exists
$checkQuery = "SELECT status FROM orders WHERE id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if ($result->num_rows > 0) {
    error_log("✅ paymongo_cancel.php: Order ID $order_id found with status " . $order['status']);
    // Redirect to a deep link
    $deep_link = "petpal://payment/cancel?order_id=$order_id";
    header("Location: $deep_link");
    exit();
} else {
    error_log("❌ paymongo_cancel.php: Order ID $order_id not found");
    echo json_encode(["success" => false, "message" => "❌ Order not found"]);
}

$conn->close();
?>