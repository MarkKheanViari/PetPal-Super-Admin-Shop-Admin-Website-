<?php
include 'db.php'; // Ensure database connection

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$shop_owner_id = isset($_GET['shop_owner_id']) ? intval($_GET['shop_owner_id']) : 0;

// ✅ Fix: Check if the column name is correct
$checkColumn = $conn->query("SHOW COLUMNS FROM products LIKE 'quantity'");
if ($checkColumn->num_rows == 0) {
    // If 'quantity' does not exist, assume the correct column is 'stock'
    $stock_column = 'stock';
} else {
    $stock_column = 'quantity';
}

if ($shop_owner_id > 0) {
    // ✅ Fetch only products belonging to this shop owner, ensuring stock is correct
    $sql = "SELECT id, name, price, description, $stock_column AS quantity, image FROM products WHERE shop_owner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $shop_owner_id);
} else {
    // ✅ Fetch all products (for mobile app)
    $sql = "SELECT id, name, price, description, $stock_column AS quantity, image FROM products";
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

// ✅ Debugging log (Check if products have stock)
file_put_contents("fetch_product_log.txt", json_encode($products, JSON_PRETTY_PRINT));

echo json_encode($products);

$stmt->close();
$conn->close();
?>
