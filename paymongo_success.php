<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: text/html");

include "db.php";

if (!isset($_GET['order_data'])) {
    error_log("❌ paymongo_success.php: Missing order data");
    echo json_encode(["success" => false, "message" => "❌ Missing order data"]);
    exit();
}

// Decode the order data
$encoded_order_data = $_GET['order_data'];
$order_data = json_decode(base64_decode($encoded_order_data), true);

if (!$order_data || !isset($order_data['mobile_user_id'], $order_data['total_price'], $order_data['payment_method'], $order_data['cart_items'])) {
    error_log("❌ paymongo_success.php: Invalid or incomplete order data");
    echo json_encode(["success" => false, "message" => "❌ Invalid order data"]);
    exit();
}

$mobile_user_id = $order_data['mobile_user_id'];
$total_price = $order_data['total_price'];
$payment_method = $order_data['payment_method'];
$status = "PAID"; // Set to PAID since payment is successful

// Start a transaction
$conn->begin_transaction();

try {
    // Step 1: Re-check Stock Availability
    foreach ($order_data['cart_items'] as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];

        $stockQuery = "SELECT quantity FROM products WHERE id = ?";
        $stockStmt = $conn->prepare($stockQuery);
        $stockStmt->bind_param("i", $product_id);
        $stockStmt->execute();
        $stockResult = $stockStmt->get_result();
        $stockStmt->close();

        if ($stockResult->num_rows === 0 || $stockResult->fetch_assoc()["quantity"] < $quantity) {
            throw new Exception("❌ Insufficient stock for Product ID: $product_id");
        }
    }

    // Step 2: Insert Order into Database
    $query = "INSERT INTO orders (mobile_user_id, total_price, payment_method, status) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("idss", $mobile_user_id, $total_price, $payment_method, $status);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Step 3: Insert Cart Items
    foreach ($order_data['cart_items'] as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        $name = $item['name'];
        $image = $item['image'];
        $description = $item['description'];

        $itemQuery = "INSERT INTO order_items (order_id, mobile_user_id, product_id, name, image, description, quantity, price) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $itemStmt = $conn->prepare($itemQuery);
        $itemStmt->bind_param("iiisssid", $order_id, $mobile_user_id, $product_id, $name, $image, $description, $quantity, $price);
        $itemStmt->execute();
        $itemStmt->close();
    }

    // Step 4: Reduce Stock
    foreach ($order_data['cart_items'] as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];

        $updateStockQuery = "UPDATE products SET quantity = quantity - ? WHERE id = ?";
        $stockStmt = $conn->prepare($updateStockQuery);
        $stockStmt->bind_param("ii", $quantity, $product_id);
        $stockStmt->execute();
        $stockStmt->close();
    }

    // Commit the transaction
    $conn->commit();
    error_log("✅ paymongo_success.php: Order ID $order_id created with status PAID and stock reduced");

    // Redirect to deep link
    $deep_link = "petpal://payment/success?order_id=$order_id";
    header("Location: $deep_link");
    exit();
} catch (Exception $e) {
    // Rollback on failure
    $conn->rollback();
    error_log("❌ paymongo_success.php: Failed to process order - " . $e->getMessage());
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();
?>