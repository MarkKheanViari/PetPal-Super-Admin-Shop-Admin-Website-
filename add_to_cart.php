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
    echo json_encode(["success" => false, "message" => "❌ Invalid user or product ID"]);
    exit();
}

// ✅ Check if product exists and has stock
$productQuery = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
$productQuery->bind_param("i", $product_id);
$productQuery->execute();
$productResult = $productQuery->get_result();

if ($productResult->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "❌ Product not found"]);
    exit();
}

$productData = $productResult->fetch_assoc();
$currentStock = intval($productData['quantity']);

// ✅ Prevent adding to cart if stock is 0
if ($currentStock == 0) {
    echo json_encode(["success" => false, "message" => "❌ This product is out of stock"]);
    exit();
}

// ✅ Ensure requested quantity does not exceed stock
if ($quantity > $currentStock) {
    $quantity = $currentStock;
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

    if ($newQuantity > $currentStock) {
        $newQuantity = $currentStock;
    }

    $updateCartQuery = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $updateCartQuery->bind_param("ii", $newQuantity, $cartData['id']);
    $updateCartQuery->execute();
} else {
    // ✅ Insert new cart entry
    $insertCartQuery = $conn->prepare("INSERT INTO cart (mobile_user_id, product_id, quantity) VALUES (?, ?, ?)");
    $insertCartQuery->bind_param("iii", $mobile_user_id, $product_id, $quantity);
    $insertCartQuery->execute();
}

// ✅ No stock update necessary as stock is not being modified

echo json_encode(["success" => true, "message" => "✅ Added to cart successfully"]);

$conn->close();
?>
