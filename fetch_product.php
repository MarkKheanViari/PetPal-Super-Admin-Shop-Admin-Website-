<?php
include 'db.php'; // Ensure database connection

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// ✅ Get shop_owner_id and category from request
$shop_owner_id = isset($_GET['shop_owner_id']) ? intval($_GET['shop_owner_id']) : 0;
$category = isset($_GET['category']) ? trim($_GET['category']) : 'all'; // Default to 'all'

// ✅ Allowed Categories List
$allowed_categories = ["Food", "Treats", "Essentials", "Supplies", "Accessories", "Grooming", "Hygiene", "Toys", "Enrichment", "Healthcare", "Training"];

// ✅ Check if category is valid (or 'all' for all categories)
if ($category !== "all" && !in_array($category, $allowed_categories)) {
    echo json_encode(["success" => false, "message" => "Invalid category"]);
    exit();
}

// ✅ Base SQL query
$sql = "SELECT id, name, price, description, quantity, category, image FROM products";
$conditions = [];
$params = [];
$types = "";

// 🛠 Add condition for shop_owner_id (if provided)
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

// ✅ Bind parameters if needed
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    // ✅ Ensure correct image path
    $image_path = !empty($row['image']) ? "http://192.168.1.13/backend/uploads/" . rawurlencode($row['image']) : "http://192.168.1.13/backend/uploads/default.jpg";
    $row['image'] = $image_path;

    // ✅ Format price & ensure non-negative stock
    $row['price'] = number_format($row['price'], 2, '.', '');
    $row['quantity'] = max(0, (int)$row['quantity']);

    $products[] = $row;
}

// ✅ Return JSON response
echo json_encode(["success" => true, "products" => $products]);

$stmt->close();
$conn->close();
?>
