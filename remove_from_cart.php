<?php
include 'db.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);
$cart_id = isset($data['cart_id']) ? intval($data['cart_id']) : 0;

if ($cart_id == 0) {
    echo json_encode(["success" => false, "message" => "Invalid cart ID"]);
    exit();
}

// ✅ Get product ID and quantity from cart
$getCartQuery = $conn->prepare("SELECT product_id, quantity FROM cart WHERE id = ?");
$getCartQuery->bind_param("i", $cart_id);
$getCartQuery->execute();
$cartResult = $getCartQuery->get_result();

if ($cartResult->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Cart item not found"]);
    exit();
}

$cartData = $cartResult->fetch_assoc();
$product_id = $cartData['product_id'];
$quantity = $cartData['quantity'];

// ✅ Delete from cart
$deleteCartQuery = $conn->prepare("DELETE FROM cart WHERE id = ?");
$deleteCartQuery->bind_param("i", $cart_id);
$deleteCartQuery->execute();

// ✅ Restore stock in products table
$updateStockQuery = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
$updateStockQuery->bind_param("ii", $quantity, $product_id);
$updateStockQuery->execute();

echo json_encode(["success" => true, "message" => "Removed from cart successfully"]);

$conn->close();
?>
