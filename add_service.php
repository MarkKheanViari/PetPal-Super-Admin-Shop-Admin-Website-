<?php
include 'db.php'; 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use $_POST instead of JSON decode, since FormData is being sent
    $shop_owner_id = isset($_POST['shop_owner_id']) ? intval($_POST['shop_owner_id']) : 0;
    $service_name = isset($_POST['service_name']) ? trim($_POST['service_name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;

    if ($shop_owner_id == 0 || empty($service_name) || empty($description) || $price <= 0) {
        echo json_encode(["success" => false, "error" => "Missing required fields"]);
        exit();
    }

    $status = "Available";

    $sql = "INSERT INTO services (shop_owner_id, service_name, description, price, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["success" => false, "error" => "Database error: " . $conn->error]);
        exit();
    }

    $stmt->bind_param("issds", $shop_owner_id, $service_name, $description, $price, $status);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Service added successfully"]);
    } else {
        echo json_encode(["success" => false, "error" => "Database error: " . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}
?>
