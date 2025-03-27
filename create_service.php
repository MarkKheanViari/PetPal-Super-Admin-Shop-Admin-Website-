<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
include 'db.php';

// ✅ Check if all required fields are set
if (!isset($_POST['type'], $_POST['service_name'], $_POST['price'], $_POST['description'], $_FILES['image'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
    exit;
}

$type = $conn->real_escape_string($_POST['type']);
$service_name = $conn->real_escape_string($_POST['service_name']);
$price = $conn->real_escape_string($_POST['price']);
$description = $conn->real_escape_string($_POST['description']);

// ✅ Handle image upload
$imageFile = $_FILES['image'];
$targetDir = "uploads/";

// Create uploads directory if it doesn't exist
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$extension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
$uniqueName = uniqid('service_', true) . '.' . $extension;
$targetFilePath = $targetDir . $uniqueName;

// ✅ Move uploaded file
if (move_uploaded_file($imageFile['tmp_name'], $targetFilePath)) {
    // ✅ Save service data including image path to DB
    $sql = "INSERT INTO services (type, service_name, price, description, image) 
            VALUES ('$type', '$service_name', '$price', '$description', '$targetFilePath')";

    if ($conn->query($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to upload image.']);
}
