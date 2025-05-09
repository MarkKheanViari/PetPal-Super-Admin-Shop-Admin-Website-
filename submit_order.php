<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

include "db.php";

$response = ["success" => false, "message" => "Unknown error"];

$rawData = file_get_contents("php://input");
error_log("📦 Raw Input: " . $rawData);
$data = json_decode($rawData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["success" => false, "message" => "❌ JSON Decode Error: " . json_last_error_msg()]);
    exit();
}

// ✅ Step 1: Validate Required Fields
if (!isset($data['mobile_user_id'], $data['total_price'], $data['payment_method'], $data['cart_items'])) {
    echo json_encode(["success" => false, "message" => "❌ Missing required fields!"]);
    exit();
}

$mobile_user_id = $data['mobile_user_id'];
$total_price = $data['total_price'];
$payment_method = $data['payment_method'];
$status = "Pending";

error_log("🛠 Mobile User ID: $mobile_user_id, Total: $total_price, Payment: $payment_method");

// ✅ Step 2: Insert into `orders`
$query = "INSERT INTO orders (mobile_user_id, total_price, payment_method, status) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "❌ Order Insert Prepare Error: " . $conn->error]);
    exit();
}

$stmt->bind_param("idss", $mobile_user_id, $total_price, $payment_method, $status);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "❌ Order Insert Error: " . $stmt->error]);
    exit();
}

$order_id = $stmt->insert_id;
$stmt->close();

error_log("✅ Order Inserted: ID $order_id");

// ✅ Step 3: Insert Cart Items
foreach ($data['cart_items'] as $item) {
    if (!isset($item['product_id'], $item['quantity'], $item['price'])) {
        echo json_encode(["success" => false, "message" => "❌ Missing fields in cart_items"]);
        exit();
    }

    $product_id = $item['product_id'];
    $quantity = $item['quantity'];
    $price = $item['price'];

    error_log("🛒 Adding Item - Product: $product_id, Quantity: $quantity, Price: $price");

    $itemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    $itemStmt = $conn->prepare($itemQuery);

    if (!$itemStmt) {
        echo json_encode(["success" => false, "message" => "❌ Order Item Insert Prepare Error: " . $conn->error]);
        exit();
    }

    $itemStmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);

    if (!$itemStmt->execute()) {
        echo json_encode(["success" => false, "message" => "❌ Order Item Insert Error: " . $itemStmt->error]);
        exit();
    }
    $itemStmt->close();
}

// ✅ Step 4: Final Response
$conn->close();

echo json_encode(["success" => true, "message" => "✅ Order placed successfully!"]);
?>
