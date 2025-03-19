<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: text/html");

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
$stmt->execute();
$stmt->close();
$conn->close();

// ✅ Redirect user to an intermediate webpage that will open the deep link
$intermediate_url = "https://192.168.1.65/deeplink_redirect.php?order_id=$order_id";

header("Location: $intermediate_url");
exit();
?>
