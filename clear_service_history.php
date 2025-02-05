<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
require 'db.php';

// ✅ Decode JSON input
$data = json_decode(file_get_contents("php://input"), true);

// ✅ Use "mobile_user_id" to match database column
if (!$data || !isset($data["mobile_user_id"])) {
    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Missing mobile_user_id"]);
    exit();
}

$mobile_user_id = intval($data["mobile_user_id"]);

// ✅ Check if service history exists
$checkHistory = $conn->prepare("SELECT id FROM service_requests WHERE mobile_user_id = ?");
if (!$checkHistory) {
    http_response_code(500);
    die(json_encode(["success" => false, "error" => "SQL Error (checkHistory): " . $conn->error]));
}
$checkHistory->bind_param("i", $mobile_user_id);
$checkHistory->execute();
$historyResult = $checkHistory->get_result();

if ($historyResult->num_rows === 0) {
    http_response_code(200); // Not an error, just no history
    echo json_encode(["success" => false, "error" => "No service history found"]);
    $checkHistory->close();
    $conn->close();
    exit();
}
$checkHistory->close();

// ✅ Delete service history using mobile_user_id
$query = $conn->prepare("DELETE FROM service_requests WHERE mobile_user_id = ?");
if (!$query) {
    http_response_code(500);
    die(json_encode(["success" => false, "error" => "SQL Error (delete): " . $conn->error]));
}
$query->bind_param("i", $mobile_user_id);
if ($query->execute()) {
    http_response_code(200);
    echo json_encode(["success" => true, "message" => "✅ Service history cleared"]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "❌ Failed to clear history",
        "sql_error" => $conn->error
    ]);
}

$query->close();
$conn->close();
?>
