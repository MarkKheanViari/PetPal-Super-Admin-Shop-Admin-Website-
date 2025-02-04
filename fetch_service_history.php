<?php
require 'db.php'; // Ensure database connection

header("Content-Type: application/json");

// Get user ID from request
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id === 0) {
    echo json_encode(["error" => "User ID is required"]);
    exit();
}

// Fetch service history for this user
$sql = "SELECT sr.id, sr.selected_date, sr.status, s.service_name, s.description 
        FROM service_requests sr
        JOIN services s ON sr.service_id = s.id
        WHERE sr.user_id = ? 
        ORDER BY sr.selected_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$history = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $history[] = [
            "id" => $row["id"],
            "service_name" => $row["service_name"],
            "description" => $row["description"],
            "selected_date" => $row["selected_date"],
            "status" => $row["status"]
        ];
    }
}

echo json_encode(["history" => $history]);
?>
