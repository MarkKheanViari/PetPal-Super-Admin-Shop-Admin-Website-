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
    $shopOwnerId = $_POST["shop_owner_id"] ?? "";

    if (empty($id) || empty($name) || empty($price) || empty($description) || empty($quantity) || empty($shopOwnerId)) {
        $response["message"] = "Missing required fields.";
        echo json_encode($response);
        exit;
    }

    // Handle Image Upload (if provided)
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

    // Update Product in Database
    $sql = "UPDATE products SET name=?, price=?, description=?, quantity=? " . 
           ($imageFileName ? ", image=?" : "") . " WHERE id=? AND shop_owner_id=?";
    $stmt = $conn->prepare($sql);

    if ($imageFileName) {
        $stmt->bind_param("sdsisii", $name, $price, $description, $quantity, $imageFileName, $id, $shopOwnerId);
    } else {
        $stmt->bind_param("sdsiii", $name, $price, $description, $quantity, $id, $shopOwnerId);
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
