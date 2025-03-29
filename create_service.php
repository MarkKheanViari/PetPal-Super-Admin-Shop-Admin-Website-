<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
include 'db.php';

// Check if all required fields are set
if (!isset($_POST['type'], $_POST['service_name'], $_POST['price'], $_POST['description'], $_FILES['image'], $_POST['start_time'], $_POST['end_time'], $_POST['start_day'], $_POST['end_day'])) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields.']);
    exit;
}

$type = $conn->real_escape_string($_POST['type']);
$service_name = $conn->real_escape_string($_POST['service_name']);
$price = $conn->real_escape_string($_POST['price']);
$description = $conn->real_escape_string($_POST['description']);
$start_time = !empty($_POST['start_time']) ? $conn->real_escape_string($_POST['start_time']) : null;
$end_time = !empty($_POST['end_time']) ? $conn->real_escape_string($_POST['end_time']) : null;
$start_day = !empty($_POST['start_day']) ? $conn->real_escape_string($_POST['start_day']) : null;
$end_day = !empty($_POST['end_day']) ? $conn->real_escape_string($_POST['end_day']) : null;

// Validate price
if (!is_numeric($price) || $price < 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid price value']);
    exit;
}

// Validate time range
if ($start_time && $end_time) {
    $start = strtotime($start_time);
    $end = strtotime($end_time);
    if ($end <= $start) {
        echo json_encode(['success' => false, 'error' => 'End time must be after start time']);
        exit;
    }
}

// Validate day range
$days_of_week = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
if ($start_day && $end_day) {
    $start_index = array_search($start_day, $days_of_week);
    $end_index = array_search($end_day, $days_of_week);
    if ($start_index === false || $end_index === false) {
        echo json_encode(['success' => false, 'error' => 'Invalid day value']);
        exit;
    }
    if ($end_index < $start_index) {
        echo json_encode(['success' => false, 'error' => 'End day must be on or after start day']);
        exit;
    }
}

// Handle image upload
$imageFile = $_FILES['image'];
$targetDir = "uploads/";

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$extension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
$uniqueName = uniqid('service_', true) . '.' . $extension;
$targetFilePath = $targetDir . $uniqueName;

// Move uploaded file
if (move_uploaded_file($imageFile['tmp_name'], $targetFilePath)) {
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO services (type, service_name, price, description, image, start_time, end_time, start_day, end_day) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdssssss", $type, $service_name, $price, $description, $targetFilePath, $start_time, $end_time, $start_day, $end_day);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to upload image.']);
}

$conn->close();
?>