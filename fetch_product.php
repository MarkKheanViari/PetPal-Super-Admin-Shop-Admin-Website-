<?php
include 'db.php'; // Ensure database connection

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$shop_owner_id = isset($_GET['shop_owner_id']) ? intval($_GET['shop_owner_id']) : 0;

if ($shop_owner_id > 0) {
    // Fetch only products belonging to this shop owner
    $sql = "SELECT id, name, price, description, quantity, image FROM products WHERE shop_owner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $shop_owner_id);
} else {
    // Fetch all products (for mobile app)
    $sql = "SELECT id, name, price, description, quantity, image FROM products";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    // Ensure full image URL is returned
    $row['image'] = "http://192.168.1.65/backend/uploads/" . $row['image'];
    $products[] = $row;
}

echo json_encode($products);

$stmt->close();
$conn->close();
?>
