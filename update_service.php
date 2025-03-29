<?php
// update_service.php

// --- No output or whitespace BEFORE this tag ---

// Enable error reporting and log errors
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');

// Send JSON and CORS headers (must be sent before any output)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Include your database connection
include 'db.php'; // Adjust path if necessary

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
    exit;
}

// Log POST and FILES data for debugging (check debug.log)
file_put_contents("debug.log", "POST: " . print_r($_POST, true) . "\nFILES: " . print_r($_FILES, true) . "\n", FILE_APPEND);

// Check for required fields
$requiredFields = ['service_id', 'service_name', 'price', 'description', 'start_time', 'end_time', 'start_day', 'end_day'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field])) {
        echo json_encode(["success" => false, "error" => "Missing required field: $field"]);
        exit;
    }
}

// Sanitize and assign variables
$service_id   = (int) $_POST['service_id'];
$service_name = trim($_POST['service_name']);
$price        = trim($_POST['price']);
$description  = trim($_POST['description']);
$start_time   = !empty($_POST['start_time']) ? $_POST['start_time'] : null;
$end_time     = !empty($_POST['end_time']) ? $_POST['end_time'] : null;
$start_day    = !empty($_POST['start_day']) ? $_POST['start_day'] : null;
$end_day      = !empty($_POST['end_day']) ? $_POST['end_day'] : null;

// Validate price
if (!is_numeric($price) || (float)$price < 0) {
    echo json_encode(["success" => false, "error" => "Invalid price value"]);
    exit;
}

// Validate time range (end_time must be after start_time)
if ($start_time && $end_time) {
    if (strtotime($end_time) <= strtotime($start_time)) {
        echo json_encode(["success" => false, "error" => "End time must be after start time"]);
        exit;
    }
}

// Validate day range
$days_of_week = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
if ($start_day && $end_day) {
    $start_index = array_search($start_day, $days_of_week);
    $end_index   = array_search($end_day, $days_of_week);
    if ($start_index === false || $end_index === false || $end_index < $start_index) {
        echo json_encode(["success" => false, "error" => "Invalid day range"]);
        exit;
    }
}

// Get current image from database
$stmt = $conn->prepare("SELECT image FROM services WHERE service_id = ? AND removed = 0");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$row = $result->fetch_assoc()) {
    echo json_encode(["success" => false, "error" => "Service not found"]);
    exit;
}
$current_image = $row['image'];
$stmt->close();

// Handle file upload if a new image is provided
$image_path = $current_image;
if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    $file_name  = uniqid('service_', true) . '_' . basename($_FILES['image']['name']);
    $image_path = 'uploads/' . $file_name;
    $full_path  = $upload_dir . $file_name;
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $full_path)) {
        echo json_encode(["success" => false, "error" => "Failed to upload image"]);
        exit;
    }
    // Delete the old image if it exists
    if ($current_image && file_exists(__DIR__ . '/' . $current_image)) {
        unlink(__DIR__ . '/' . $current_image);
    }
}

// Prepare the update query
$query = "UPDATE services 
          SET service_name = ?, 
              price = ?, 
              description = ?, 
              start_time = ?, 
              end_time = ?, 
              start_day = ?, 
              end_day = ?";

if ($image_path !== $current_image) {
    $query .= ", image = ?";
}
$query .= " WHERE service_id = ? AND removed = 0";

$stmt = $conn->prepare($query);
if (!$stmt) {
    echo json_encode(["success" => false, "error" => "Database prepare error"]);
    exit;
}

// Bind parameters based on whether a new image was uploaded
if ($image_path !== $current_image) {
    $stmt->bind_param("sdssssssi", $service_name, $price, $description, $start_time, $end_time, $start_day, $end_day, $image_path, $service_id);
} else {
    $stmt->bind_param("sdsssssi", $service_name, $price, $description, $start_time, $end_time, $start_day, $end_day, $service_id);
}

// Execute and return JSON
if ($stmt->execute()) {
    echo json_encode(["success" => true, "image" => $image_path]);
} else {
    echo json_encode(["success" => false, "error" => "Database update failed: " . $stmt->error]);
}
$stmt->close();
$conn->close();
?>