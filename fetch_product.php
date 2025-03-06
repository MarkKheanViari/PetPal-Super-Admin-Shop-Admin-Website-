<?php
include 'db.php'; // Ensure database connection

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// ✅ Get shop_owner_id and category from request
$shop_owner_id = isset($_GET['shop_owner_id']) ? intval($_GET['shop_owner_id']) : 0;
$category = isset($_GET['category']) ? $_GET['category'] : 'all'; // Default to 'all'

// ✅ Check if 'quantity' column exists; fallback to 'stock'
$checkColumn = $conn->query("SHOW COLUMNS FROM products LIKE 'quantity'");
$stock_column = ($checkColumn->num_rows > 0) ? 'quantity' : 'stock';

// ✅ Base SQL query
$sql = "SELECT id, name, price, description, $stock_column AS quantity, image FROM products";
$conditions = [];
$params = [];
$types = "";

// 🛠 Add condition for shop_owner_id
if ($shop_owner_id > 0) {
    $conditions[] = "shop_owner_id = ?";
    $params[] = $shop_owner_id;
    $types .= "i";
}

// 🛠 Add condition for category filtering
if ($category !== 'all') {
    $conditions[] = "category = ?";
    $params[] = $category;
    $types .= "s";
}

// ✅ Build final SQL query with conditions
if (count($conditions) > 0) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Query preparation failed: " . $conn->error]);
    exit();
}

// ✅ Bind parameters if there are any
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    // ✅ Ensure correct image path with encoding
    if (!empty($row['image'])) {
        $image_path = "http://192.168.168.55/backend/uploads/" . rawurlencode($row['image']);
    } else {
        $image_path = "http://192.168.168.55/backend/uploads/default.jpg";
    }
    
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
