<?php
include 'db.php'; // Ensure database connection

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// ✅ Get shop_owner_id securely (Only for shop owners viewing their own products)
$shop_owner_id = isset($_GET['shop_owner_id']) ? intval($_GET['shop_owner_id']) : 0;

// ✅ Check if 'quantity' column exists; fallback to 'stock'
$checkColumn = $conn->query("SHOW COLUMNS FROM products LIKE 'quantity'");
$stock_column = ($checkColumn->num_rows > 0) ? 'quantity' : 'stock';

// ✅ Query: Filter by shop_owner_id if provided, else return all products
if ($shop_owner_id > 0) {
    // 🛠️ Shop owner only sees their own products
    $sql = "SELECT id, name, price, description, $stock_column AS quantity, image 
            FROM products WHERE shop_owner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $shop_owner_id);
} else {
    // ✅ Mobile users see all products (no filtering)
    $sql = "SELECT id, name, price, description, $stock_column AS quantity, image FROM products";
    $stmt = $conn->prepare($sql);
}

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Query preparation failed: " . $conn->error]);
    exit();
}

$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    // ✅ Ensure full image URL is returned
    $image_path = !empty($row['image']) ? "http://192.168.1.65/backend/uploads/" . $row['image'] : "http://192.168.1.65/backend/uploads/default.jpg";
    $row['image'] = $image_path;

    // ✅ Format price & prevent negative stock values
    $row['price'] = number_format($row['price'], 2, '.', '');
    $row['quantity'] = max(0, (int)$row['quantity']);

    $products[] = $row;
}

// ✅ Return JSON response
echo json_encode(["success" => true, "products" => $products]);

$stmt->close();
$conn->close();
?>
