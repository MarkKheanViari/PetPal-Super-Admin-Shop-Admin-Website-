<?php
// ✅ Enable full error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'db.php'; // Ensure database connection

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// ✅ Capture PHP errors and log them
function handleError($errno, $errstr, $errfile, $errline) {
    file_put_contents("debug_log.txt", "[ERROR] [$errno] $errstr in $errfile on line $errline" . PHP_EOL, FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Internal Server Error: $errstr"]);
    exit;
}
set_error_handler("handleError");

// ✅ Read and log raw JSON input
$input = file_get_contents("php://input");
file_put_contents("debug_log.txt", "[" . date("Y-m-d H:i:s") . "] RAW INPUT: " . $input . PHP_EOL, FILE_APPEND);

// ✅ Decode JSON input
$data = json_decode(trim($input), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    file_put_contents("debug_log.txt", "❌ JSON ERROR: " . json_last_error_msg() . PHP_EOL, FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Invalid JSON format: " . json_last_error_msg()]);
    exit;
}

// ✅ Validate request parameters
if (!isset($data['customer_id']) || !isset($data['product_id']) || !isset($data['quantity'])) {
    file_put_contents("debug_log.txt", "❌ MISSING PARAMETERS" . PHP_EOL, FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Missing parameters"]);
    exit;
}

$customer_id = intval($data['customer_id']);
$product_id = intval($data['product_id']);
$quantity = intval($data['quantity']);

// ✅ Check if the product exists and get available stock
$stockQuery = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
$stockQuery->bind_param("i", $product_id);
if (!$stockQuery->execute()) {
    file_put_contents("debug_log.txt", "❌ STOCK QUERY ERROR: " . $conn->error . PHP_EOL, FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Database error while fetching stock"]);
    exit;
}

$stockResult = $stockQuery->get_result();
if ($stockResult->num_rows == 0) {
    file_put_contents("debug_log.txt", "❌ PRODUCT NOT FOUND" . PHP_EOL, FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Product not found"]);
    exit;
}

$row = $stockResult->fetch_assoc();
$availableStock = $row['quantity'];

// ✅ Check if stock is sufficient
if ($availableStock < $quantity) {
    file_put_contents("debug_log.txt", "❌ INSUFFICIENT STOCK: Requested $quantity, Available $availableStock" . PHP_EOL, FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Not enough stock available"]);
    exit;
}

// ✅ Check if product is already in the cart
$checkCart = $conn->prepare("SELECT id, quantity FROM cart WHERE customer_id = ? AND product_id = ?");
$checkCart->bind_param("ii", $customer_id, $product_id);
if (!$checkCart->execute()) {
    file_put_contents("debug_log.txt", "❌ CART CHECK ERROR: " . $conn->error . PHP_EOL, FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Database error while checking cart"]);
    exit;
}

$result = $checkCart->get_result();

if ($result->num_rows > 0) {
    // ✅ Update existing cart item
    $row = $result->fetch_assoc();
    $new_quantity = $row['quantity'] + $quantity;

    if ($new_quantity > $availableStock) {
        echo json_encode(["success" => false, "message" => "Exceeds available stock"]);
        exit;
    }

    $updateCart = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $updateCart->bind_param("ii", $new_quantity, $row['id']);
    if (!$updateCart->execute()) {
        file_put_contents("debug_log.txt", "❌ UPDATE CART ERROR: " . $conn->error . PHP_EOL, FILE_APPEND);
        echo json_encode(["success" => false, "message" => "Failed to update cart"]);
        exit;
    }
    
    file_put_contents("debug_log.txt", "✅ CART UPDATED: Customer $customer_id, Product $product_id, Quantity $new_quantity" . PHP_EOL, FILE_APPEND);
    echo json_encode(["success" => true, "message" => "Cart updated"]);
} else {
    // ✅ Insert new cart item
    $insertCart = $conn->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)");
    $insertCart->bind_param("iii", $customer_id, $product_id, $quantity);
    if (!$insertCart->execute()) {
        file_put_contents("debug_log.txt", "❌ INSERT CART ERROR: " . $conn->error . PHP_EOL, FILE_APPEND);
        echo json_encode(["success" => false, "message" => "Failed to add to cart"]);
        exit;
    }
    
    file_put_contents("debug_log.txt", "✅ CART ADDED: Customer $customer_id, Product $product_id, Quantity $quantity" . PHP_EOL, FILE_APPEND);
    echo json_encode(["success" => true, "message" => "Added to cart"]);
}

$conn->close();
?>
