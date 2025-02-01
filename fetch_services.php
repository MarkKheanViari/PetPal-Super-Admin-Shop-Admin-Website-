<?php
include 'db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$sql = "SELECT * FROM services";
$result = $conn->query($sql);

$services = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $services[] = [
            "id" => $row["id"],
            "service_name" => $row["service_name"],
            "description" => isset($row["description"]) ? $row["description"] : "No description available",
            "status" => isset($row["status"]) ? $row["status"] : "Pending",
            "price" => isset($row["price"]) ? $row["price"] : "0.00",
        ];
    }
}

echo json_encode($services);
$conn->close();
?>
