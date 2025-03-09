<?php
include 'db.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$response = ["success" => false, "message" => "Something went wrong"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["product_id"] ?? "";
    $name = $_POST["product_name"] ?? "";
    $price = $_POST["product_price"] ?? "";
    $description = $_POST["product_description"] ?? "";
    $quantity = $_POST["product_quantity"] ?? "";
    $category = $_POST["category"] ?? "";
    $shopOwnerId = $_POST["shop_owner_id"] ?? "";

    // ✅ Allowed Categories
    $allowed_categories = ["Food", "Treats", "Essentials", "Supplies", "Accessories", "Grooming", "Hygiene", "Toys", "Enrichment", "Healthcare", "Training"];

    // ✅ Validate required fields
    if (empty($id) || empty($name) || empty($price) || empty($description) || empty($quantity) || empty($shopOwnerId)) {
        $response["message"] = "Missing required fields.";
        echo json_encode($response);
        exit;
    }

    // ✅ Validate category
    if (!empty($category) && !in_array($category, $allowed_categories)) {
        $response["message"] = "Invalid category.";
        echo json_encode($response);
        exit;
    }

    // ✅ Handle Image Upload (if provided)
    $imageFileName = null;
    if (!empty($_FILES["product_image"]["name"])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $imageFileName = time() . "_" . basename($_FILES["product_image"]["name"]);
        $targetFilePath = $targetDir . $imageFileName;

        if (!move_uploaded_file($_FILES["product_image"]["tmp_name"], $targetFilePath)) {
            $response["message"] = "Failed to upload image.";
            echo json_encode($response);
            exit;
        }
    }

    // ✅ Update Product in Database
    $sql = "UPDATE products SET name=?, price=?, description=?, quantity=?, category=? " . 
           ($imageFileName ? ", image=?" : "") . " WHERE id=? AND shop_owner_id=?";
    $stmt = $conn->prepare($sql);

    if ($imageFileName) {
        $stmt->bind_param("sdsssisi", $name, $price, $description, $quantity, $category, $imageFileName, $id, $shopOwnerId);
    } else {
        $stmt->bind_param("sdssisi", $name, $price, $description, $quantity, $category, $id, $shopOwnerId);
    }

    if ($stmt->execute()) {
        $response = ["success" => true, "message" => "Product updated successfully"];
    } else {
        $response["message"] = "Database error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
