<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

error_reporting(E_ALL);
ini_set('display_errors', 1);

include $_SERVER['DOCUMENT_ROOT'] . "/backend/db.php";

$response = array();
$response["users"] = array();

// Query for Mobile Users (Customers)
$mobileUsersQuery = "SELECT id, username AS name, email, 'Customer' AS type FROM mobile_users";
$mobileUsersResult = $conn->query($mobileUsersQuery);
while ($row = $mobileUsersResult->fetch_assoc()) {
    $response["users"][] = $row;
}

// Query for Shop Owners
$shopOwnersQuery = "SELECT id, username AS name, email, 'Shop Owner' AS type FROM shop_owners";
$shopOwnersResult = $conn->query($shopOwnersQuery);
while ($row = $shopOwnersResult->fetch_assoc()) {
    $response["users"][] = $row;
}

// Return JSON response
echo json_encode($response);
$conn->close();
?>
