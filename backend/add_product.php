<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Get shop owner ID from the request
$shop_owner_id = $_POST['shop_owner_id'];
$product_name = $_POST['product_name'];
$product_price = $_POST['product_price'];

if (!$shop_owner_id || !$product_name || !$product_price) {
    die(json_encode(["error" => "Missing fields"]));
}

$stmt = $conn->prepare("INSERT INTO products (name, price, shop_owner_id) VALUES (?, ?, ?)");
$stmt->bind_param("sdi", $product_name, $product_price, $shop_owner_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to add product"]);
}
?>

