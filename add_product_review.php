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
if (!isset($data['mobile_user_id'], $data['product_id'], $data['rating'])) {
    echo json_encode(["success" => false, "message" => "❌ Missing required fields!"]);
    exit();
}

$mobile_user_id = $data['mobile_user_id'];
$product_id = $data['product_id'];
$rating = $data['rating'];

// Step 2: Validate Rating (must be between 1 and 5)
if ($rating < 1 || $rating > 5) {
    echo json_encode(["success" => false, "message" => "❌ Rating must be between 1 and 5!"]);
    exit();
}

// Step 3: Check if the User and Product Exist
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

// Step 4: Check if the User Has Already Reviewed This Product
$reviewCheckQuery = "SELECT id FROM product_reviews WHERE mobile_user_id = ? AND product_id = ?";
$reviewStmt = $conn->prepare($reviewCheckQuery);
$reviewStmt->bind_param("ii", $mobile_user_id, $product_id);
$reviewStmt->execute();
$reviewResult = $reviewStmt->get_result();

if ($reviewResult->num_rows > 0) {
    // User has already rated this product, update the existing rating
    $updateQuery = "UPDATE product_reviews SET rating = ?, created_at = CURRENT_TIMESTAMP WHERE mobile_user_id = ? AND product_id = ?";
    $updateStmt = $conn->prepare($updateQuery);

    if (!$updateStmt) {
        echo json_encode(["success" => false, "message" => "❌ Rating Update Prepare Error: " . $conn->error]);
        exit();
    }

    $updateStmt->bind_param("iii", $rating, $mobile_user_id, $product_id);

    if (!$updateStmt->execute()) {
        echo json_encode(["success" => false, "message" => "❌ Rating Update Error: " . $updateStmt->error]);
        exit();
    }

    $updateStmt->close();
    echo json_encode(["success" => true, "message" => "✅ Rating updated successfully!"]);
} else {
    // User has not rated this product, insert a new rating
    $insertQuery = "INSERT INTO product_reviews (mobile_user_id, product_id, rating) VALUES (?, ?, ?)";
    $insertStmt = $conn->prepare($insertQuery);

    if (!$insertStmt) {
        echo json_encode(["success" => false, "message" => "❌ Rating Insert Prepare Error: " . $conn->error]);
        exit();
    }

    $insertStmt->bind_param("iii", $mobile_user_id, $product_id, $rating);

    if (!$insertStmt->execute()) {
        echo json_encode(["success" => false, "message" => "❌ Rating Insert Error: " . $insertStmt->error]);
        exit();
    }

    $insertStmt->close();
    echo json_encode(["success" => true, "message" => "✅ Rating submitted successfully!"]);
}

$reviewStmt->close();
$conn->close();
?>