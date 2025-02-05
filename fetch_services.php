<?php
include 'db.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Enable error reporting (for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch all services
$sql = "SELECT id, service_name, description, price, status FROM services WHERE status = 'Available'";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    exit;
}

$services = [];
while ($row = $result->fetch_assoc()) {
    $services[] = [
        "id" => (int)$row["id"], // ✅ Ensure `id` is an integer
        "service_name" => $row["service_name"],
        "description" => $row["description"],
        "price" => $row["price"],
        "status" => $row["status"]
    ];
}

// ✅ Output JSON only once
echo json_encode($services, JSON_PRETTY_PRINT);
$conn->close();
exit; // ✅ Stop execution after JSON output
?>
