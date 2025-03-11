<?php
include 'db.php'; // Database connection

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// ✅ Read JSON Input
$data = json_decode(file_get_contents("php://input"), true);
$cart_id = isset($data['cart_id']) ? intval($data['cart_id']) : 0;
$new_quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;

// Check if quantity is valid
if ($cart_id == 0 || $new_quantity <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid cart item or quantity"]);
    exit();
}

// ✅ Get product stock before updating
$productCheck = $conn->prepare("SELECT product_id FROM cart WHERE id = ?");
$productCheck->bind_param("i", $cart_id);
$productCheck->execute();
$productResult = $productCheck->get_result();
if ($productResult->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Cart item not found"]);
    exit();
}

$productRow = $productResult->fetch_assoc();
$product_id = $productRow["product_id"];

// ✅ Check if stock is available
$stockCheck = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
$stockCheck->bind_param("i", $product_id);
$stockCheck->execute();
$stockResult = $stockCheck->get_result();
$stockRow = $stockResult->fetch_assoc();
$available_stock = $stockRow["quantity"];

// ✅ If requested quantity exceeds stock, reject the request
if ($new_quantity > $available_stock) {
    echo json_encode(["success" => false, "message" => "Not enough stock available, only " . $available_stock . " units in stock"]);
    exit();
}

// ✅ Update quantity in cart
$update = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
$update->bind_param("ii", $new_quantity, $cart_id);
$success = $update->execute();

if ($success) {
    echo json_encode(["success" => true, "message" => "Cart updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update cart"]);
}

$update->close();
$conn->close();
?>
