<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");

include "db.php";

$response = ["success" => false, "message" => "Unknown error"];

if (!isset($_GET['product_id']) || !isset($_GET['mobile_user_id'])) {
    echo json_encode(["success" => false, "message" => "❌ Product ID and Mobile User ID are required!"]);
    exit();
}

$product_id = $_GET['product_id'];
$mobile_user_id = $_GET['mobile_user_id'];

// Step 1: Check if the Product Exists
$productCheckQuery = "SELECT id FROM products WHERE id = ?";
$productStmt = $conn->prepare($productCheckQuery);
$productStmt->bind_param("i", $product_id);
$productStmt->execute();
$productResult = $productStmt->get_result();
$productStmt->close();

if ($productResult->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "❌ Product ID $product_id not found!"]);
    exit();
}

// Step 2: Check if the User Exists
$userCheckQuery = "SELECT id FROM mobile_users WHERE id = ?";
$userStmt = $conn->prepare($userCheckQuery);
$userStmt->bind_param("i", $mobile_user_id);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userStmt->close();

if ($userResult->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "❌ User ID $mobile_user_id not found!"]);
    exit();
}

// Step 3: Fetch the User's Rating
$ratingQuery = "SELECT rating FROM product_reviews WHERE product_id = ? AND mobile_user_id = ?";
$ratingStmt = $conn->prepare($ratingQuery);
$ratingStmt->bind_param("ii", $product_id, $mobile_user_id);
$ratingStmt->execute();
$ratingResult = $ratingStmt->get_result();
$ratingStmt->close();

if ($ratingResult->num_rows > 0) {
    $row = $ratingResult->fetch_assoc();
    $response = [
        "success" => true,
        "rating" => $row['rating']
    ];
} else {
    $response = [
        "success" => true,
        "rating" => 0 // No rating found
    ];
}

$conn->close();
echo json_encode($response);
?>