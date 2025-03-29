<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log'); // Log errors to php_error.log

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php'; // Include database connection (MySQLi $conn)

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    error_log("❌ Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$type = '';

if (strpos($_SERVER['SCRIPT_NAME'], 'grooming') !== false) {
    $type = 'Grooming';
} elseif (strpos($_SERVER['SCRIPT_NAME'], 'veterinary') !== false) {
    $type = 'Veterinary';
} else {
    echo json_encode(['success' => false, 'error' => 'Unknown service type']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT service_id, type, service_name, price, description, image, start_time, end_time, start_day, end_day, removed FROM services WHERE type = ?");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $result = $stmt->get_result();

    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = [
            'service_id' => (int)$row['service_id'],
            'type' => $row['type'],
            'service_name' => $row['service_name'],
            'price' => (float)$row['price'],
            'description' => $row['description'],
            'image' => $row['image'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'start_day' => $row['start_day'],
            'end_day' => $row['end_day'],
            'removed' => (int)$row['removed']
        ];
    }

    echo json_encode(['success' => true, 'services' => $services]);

} catch (Exception $e) {
    error_log("❌ Error fetching $type services: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch services: ' . $e->getMessage(),
        'services' => []
    ]);
} finally {
    $conn->close();
}
?>
