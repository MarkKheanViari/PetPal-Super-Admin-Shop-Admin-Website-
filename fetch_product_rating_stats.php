<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");

include "db.php";

$response = ["success" => false, "message" => "Unknown error"];

if (!isset($_GET['product_id'])) {
    echo json_encode(["success" => false, "message" => "❌ Product ID is required!"]);
    exit();
}

$product_id = $_GET['product_id'];

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

// Step 2: Calculate Total Ratings and Average Rating
$totalRatingsQuery = "SELECT COUNT(*) as total, AVG(rating) as average FROM product_reviews WHERE product_id = ?";
$totalStmt = $conn->prepare($totalRatingsQuery);
$totalStmt->bind_param("i", $product_id);
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$totalRow = $totalResult->fetch_assoc();
$totalStmt->close();

$totalRatings = $totalRow['total'];
$averageRating = round($totalRow['average'], 1); // Round to 1 decimal place

// Step 3: Get Rating Distribution (1 to 5 stars)
$ratingDistribution = [
    "5" => 0,
    "4" => 0,
    "3" => 0,
    "2" => 0,
    "1" => 0
];

if ($totalRatings > 0) {
    $distQuery = "SELECT rating, COUNT(*) as count FROM product_reviews WHERE product_id = ? GROUP BY rating";
    $distStmt = $conn->prepare($distQuery);
    $distStmt->bind_param("i", $product_id);
    $distStmt->execute();
    $distResult = $distStmt->get_result();

    while ($row = $distResult->fetch_assoc()) {
        $rating = $row['rating'];
        $count = $row['count'];
        $ratingDistribution[strval($rating)] = $count;
    }
    $distStmt->close();

    // Calculate percentages
    foreach ($ratingDistribution as $star => $count) {
        $ratingDistribution[$star] = round(($count / $totalRatings) * 100); // Percentage
    }
}

// Step 4: Prepare Response
$response = [
    "success" => true,
    "total_ratings" => $totalRatings,
    "average_rating" => $averageRating,
    "rating_distribution" => $ratingDistribution
];

$conn->close();
echo json_encode($response);
?>