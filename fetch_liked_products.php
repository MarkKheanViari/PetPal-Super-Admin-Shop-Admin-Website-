<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type");

include "db.php";

$response = ["success" => false, "message" => "Unknown error"];

if (!isset($_GET['mobile_user_id'])) {
    echo json_encode(["success" => false, "message" => "❌ Mobile User ID is required!"]);
    exit();
}

$mobile_user_id = $_GET['mobile_user_id'];

// Step 1: Check if the User Exists
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

// Step 2: Fetch Liked Products
$query = "
    SELECT p.id, p.name, p.price, p.description, p.image
    FROM liked_products lp
    JOIN products p ON lp.product_id = p.id
    WHERE lp.mobile_user_id = ?
    ORDER BY lp.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $mobile_user_id);
$stmt->execute();
$result = $stmt->get_result();

$likedProducts = [];
$baseImageUrl = "http://192.168.1.65/backend/uploads/";

while ($row = $result->fetch_assoc()) {
    $rawImage = $row['image'] ?? '';
    $fullImageUrl = !empty($rawImage) && !str_starts_with($rawImage, 'http') 
        ? $baseImageUrl . $rawImage 
        : ($rawImage ?: $baseImageUrl . "default.jpg");

    $likedProducts[] = [
        "id" => $row['id'],
        "name" => $row['name'],
        "price" => $row['price'],
        "description" => $row['description'],
        "image" => $fullImageUrl,
        "quantity" => 1 // Default quantity for display purposes
    ];
}

$stmt->close();
$conn->close();

echo json_encode([
    "success" => true,
    "liked_products" => $likedProducts
]);
?>