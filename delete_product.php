<?php
include 'db.php'; // Ensure database connection

error_reporting(E_ALL);
ini_set('display_errors', 1);


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

file_put_contents("log.txt", "Delete request received: " . json_encode($_POST) . "\n", FILE_APPEND);

$data = json_decode(file_get_contents("php://input"), true); 

if (!isset($data['product_id']) || !isset($data['shop_owner_id'])) {
    echo json_encode(["success" => false, "error" => "Missing parameters"]);
    exit();
}

$product_id = $data['product_id'];
$shop_owner_id = $data['shop_owner_id'];

// Delete product from database
$sql = "DELETE FROM products WHERE id = ? AND shop_owner_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_id, $shop_owner_id);
$result = $stmt->execute();

if ($result) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Database error"]);
}
?>
