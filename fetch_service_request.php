<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "db.php";

// Corrected SQL Query
$query = "SELECT sr.id, s.service_name, mu.username AS user_name, sr.selected_date, sr.status 
          FROM service_requests sr
          JOIN services s ON sr.service_id = s.id
          JOIN mobile_users mu ON sr.user_id = mu.id
          WHERE sr.status = 'pending'
          ORDER BY sr.id DESC";


$result = $conn->query($query);

// If query fails, return an error message
if (!$result) {
    echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    exit;
}

// Fetch Data
$service_requests = [];
while ($row = $result->fetch_assoc()) {
    $service_requests[] = $row;
}

// Return JSON response
echo json_encode($service_requests, JSON_PRETTY_PRINT);
$conn->close();
?>
