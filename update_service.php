<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log'); // Log errors to php_error.log

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php'; // ✅ Using MySQLi ($conn)

header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("❌ Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
    exit;
}

// Log received data for debugging
file_put_contents("debug.log", "POST: " . print_r($_POST, true) . "\nFILES: " . print_r($_FILES, true) . "\n", FILE_APPEND);

// Check required fields
if (!isset($_POST['service_name']) || !isset($_POST['price']) || !isset($_POST['description'])) {
    error_log("❌ Missing required input data: " . print_r($_POST, true));
    echo json_encode(["success" => false, "error" => "Invalid input data", "received" => $_POST]);
    exit;
}

// Assign values
$service_name = $_POST['service_name'];
$price = $_POST['price'];
$description = $_POST['description'];

// Validate price
if (!is_numeric($price) || $price < 0) {
    error_log("❌ Invalid price value: " . $price);
    echo json_encode(["success" => false, "error" => "Invalid price value"]);
    exit;
}

// Fetch the current image path (to delete it if a new image is uploaded)
$current_image = null;
$stmt = $conn->prepare("SELECT image FROM services WHERE service_name = ? AND removed = 0");
if (!$stmt) {
    error_log("❌ SQL Prepare Error (SELECT): " . $conn->error);
    echo json_encode(["success" => false, "error" => "Database prepare failed (SELECT)"]);
    exit;
}
$stmt->bind_param("s", $service_name);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $current_image = $row['image'];
} else {
    error_log("❌ No service found with name: " . $service_name);
    echo json_encode(["success" => false, "error" => "Service not found"]);
    exit;
}
$stmt->close();

// Handle file upload (optional)
$image_path = $current_image; // Default to current image
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/uploads/'; // Ensure this directory exists and is writable
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $file_name = uniqid('service_', true) . '_' . basename($_FILES['image']['name']);
    $image_path = 'uploads/' . $file_name;
    $full_path = $upload_dir . $file_name;

    // Move the new file
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $full_path)) {
        error_log("❌ Failed to move uploaded file: " . $_FILES['image']['name']);
        echo json_encode(["success" => false, "error" => "Failed to upload image"]);
        exit;
    }

    // Delete the old image file if it exists
    if ($current_image && file_exists(__DIR__ . '/' . $current_image)) {
        unlink(__DIR__ . '/' . $current_image);
        error_log("✅ Deleted old image: " . $current_image);
    }
}

// Prepare SQL query
$query = "UPDATE services SET price = ?, description = ?" . ($image_path !== $current_image ? ", image = ?" : "") . " WHERE service_name = ? AND removed = 0";
$stmt = $conn->prepare($query);
if (!$stmt) {
    error_log("❌ SQL Prepare Error (UPDATE): " . $conn->error);
    echo json_encode(["success" => false, "error" => "Database prepare failed (UPDATE)"]);
    exit;
}

// Bind parameters dynamically based on whether the image has changed
if ($image_path !== $current_image) {
    $stmt->bind_param("dsss", $price, $description, $image_path, $service_name);
} else {
    $stmt->bind_param("dss", $price, $description, $service_name);
}

// Debug log before execution
error_log("✅ Running SQL Update: service_name=$service_name, price=$price, description=$description" . ($image_path !== $current_image ? ", image=$image_path" : ""));

// Execute the query
$result = $stmt->execute();

if ($result) {
    error_log("✅ Service updated successfully!");
    echo json_encode([
        "success" => true,
        "message" => "Service updated successfully",
        "image" => $image_path // Return the updated image path
    ]);
} else {
    error_log("❌ SQL Execute Error: " . $stmt->error);
    echo json_encode(["success" => false, "error" => "Database update failed: " . $stmt->error]);
}

// Close statement and connection
$stmt->close();
$conn->close();
?>