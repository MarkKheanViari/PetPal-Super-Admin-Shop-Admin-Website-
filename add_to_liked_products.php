<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");

include "db.php";

$response = ["success" => false, "message" => "Unknown error"];

$rawData = file_get_contents("php://input");
error_log("📦 Raw Input: " . $rawData);
$data = json_decode($rawData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["success" => false, "message" => "❌ JSON Decode Error: " . json_last_error_msg()]);
    exit();
}

// Step 1: Validate Required Fields
if (!isset($data['mobile_user_id'], $data['product_id'])) {
    echo json_encode(["success" => false, "message" => "❌ Missing required fields!"]);
    exit();
}

$mobile_user_id = $data['mobile_user_id'];
$product_id = $data['product_id'];

// Step 2: Check if the User and Product Exist
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

// Step 3: Check if the Product is Already Liked
$likeCheckQuery = "SELECT id FROM liked_products WHERE mobile_user_id = ? AND product_id = ?";
$likeStmt = $conn->prepare($likeCheckQuery);
$likeStmt->bind_param("ii", $mobile_user_id, $product_id);
$likeStmt->execute();
$likeResult = $likeStmt->get_result();
$likeStmt->close();

if ($likeResult->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "❌ Product already liked!"]);
    exit();
}

// Step 4: Add the Product to Liked Products
$insertQuery = "INSERT INTO liked_products (mobile_user_id, product_id) VALUES (?, ?)";
$insertStmt = $conn->prepare($insertQuery);

if (!$insertStmt) {
    echo json_encode(["success" => false, "message" => "❌ Prepare Error: " . $conn->error]);
    exit();
}

$insertStmt->bind_param("ii", $mobile_user_id, $product_id);

if (!$insertStmt->execute()) {
    echo json_encode(["success" => false, "message" => "❌ Insert Error: " . $insertStmt->error]);
    exit();
}

$insertStmt->close();
$conn->close();

echo json_encode(["success" => true, "message" => "✅ Product added to liked products!"]);
?>