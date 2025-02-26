<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once $_SERVER['DOCUMENT_ROOT'] . "/backend/db.php";

$response = array();

$page = isset($_GET['page']) ? $_GET['page'] : '';

if ($page === 'dashboard') {
    // Fetch limited details for Dashboard
    $query = "SELECT p.id, p.name, s.username AS owner FROM products p
              LEFT JOIN shop_owners s ON p.shop_owner_id = s.id 
              ORDER BY p.id DESC";
} else {
    // Fetch all product details including category and quantity for Content Management
    $query = "SELECT p.id, p.name, p.price, p.quantity, p.category, p.image_url 
              FROM products p
              ORDER BY p.id DESC";
}

$result = $conn->query($query);

if ($result) {
    $products = array();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $response["products"] = $products;
} else {
    $response["error"] = "Query Failed: " . $conn->error;
}

echo json_encode($response);
$conn->close();
?>
