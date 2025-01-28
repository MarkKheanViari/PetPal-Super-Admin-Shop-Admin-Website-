<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    if (!$data || !isset($data->service_id) || !isset($data->user_id) || !isset($data->status) || !isset($data->selected_date)) {
        throw new Exception("Missing required fields");
    }

    $service_id = intval($data->service_id);
    $user_id = intval($data->user_id);
    $status = $data->status;
    $selected_date = $data->selected_date;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if service is already requested for this date
        $stmt = $conn->prepare("
            SELECT id FROM service_requests 
            WHERE service_id = ? 
            AND selected_date = ?
            AND status != 'declined'
        ");
        $stmt->bind_param("is", $service_id, $selected_date);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            throw new Exception("Service is already booked for this date");
        }

        // Insert service request
        $stmt = $conn->prepare("
            INSERT INTO service_requests (service_id, mobile_user_id, status, selected_date) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiss", $service_id, $user_id, $status, $selected_date);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create service request");
        }

        // Update service status
        $stmt = $conn->prepare("
            UPDATE services 
            SET status = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("si", $status, $service_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update service status");
        }

        $conn->commit();

        echo json_encode([
            "success" => true,
            "message" => "Service request submitted successfully"
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error in request_service.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>