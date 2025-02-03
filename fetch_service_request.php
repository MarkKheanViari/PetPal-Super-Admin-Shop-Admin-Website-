<?php
include 'db.php';

header("Content-Type: application/json");

$query = "SELECT sr.id, s.service_name, u.username, sr.selected_date, sr.status 
          FROM service_requests sr
          JOIN services s ON sr.service_id = s.id
          JOIN mobile_users u ON sr.user_id = u.id
          ORDER BY sr.created_at DESC";

$result = $conn->query($query);
$requests = [];

while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

// Log output for debugging
error_log("✅ Fetched Service Requests: " . print_r($requests, true));

echo json_encode($requests);
?>
