<?php
include 'db.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Fetch active services
$sql = "SELECT id, service_name, description, price, status FROM services WHERE status = 'Available'";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    exit;
}

$services = [];
while ($row = $result->fetch_assoc()) {
    $services[] = [
        "id" => (int)$row["id"],
        "service_name" => $row["service_name"],
        "description" => $row["description"],
        "price" => (float)$row["price"],
        "status" => $row["status"]
    ];
}

// ✅ Return JSON response
echo json_encode([
    "success" => true,
    "services" => $services
], JSON_PRETTY_PRINT);

$conn->close();
exit;
?>
