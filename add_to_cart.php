<?php
include 'db.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// ✅ Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
$mobile_user_id = isset($data['mobile_user_id']) ? intval($data['mobile_user_id']) : 0;
$product_id = isset($data['product_id']) ? intval($data['product_id']) : 0;
$quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;

if ($mobile_user_id == 0 || $product_id == 0) {
    echo json_encode(["success" => false, "message" => "Invalid user or product ID"]);
    exit();
}

// ✅ Check if product exists and has enough stock
$productQuery = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
$productQuery->bind_param("i", $product_id);
$productQuery->execute();
$productResult = $productQuery->get_result();

if ($productResult->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Product not found"]);
    exit();
}

$productData = $productResult->fetch_assoc();
$currentStock = intval($productData['quantity']);

if ($currentStock < $quantity) {
    echo json_encode(["success" => false, "message" => "Not enough stock available"]);
    exit();
}

// ✅ Check if item is already in the cart
$checkCartQuery = $conn->prepare("SELECT id, quantity FROM cart WHERE mobile_user_id = ? AND product_id = ?");
$checkCartQuery->bind_param("ii", $mobile_user_id, $product_id);
$checkCartQuery->execute();
$cartResult = $checkCartQuery->get_result();

if ($cartResult->num_rows > 0) {
    // ✅ Update quantity if already in cart
    $cartData = $cartResult->fetch_assoc();
    $newQuantity = $cartData['quantity'] + $quantity;

    $updateCartQuery = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $updateCartQuery->bind_param("ii", $newQuantity, $cartData['id']);
    $updateCartQuery->execute();
} else {
    // ✅ Insert new cart entry
    $insertCartQuery = $conn->prepare("INSERT INTO cart (mobile_user_id, product_id, quantity) VALUES (?, ?, ?)");
    $insertCartQuery->bind_param("iii", $mobile_user_id, $product_id, $quantity);
    $insertCartQuery->execute();
}

// ✅ Update stock in products table
$updateStockQuery = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
$updateStockQuery->bind_param("ii", $quantity, $product_id);
$updateStockQuery->execute();

echo json_encode(["success" => true, "message" => "Added to cart successfully"]);

$conn->close();
?>
