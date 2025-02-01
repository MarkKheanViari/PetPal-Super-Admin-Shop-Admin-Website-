<?php
include 'db.php'; // Include database connection

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $shop_owner_id = $_POST['shop_owner_id'] ?? '';
    $service_name = $_POST['service_name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';
    $status = $_POST['status'] ?? 'Available'; // Default status

    if (empty($shop_owner_id) || empty($service_name) || empty($price)) {
        echo json_encode(["success" => false, "error" => "All fields are required"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO services (shop_owner_id, service_name, description, price, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issds", $shop_owner_id, $service_name, $description, $price, $status);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Service added successfully"]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to add service"]);
    }

    $stmt->close();
    $conn->close();
}
?>
