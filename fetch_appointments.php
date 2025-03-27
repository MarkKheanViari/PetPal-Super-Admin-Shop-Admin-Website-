<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$response = ["success" => false];

if (!isset($_GET["mobile_user_id"])) {
    $response["message"] = "Missing user ID";
    echo json_encode($response);
    exit();
}

$mobile_user_id = intval($_GET["mobile_user_id"]);

error_log("Fetching mobile appointments for user_id: $mobile_user_id");

// ✅ Add s.image to the SELECT
$sql = "SELECT a.service_name, a.service_type, a.appointment_date, s.price, a.status, s.image 
        FROM mobile_appointments a 
        JOIN services s 
          ON a.service_name = s.service_name 
         AND a.service_type = s.type
        WHERE a.mobile_user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $mobile_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    $response["success"] = true;
    $response["appointments"] = $appointments;
} else {
    $response["message"] = "No appointments found";
}

error_log("Response: " . json_encode($response));
echo json_encode($response);

$stmt->close();
$conn->close();
?>
