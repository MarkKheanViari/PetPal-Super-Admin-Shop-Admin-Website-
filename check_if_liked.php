<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");

include "db.php";

$response = ["success" => false, "message" => "Unknown error"];

if (!isset($_GET['mobile_user_id']) || !isset($_GET['product_id'])) {
    echo json_encode(["success" => false, "message" => "❌ Mobile User ID and Product ID are required!"]);
    exit();
}

$mobile_user_id = $_GET['mobile_user_id'];
$product_id = $_GET['product_id'];

// Step 1: Check if the User and Product Exist
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

// Step 2: Check if the Product is Liked
$likeQuery = "SELECT id FROM liked_products WHERE mobile_user_id = ? AND product_id = ?";
$likeStmt = $conn->prepare($likeQuery);
$likeStmt->bind_param("ii", $mobile_user_id, $product_id);
$likeStmt->execute();
$likeResult = $likeStmt->get_result();

$isLiked = $likeResult->num_rows > 0;

$likeStmt->close();
$conn->close();

echo json_encode([
    "success" => true,
    "is_liked" => $isLiked
]);
?>