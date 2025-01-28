<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function sendResponse($success, $message, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit();
}

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    if (!$data || !isset($data->id) || !isset($data->status)) {
        throw new Exception("Missing required fields");
    }

    $service_id = intval($data->id);
    $new_status = $data->status;
    $shop_owner_id = isset($data->shop_owner_id) ? intval($data->shop_owner_id) : null;

    $allowed_statuses = ['pending', 'confirmed', 'declined'];
    if (!in_array(strtolower($new_status), $allowed_statuses)) {
        throw new Exception("Invalid status value");
    }

    $stmt = $conn->prepare("
        UPDATE services 
        SET status = ?, 
            shop_owner_id = CASE 
                WHEN status = 'pending' AND ? IN ('confirmed', 'declined') 
                THEN ? 
                ELSE shop_owner_id 
            END 
        WHERE id = ?
    ");
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("ssii", $new_status, $new_status, $shop_owner_id, $service_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        sendResponse(true, "Status updated successfully");
    } else {
        sendResponse(false, "No service found with ID: $service_id", 404);
    }

} catch (Exception $e) {
    error_log("Error in update_service_status.php: " . $e->getMessage());
    sendResponse(false, $e->getMessage(), 500);
}
?>