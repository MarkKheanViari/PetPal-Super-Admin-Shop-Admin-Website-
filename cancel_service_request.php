<?php
require 'db.php'; // Ensure database connection

header("Content-Type: application/json");

// Get service request ID
$request_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($request_id === 0) {
    echo json_encode(["error" => "Invalid request ID"]);
    exit();
}

// Check if status is still pending
$sql = "SELECT status FROM service_requests WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row || $row['status'] !== 'pending') {
    echo json_encode(["error" => "Cannot cancel a confirmed or declined request"]);
    exit();
}

// Delete the request
$sql = "DELETE FROM service_requests WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $request_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Failed to cancel request"]);
}
?>
