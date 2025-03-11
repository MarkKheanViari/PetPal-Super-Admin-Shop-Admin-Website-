<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$response = ["success" => false];

// ✅ Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// ✅ Debug: Log the request
file_put_contents("debug_update_status.txt", "Incoming request: " . print_r($data, true) . "\n", FILE_APPEND);

// ✅ Check if required fields are present
if (!isset($data["id"]) || !isset($data["status"])) {
    file_put_contents("debug_update_status.txt", "❌ Missing required fields\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit();
}

$id = intval($data["id"]);
$status = $data["status"];

// ✅ Debug: Log received `id` & `status`
file_put_contents("debug_update_status.txt", "Processing update for ID: $id with status: $status\n", FILE_APPEND);

// ✅ Check if the `id` exists in `appointments`
$check_sql = "SELECT id FROM appointments WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    file_put_contents("debug_update_status.txt", "❌ No matching ID in `appointments`\n", FILE_APPEND);
    echo json_encode(["success" => false, "message" => "ID not found in `appointments`"]);
    exit();
}

// ✅ Check if the `id` exists in `mobile_appointments`
$check_sql_mobile = "SELECT id FROM mobile_appointments WHERE id = ?";
$check_stmt_mobile = $conn->prepare($check_sql_mobile);
$check_stmt_mobile->bind_param("i", $id);
$check_stmt_mobile->execute();
$result_mobile = $check_stmt_mobile->get_result();

if ($result_mobile->num_rows === 0) {
    file_put_contents("debug_update_status.txt", "⚠️ No matching ID in `mobile_appointments` (Proceeding with `appointments` only)\n", FILE_APPEND);
}

// ✅ Begin transaction
$conn->begin_transaction();

try {
    // ✅ Update `appointments`
    $sql_appointments = "UPDATE appointments SET status = ? WHERE id = ?";
    $stmt_appointments = $conn->prepare($sql_appointments);
    if (!$stmt_appointments) {
        throw new Exception("Error preparing `appointments` update: " . $conn->error);
    }
    $stmt_appointments->bind_param("si", $status, $id);
    if (!$stmt_appointments->execute()) {
        throw new Exception("Error executing `appointments` update: " . $stmt_appointments->error);
    }
    if ($stmt_appointments->affected_rows === 0) {
        throw new Exception("No rows were updated in `appointments`.");
    }

    // ✅ Update `mobile_appointments`
    $sql_mobile = "UPDATE mobile_appointments SET status = ? WHERE id = ?";
    $stmt_mobile = $conn->prepare($sql_mobile);
    if (!$stmt_mobile) {
        throw new Exception("Error preparing `mobile_appointments` update: " . $conn->error);
    }
    $stmt_mobile->bind_param("si", $status, $id);
    if (!$stmt_mobile->execute()) {
        throw new Exception("Error executing `mobile_appointments` update: " . $stmt_mobile->error);
    }
    if ($stmt_mobile->affected_rows === 0) {
        throw new Exception("No rows were updated in `mobile_appointments`.");
    }

    // ✅ If both updates succeed, commit transaction
    $conn->commit();
    $response["success"] = true;
    $response["message"] = "Status updated successfully in both tables";

} catch (Exception $e) {
    $conn->rollback();
    file_put_contents("debug_update_status.txt", "❌ Error: " . $e->getMessage() . "\n", FILE_APPEND);
    $response["success"] = false;
    $response["message"] = "Failed to update status: " . $e->getMessage();
}

// ✅ Always return a response
echo json_encode($response);

// ✅ Close connections
$stmt_appointments->close();
$stmt_mobile->close();
$conn->close();
?>
