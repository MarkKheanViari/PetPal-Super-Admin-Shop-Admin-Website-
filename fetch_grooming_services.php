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

try {
    // Prepare and execute the query to fetch grooming services
    $sql = "SELECT * FROM services WHERE type = 'Grooming'";
    $result = $conn->query($sql);

    if ($result === false) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    $services = [];
    while ($row = $result->fetch_assoc()) {
        // Ensure all fields are included, including start_time, end_time, start_day, end_day
        $services[] = [
            'id' => $row['id'],
            'type' => $row['type'],
            'service_name' => $row['service_name'],
            'price' => $row['price'],
            'description' => $row['description'],
            'image' => $row['image'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'start_day' => $row['start_day'],
            'end_day' => $row['end_day'],
            'removed' => $row['removed']
        ];
    }

    // Log success
    error_log("✅ Successfully fetched " . count($services) . " grooming services");

    // Return the response
    echo json_encode([
        'success' => true,
        'services' => $services
    ]);

} catch (Exception $e) {
    // Log the error
    error_log("❌ Error fetching grooming services: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to fetch grooming services: ' . $e->getMessage(),
        'services' => []
    ]);
} finally {
    $conn->close();
}
?>