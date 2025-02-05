<?php
require 'db.php';

header('Content-Type: application/json');

if (!isset($_GET['user_id'])) {
    echo json_encode(["success" => false, "message" => "User ID is required"]);
    exit;
}

$user_id = $_GET['user_id'];

$query = $conn->prepare("
    SELECT cart.id, products.name, products.price, cart.quantity, products.image 
    FROM cart 
    JOIN products ON cart.product_id = products.id
    WHERE cart.user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

$cartItems = [];
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
}

echo json_encode(["success" => true, "cart" => $cartItems]);
$conn->close();
?>
