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

    // ✅ Debug: Log received values
    error_log("🔍 Updating Product ID: $id");
    error_log("📌 Name: $name, Price: $price, Desc: $description, Quantity: $quantity, Category: $category, Shop Owner: $shopOwnerId");

    // ✅ Validate Required Fields
    if (empty($id) || empty($name) || empty($price) || empty($description) || empty($quantity) || empty($shopOwnerId) || empty($category)) {
        error_log("❌ Missing required fields");
        echo json_encode(["success" => false, "message" => "Missing required fields"]);
        exit();
    }

    // ✅ Ensure category is valid
    $allowed_categories = ["Food", "Treats", "Essentials", "Supplies", "Accessories", "Grooming", "Hygiene", "Toys", "Enrichment", "Healthcare", "Training"];
    if (!in_array($category, $allowed_categories)) {
        error_log("❌ Invalid category: $category");
        echo json_encode(["success" => false, "message" => "Invalid category: $category"]);
        exit();
    }

    // ✅ Fetch existing product data (to retain current image if not updated)
    $checkStmt = $conn->prepare("SELECT category, image FROM products WHERE id = ? AND shop_owner_id = ?");
    $checkStmt->bind_param("ii", $id, $shopOwnerId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        error_log("❌ Product not found for update.");
        echo json_encode(["success" => false, "message" => "Product not found."]);
        exit();
    }

    $existingProduct = $checkResult->fetch_assoc();
    $existingImage = $existingProduct['image']; // ✅ Keep existing image if no new one is uploaded

    error_log("📌 Existing Image in DB: " . $existingImage);

    // ✅ Handle Image Upload (Only Update If a New Image is Uploaded)
    if (!empty($_FILES["product_image"]["name"])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $imageFileName = time() . "_" . basename($_FILES["product_image"]["name"]);
        $targetFilePath = $targetDir . $imageFileName;

        if (!move_uploaded_file($_FILES["product_image"]["tmp_name"], $targetFilePath)) {
            error_log("❌ Failed to upload image.");
            echo json_encode(["success" => false, "message" => "❌ Failed to upload image."]);
            exit();
        }
    } else {
        $imageFileName = $existingImage; // ✅ Keep the old image if no new one is uploaded
    }

    // ✅ Debugging Image Before Updating
    error_log("📌 Final Image Used: " . $imageFileName);

    // ✅ Update SQL Query (Always Keep Image)
    $sql = "UPDATE products SET name=?, price=?, description=?, quantity=?, category=?, image=? WHERE id=? AND shop_owner_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdsssisi", $name, $price, $description, $quantity, $category, $imageFileName, $id, $shopOwnerId);

    // ✅ Execute the update query
    if ($stmt->execute()) {
        error_log("✅ SQL Update Successful: Category updated to $category, Image retained as $imageFileName");
        echo json_encode(["success" => true, "message" => "✅ Product updated successfully"]);
    } else {
        error_log("❌ SQL Update Failed: " . $stmt->error);
        echo json_encode(["success" => false, "message" => "❌ Database error: " . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>
