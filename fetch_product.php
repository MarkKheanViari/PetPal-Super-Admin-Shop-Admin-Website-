<?php
include 'db.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$sql = "SELECT id, name, price, description, quantity, image FROM products";
$result = $conn->query($sql);

$products = [];
while ($row = $result->fetch_assoc()) {
    // ✅ Ensure image URL is correct
    $row['image'] = !empty($row['image']) ? "http://192.168.161.55/backend/uploads/" . $row['image'] : "http://192.168.161.55/backend/uploads/default.png";
    $products[] = $row;
}

echo json_encode($products);
?>

