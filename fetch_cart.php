<?php
include 'db.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// ✅ Get user ID from request
$mobile_user_id = isset($_GET['mobile_user_id']) ? intval($_GET['mobile_user_id']) : 0;

if ($mobile_user_id == 0) {
    echo json_encode(["success" => false, "message" => "Invalid user ID"]);
    exit();
}

// ✅ Fetch cart items with product details
$sql = "
    SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.price, p.image
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.mobile_user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mobile_user_id);
$stmt->execute();
$result = $stmt->get_result();

$cartItems = [];
while ($row = $result->fetch_assoc()) {
    // ✅ Ensure full image URL & handle missing images
    $row['image'] = !empty($row['image']) 
        ? "http://192.168.137.14/backend/uploads/" . $row['image'] 
        : "http://192.168.137.14/backend/uploads/default.jpg";

    $cartItems[] = $row;
}

// ✅ Return response
echo json_encode(["success" => true, "cart" => $cartItems]);

$stmt->close();
$conn->close();
?>
