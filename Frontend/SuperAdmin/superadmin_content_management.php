<?php
header("Content-Type: application/json");

include_once $_SERVER['DOCUMENT_ROOT'] . "/backend/db.php";

// ✅ Enable error reporting for debugging
error_reporting(E_ALL);
ini_set("display_errors", 1);

// ✅ Check Database Connection
if (!$conn) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// ✅ Fetch Products
$query = "SELECT p.id, p.name, p.price, p.shop_owner_id, p.description, p.quantity, p.category, p.image_url FROM products p";
$result = mysqli_query($conn, $query);

// ✅ Check for query errors
if (!$result) {
    echo json_encode(["error" => "SQL Query Failed: " . mysqli_error($conn)]);
    exit;
}

$products = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Fetch shop owner's name
    $shop_owner_id = $row["shop_owner_id"];
    $shop_query = "SELECT username FROM users WHERE id = '$shop_owner_id'";
    $shop_result = mysqli_query($conn, $shop_query);
    $shop_data = mysqli_fetch_assoc($shop_result);
    
    $row["shop"] = $shop_data ? $shop_data["username"] : "Unknown"; // Assign shop owner name
    
    $products[] = [
        "id" => $row["id"],
        "name" => $row["name"],
        "price" => number_format($row["price"], 2),
        "shop" => $row["shop"],
        "description" => $row["description"],
        "quantity" => $row["quantity"],
        "category" => $row["category"],
        "image_url" => $row["image_url"]
    ];
}

// ✅ Check if products array is empty
if (empty($products)) {
    echo json_encode(["error" => "No products found"]);
    exit;
}

// ✅ Send JSON response
echo json_encode(["products" => $products]);
mysqli_close($conn);
?>
