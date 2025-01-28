<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable output of errors in JSON response

try {
    // Get shop owner ID from query parameter
    $shop_owner_id = isset($_GET['shop_owner_id']) ? $_GET['shop_owner_id'] : null;

    if ($shop_owner_id) {
        $stmt = $conn->prepare("SELECT name, price FROM products WHERE shop_owner_id = ?");
        $stmt->bind_param("i", $shop_owner_id);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // If no shop_owner_id provided, fetch all products (for mobile app)
        $result = $conn->query("SELECT name, price FROM products");
    }

    $products = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $products[] = [
                "name" => $row["name"],
                "price" => number_format((float)$row["price"], 2, '.', '')
            ];
        }
        echo json_encode($products);
    } else {
        throw new Exception("Failed to fetch products");
    }
} catch (Exception $e) {
    error_log("Error in fetch_product.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
}
?>