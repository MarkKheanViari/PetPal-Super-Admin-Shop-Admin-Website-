<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");
include 'db.php';

$sql = "SELECT * FROM services WHERE type = 'Grooming'";
$result = $conn->query($sql);

$services = array();
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    echo json_encode(['success' => true, 'services' => $services]);
} else {
    echo json_encode(['success' => false, 'services' => []]);
}

$conn->close();
?>
