<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");


include_once $_SERVER['DOCUMENT_ROOT'] . "/backend/db.php";





$response = array();

// Count Mobile Users (Customers)
$customersQuery = "SELECT COUNT(*) AS count FROM mobile_users";
$customersResult = $conn->query($customersQuery);
$customers = $customersResult->fetch_assoc()["count"];

// Count Website Users (Shop Owners)
$shopOwnersQuery = "SELECT COUNT(*) AS count FROM shop_owners";
$shopOwnersResult = $conn->query($shopOwnersQuery);
$shopOwners = $shopOwnersResult->fetch_assoc()["count"];

// Response
$response["customers"] = $customers;
$response["shopOwners"] = $shopOwners;

echo json_encode($response);
$conn->close();
?>
