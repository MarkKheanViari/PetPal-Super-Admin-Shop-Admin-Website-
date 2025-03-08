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

// ✅ Fetch both Approved & Declined appointments
$sql = "SELECT a.id, a.service_name, a.service_type, a.appointment_date, s.price, a.status 
        FROM appointments a 
        JOIN services s ON a.service_name = s.service_name
        WHERE a.mobile_user_id = ? AND a.status IN ('Approved', 'Declined') AND a.notification_sent = 0";

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

    // ✅ Mark as notified so they don't appear again
    $update_sql = "UPDATE appointments SET notification_sent = 1 WHERE mobile_user_id = ? AND status IN ('Approved', 'Declined')";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $mobile_user_id);
    $update_stmt->execute();
} else {
    $response["message"] = "No new notifications";
}

echo json_encode($response);
?>
