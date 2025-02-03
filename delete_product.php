<?php
include 'db.php'; // Ensure database connection

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0); // Handle preflight request
}

// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['product_id']) || !isset($data['shop_owner_id'])) {
    echo json_encode(["success" => false, "error" => "Missing parameters"]);
    exit();
}

$product_id = $data['product_id'];
$shop_owner_id = $data['shop_owner_id'];

// ✅ Debugging Log
file_put_contents("delete_log.txt", "DELETE request received: " . json_encode($data) . "\n", FILE_APPEND);

// ✅ Verify that the product exists before deleting
$check_query = "SELECT * FROM products WHERE id = ? AND shop_owner_id = ?";
$stmt_check = $conn->prepare($check_query);
$stmt_check->bind_param("ii", $product_id, $shop_owner_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["success" => false, "error" => "Product not found"]);
    exit();
}

// ✅ Execute the DELETE query
$sql = "DELETE FROM products WHERE id = ? AND shop_owner_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $product_id, $shop_owner_id);
$result = $stmt->execute();

if ($result) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Database error: " . $conn->error]);
}

// Close statements and connection
$stmt_check->close();
$stmt->close();
$conn->close();
?>
