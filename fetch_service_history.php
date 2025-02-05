<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "db.php";

// Check if user_id is provided
if (!isset($_GET['user_id'])) {
    die(json_encode(["success" => false, "message" => "Missing user_id"]));
}

$user_id = intval($_GET['user_id']);

// Fetch service history
$query = "SELECT sr.id, s.service_name, sr.selected_date, sr.status 
          FROM service_requests sr
          JOIN services s ON sr.service_id = s.id
          WHERE sr.mobile_user_id = ?
          ORDER BY sr.selected_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$service_history = [];
while ($row = $result->fetch_assoc()) {
    $service_history[] = $row;
}

// Debug log
file_put_contents("debug_log.txt", "[" . date("Y-m-d H:i:s") . "] Service History: " . json_encode($service_history) . PHP_EOL, FILE_APPEND);

// Check if data exists
if (empty($service_history)) {
    echo json_encode(["success" => false, "message" => "No service history found"]);
    exit;
}

echo json_encode($service_history, JSON_PRETTY_PRINT);
$conn->close();
?>
