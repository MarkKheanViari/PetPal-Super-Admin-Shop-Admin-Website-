<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "db.php";

// DEBUG: Check database connection
if (!$conn) {
    die(json_encode(["success" => false, "message" => "Database connection failed"]));
}

// DEBUG: Check if service_requests table has data
$debugResult = $conn->query("SELECT COUNT(*) AS count FROM service_requests");
$debugRow = $debugResult->fetch_assoc();
if ($debugRow['count'] == 0) {
    die(json_encode(["success" => false, "message" => "No service requests found"]));
}

// Corrected SQL Query
$query = "SELECT sr.id, s.service_name, mu.username AS user_name, sr.selected_date, sr.status 
          FROM service_requests sr
          JOIN services s ON sr.service_id = s.id
          JOIN mobile_users mu ON sr.mobile_user_id = mu.id  -- ✅ FIXED
          WHERE sr.status = 'pending'
          ORDER BY sr.id DESC";


$result = $conn->query($query);

// If query fails, return an error message
if (!$result) {
    die(json_encode(["success" => false, "message" => "Database error: " . $conn->error]));
}

// Fetch Data
$service_requests = [];
while ($row = $result->fetch_assoc()) {
    $service_requests[] = $row;
}

// DEBUG: Log raw JSON output
file_put_contents("debug_log.txt", "[" . date("Y-m-d H:i:s") . "] API OUTPUT: " . json_encode($service_requests) . PHP_EOL, FILE_APPEND);

// Return JSON response
echo json_encode($service_requests, JSON_PRETTY_PRINT);
$conn->close();
?>
