<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

try {
    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

    $query = "
        SELECT 
            s.id,
            s.service_name,
            s.description,
            s.status,
            sr.mobile_user_id as user_id,
            sr.selected_date
        FROM services s
        LEFT JOIN service_requests sr ON s.id = sr.service_id
    ";

    if ($user_id) {
        $query .= " WHERE sr.mobile_user_id = ? OR sr.mobile_user_id IS NULL";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
    } else {
        $stmt = $conn->prepare($query);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = array(
            'id' => intval($row['id']),
            'service_name' => $row['service_name'],
            'description' => $row['description'],
            'status' => $row['status'] ?? '',
            'user_id' => $row['user_id'] ? intval($row['user_id']) : null,
            'selected_date' => $row['selected_date'] ?? ''
        );
    }
    
    echo json_encode($services);
    
} catch (Exception $e) {
    error_log("Error in fetch_services.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        'error' => true,
        'message' => $e->getMessage()
    ));
}
?>