<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$response = ["success" => false];

// ✅ Check if `mobile_user_id` is provided
if (!isset($_GET["mobile_user_id"])) {
    $response["message"] = "Missing user ID";
    echo json_encode($response);
    exit();
}

$mobile_user_id = intval($_GET["mobile_user_id"]);

// ✅ Debug: Log the user ID received
error_log("Fetching mobile appointments for user_id: $mobile_user_id");

// ✅ Fetch appointments from `mobile_appointments` and join with `services` to get price
$sql = "SELECT a.service_name, a.service_type, a.appointment_date, s.price, a.status 
        FROM mobile_appointments a 
        JOIN services s ON a.service_name = s.service_name
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

// ✅ Debug: Log response before sending
error_log("Response: " . json_encode($response));

echo json_encode($response);

// Close connections
$stmt->close();
$conn->close();
?>
