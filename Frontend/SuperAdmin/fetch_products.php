<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once $_SERVER['DOCUMENT_ROOT'] . "/backend/db.php";

$response = array();

$page = isset($_GET['page']) ? $_GET['page'] : '';

if ($page === 'dashboard') {
    // Fetch limited details for Dashboard
    $query = "SELECT p.id, p.name, s.username AS owner FROM products p
              LEFT JOIN mobile_users s ON p.shop_owner_id = s.id 
              ORDER BY p.id DESC";
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
} else {
    // Fetch all product details including category, quantity, and report count for Content Management
    $query = "
        SELECT 
            p.id, 
            p.name, 
            p.price, 
            p.shop_owner_id, 
            p.description, 
            p.quantity, 
            p.category, 
            p.image_url, 
            p.image,
            s.username AS shop,
            COUNT(r.id) AS report_count
        FROM products p
        LEFT JOIN mobile_users s ON p.shop_owner_id = s.id
        LEFT JOIN reports r ON p.id = r.product_id
        GROUP BY p.id
        ORDER BY p.id DESC
    ";
    $result = $conn->query($query);

    if ($result) {
        $products = array();
        while ($row = $result->fetch_assoc()) {
            $products[] = [
                "id" => $row["id"],
                "name" => $row["name"],
                "price" => number_format($row["price"], 2),
                "shop" => $row["shop"] ?: "Unknown",
                "description" => $row["description"],
                "quantity" => $row["quantity"],
                "category" => $row["category"],
                "image_url" => $row["image_url"],
                "image" => $row["image"],
                "report_count" => (int)$row["report_count"]
            ];
        }
        $response["products"] = $products;
    } else {
        $response["error"] = "Query Failed: " . $conn->error;
    }
}

echo json_encode($response);
$conn->close();
?>