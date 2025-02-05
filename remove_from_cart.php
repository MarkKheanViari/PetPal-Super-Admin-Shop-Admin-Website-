<?php
require 'db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->cart_id)) {
    echo json_encode(["success" => false, "message" => "Cart ID required"]);
    exit;
}

$cart_id = $data->cart_id;

$deleteQuery = $conn->prepare("DELETE FROM cart WHERE id = ?");
$deleteQuery->bind_param("i", $cart_id);

if ($deleteQuery->execute()) {
    echo json_encode(["success" => true, "message" => "Item removed"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to remove item"]);
}

$conn->close();
?>
