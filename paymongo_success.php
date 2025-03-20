<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: text/html");

include "db.php";

if (!isset($_GET['order_id'])) {
    error_log("❌ paymongo_success.php: Missing order ID");
    echo json_encode(["success" => false, "message" => "❌ Missing order ID"]);
    exit();
}

$order_id = intval($_GET['order_id']);
error_log("🔍 paymongo_success.php: Processing order_id=$order_id");

// Verify order exists and is in "Pending" status
$checkQuery = "SELECT status FROM orders WHERE id = ? AND status = 'Pending'";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if ($result->num_rows === 0) {
    error_log("❌ paymongo_success.php: Order ID $order_id not found or not in 'Pending' status");
    if ($order) {
        error_log("🔍 paymongo_success.php: Current status of order $order_id: " . $order['status']);
    }
    echo json_encode(["success" => false, "message" => "❌ Invalid or already processed order"]);
    exit();
}

// Update order status to "To Ship"
$updateOrderQuery = "UPDATE orders SET status = 'To Ship' WHERE id = ?";
$stmt = $conn->prepare($updateOrderQuery);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$stmt->close();

// Reduce stock
$itemsQuery = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
$stmt = $conn->prepare($itemsQuery);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result();
while ($item = $items->fetch_assoc()) {
    $updateStockQuery = "UPDATE products SET quantity = quantity - ? WHERE id = ?";
    $stockStmt = $conn->prepare($updateStockQuery);
    $stockStmt->bind_param("ii", $item['quantity'], $item['product_id']);
    $stockStmt->execute();
    $stockStmt->close();
}
$stmt->close();

error_log("✅ paymongo_success.php: Order ID $order_id updated to 'To Ship' and stock reduced");

$conn->close();

// Redirect to a deep link
$deep_link = "petpal://payment/success?order_id=$order_id";
header("Location: $deep_link");
exit();
?>