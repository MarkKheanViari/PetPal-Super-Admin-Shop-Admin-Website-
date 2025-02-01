
<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $json = file_get_contents('php://input');
    if (!$json) {
        throw new Exception("No data received");
    }

    $data = json_decode($json);
    if (!$data) {
        throw new Exception("Invalid JSON data");
    }

    if (!isset($data->service_id) || !isset($data->selected_date)) {
        throw new Exception("Missing required fields");
    }

    $service_id = intval($data->service_id);
    $selected_date = $data->selected_date;

    // Check if the date is in the past
    $today = date('Y-m-d');
    if ($selected_date < $today) {
        throw new Exception("Cannot select past dates");
    }

    // Check if there are any existing appointments for this service on this date
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM service_requests 
        WHERE service_id = ? 
        AND selected_date = ? 
        AND status != 'declined'
    ");
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }

    $stmt->bind_param("is", $service_id, $selected_date);
    
    if (!$stmt->execute()) {
        throw new Exception("Query execution failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Assuming each service can only handle one appointment per day
    if ($row['count'] > 0) {
        echo json_encode([
            "success" => false,
            "available" => false,
            "message" => "This date is already booked"
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "available" => true,
            "message" => "Date is available"
        ]);
    }

} catch (Exception $e) {
    error_log("Error in check_date_availability.php: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "available" => false,
        "message" => $e->getMessage()
    ]);
}
?>