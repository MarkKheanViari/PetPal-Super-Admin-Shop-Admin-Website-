@ -0,0 +1,31 @@
<?php
header("Content-Type: application/json");
include "db_connection.php";

// Ensure the request is a POST request
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// Get the service ID from the POST data
$service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;

if ($service_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid service ID"]);
    exit;
}

// Prepare and execute the SQL query to reset the service status
$stmt = $conn->prepare("UPDATE service_requests SET status = NULL, user_id = NULL WHERE id = ?");
$stmt->bind_param("i", $service_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Service status reset successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to reset service status"]);
}

$stmt->close();
$conn->close();
?>