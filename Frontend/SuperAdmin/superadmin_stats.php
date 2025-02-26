<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

error_reporting(E_ALL);
ini_set('display_errors', 1);

include $_SERVER['DOCUMENT_ROOT'] . "/backend/db.php";

$response = array();

// Count total users (Customers + Shop Owners)
$totalUsersQuery = "SELECT (SELECT COUNT(*) FROM mobile_users) + (SELECT COUNT(*) FROM shop_owners) AS totalUsers";
$totalUsersResult = $conn->query($totalUsersQuery);
if ($totalUsersResult) {
    $response["totalUsers"] = $totalUsersResult->fetch_assoc()["totalUsers"] ?? 0;
} else {
    $response["totalUsers"] = 0;
    $response["error"] = "Error in totalUsersQuery: " . $conn->error;
}

// Count active shop owners
$activeShopOwnersQuery = "SELECT COUNT(*) AS activeShopOwners FROM shop_owners";
$activeShopOwnersResult = $conn->query($activeShopOwnersQuery);
if ($activeShopOwnersResult) {
    $response["activeShopOwners"] = $activeShopOwnersResult->fetch_assoc()["activeShopOwners"] ?? 0;
} else {
    $response["activeShopOwners"] = 0;
    $response["error"] = "Error in activeShopOwnersQuery: " . $conn->error;
}

// Count active customers
$activeCustomersQuery = "SELECT COUNT(*) AS activeCustomers FROM mobile_users ";
$activeCustomersResult = $conn->query($activeCustomersQuery);
if ($activeCustomersResult) {
    $response["activeCustomers"] = $activeCustomersResult->fetch_assoc()["activeCustomers"] ?? 0;
} else {
    $response["activeCustomers"] = 0;
    $response["error"] = "Error in activeCustomersQuery: " . $conn->error;
}

// Count pending products
$pendingProductsQuery = "SELECT COUNT(*) AS pendingProducts FROM products";
$pendingProductsResult = $conn->query($pendingProductsQuery);
if ($pendingProductsResult) {
    $response["pendingProducts"] = $pendingProductsResult->fetch_assoc()["pendingProducts"] ?? 0;
} else {
    $response["pendingProducts"] = 0;
    $response["error"] = "Error in pendingProductsQuery: " . $conn->error;
}

// Return the JSON response
echo json_encode($response);
$conn->close();
?>
