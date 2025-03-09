<?php
include 'db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$response = ["success" => false, "message" => "Something went wrong"];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['product_name'] ?? "";
    $price = $_POST['product_price'] ?? "";
    $description = $_POST['product_description'] ?? "";
    $quantity = $_POST['product_quantity'] ?? "";
    $category = $_POST['category'] ?? "unknown"; // Default category
    $shopOwnerId = $_POST['shop_owner_id'] ?? "";

    // ✅ Allowed Categories
    $allowed_categories = ["Food", "Treats", "Essentials", "Supplies", "Accessories", "Grooming", "Hygiene", "Toys", "Enrichment", "Healthcare", "Training"];

    // ✅ Validate required fields
    if (empty($name) || empty($price) || empty($description) || empty($quantity) || empty($shopOwnerId)) {
        $response["message"] = "Missing required fields.";
        echo json_encode($response);
        exit;
    }

    // ✅ Validate category
    if (!in_array($category, $allowed_categories)) {
        $response["message"] = "Invalid category.";
        echo json_encode($response);
        exit;
    }

    // ✅ Handle Image Upload
    $imageFileName = null;
    if (!empty($_FILES['product_image']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $imageFileName = time() . '_' . basename($_FILES['product_image']['name']);
        $targetFilePath = $targetDir . $imageFileName;

        if (!move_uploaded_file($_FILES['product_image']['tmp_name'], $targetFilePath)) {
            $response["message"] = "Failed to upload image.";
            echo json_encode($response);
            exit;
        }
    }

    // ✅ Insert Product into Database
    $sql = "INSERT INTO products (name, price, description, quantity, category, image, shop_owner_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdssssi", $name, $price, $description, $quantity, $category, $imageFileName, $shopOwnerId);

    if ($stmt->execute()) {
        $response = ["success" => true, "message" => "Product added successfully", "category" => $category];
    } else {
        $response["message"] = "Database error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
