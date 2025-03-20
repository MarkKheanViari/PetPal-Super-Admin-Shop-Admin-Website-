<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

include "db.php";

$paymongo_secret_key = "sk_test_B8LiLXJfYa8wRc3mtX8MyPcP";
$encoded_key = base64_encode($paymongo_secret_key);

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!isset($data['mobile_user_id'], $data['total_price'], $data['cart_items'], $data['payment_method'])) {
    echo json_encode(["success" => false, "message" => "❌ Missing required fields!"]);
    exit();
}

$mobile_user_id = $data['mobile_user_id'];
$total_price = number_format($data['total_price'], 2, '.', '');
$payment_method = $data['payment_method'];
$status = "Pending"; // Match the database enum value

// Start a transaction for rollback support
$conn->begin_transaction();

try {
    // Step 1: Check Stock Availability
    foreach ($data['cart_items'] as $item) {
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
    foreach ($data['cart_items'] as $item) {
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

    // Step 4: Prepare PayMongo Checkout Session
    $cart_items = [];
    foreach ($data['cart_items'] as $item) {
        $cart_items[] = [
            "name" => $item['name'],
            "quantity" => $item['quantity'],
            "amount" => intval($item['price'] * 100),
            "currency" => "PHP",
            "description" => $item['description']
        ];
    }

    $checkout_data = [
        "data" => [
            "attributes" => [
                "line_items" => $cart_items,
                "payment_method_types" => ["gcash"],
                "description" => "Order from PetPal #$order_id",
                "success_url" => "http://192.168.1.65/backend/paymongo_success.php?order_id=$order_id",
                "cancel_url" => "http://192.168.1.65/backend/paymongo_cancel.php?order_id=$order_id"
            ]
        ]
    ];
    error_log("🔍 paymongo_checkout.php: Creating PayMongo session with order_id=$order_id");

    // Step 5: Make PayMongo API Request
    $ch = curl_init("https://api.paymongo.com/v1/checkout_sessions");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Accept: application/json",
        "Authorization: Basic " . $encoded_key
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($checkout_data));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    error_log("🔍 PayMongo Response: " . $response);
    error_log("🔍 HTTP Code: " . $http_code);

    if ($http_code == 200 || $http_code == 201) {
        $response_data = json_decode($response, true);
        if (isset($response_data["data"]["attributes"]["checkout_url"])) {
            $checkout_url = $response_data["data"]["attributes"]["checkout_url"];
            // Commit the transaction
            $conn->commit();
            echo json_encode([
                "success" => true,
                "checkout_url" => $checkout_url,
                "order_id" => $order_id
            ]);
        } else {
            throw new Exception("❌ PayMongo response missing checkout_url");
        }
    } else {
        throw new Exception("❌ Failed to create PayMongo session, HTTP Code: $http_code");
    }
} catch (Exception $e) {
    // Rollback on failure
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();
?>