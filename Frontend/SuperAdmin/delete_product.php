<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set("display_errors", 0);
ini_set("log_errors", 1);
ini_set("error_log", "C:/xampp/htdocs/backend/php_errors.log");

// Set headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once $_SERVER['DOCUMENT_ROOT'] . "/backend/db.php";

$response = array();

// Check if the database connection is successful
if (!$conn) {
    $response["error"] = "Database connection failed: " . mysqli_connect_error();
    echo json_encode($response);
    exit;
}

// Check if product_id is provided
if (!isset($_POST['product_id'])) {
    $response["error"] = "Product ID is required";
    echo json_encode($response);
    exit;
}

$product_id = (int)$_POST['product_id'];

// Start a transaction to ensure data consistency
$conn->begin_transaction();

try {
    // Fetch the product's image file name (if any)
    $query = "SELECT image FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $image_file = $product['image'] ?? null;
    $stmt->close();

    // Delete associated reports
    $query = "DELETE FROM reports WHERE product_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();

    // Delete the product
    $query = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete product: " . $stmt->error);
    }
    $stmt->close();

    // Delete the image file if it exists
    if ($image_file) {
        $image_path = $_SERVER['DOCUMENT_ROOT'] . "/backend/uploads/" . $image_file;
        if (file_exists($image_path)) {
            if (!unlink($image_path)) {
                error_log("Failed to delete image file: $image_path");
            }
        }
    }

    // Commit the transaction
    $conn->commit();
    $response["success"] = "Product deleted successfully";
} catch (Exception $e) {
    // Roll back the transaction on error
    $conn->rollback();
    $response["error"] = $e->getMessage();
}

$conn->close();

// Ensure the response is always valid JSON
echo json_encode($response);
exit;
?>