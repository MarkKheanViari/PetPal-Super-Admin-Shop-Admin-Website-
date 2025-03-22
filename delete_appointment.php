<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$response = ["success" => false];

// 1) Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// 2) Check if required field "id" is present
if (!isset($data["id"])) {
    echo json_encode(["success" => false, "message" => "Missing required field: id"]);
    exit();
}

$id = intval($data["id"]);

// 3) Check if the id exists in the appointments table
$sqlCheckAppointments = "SELECT id FROM appointments WHERE id = ?";
$stmtCheckAppointments = $conn->prepare($sqlCheckAppointments);
$stmtCheckAppointments->bind_param("i", $id);
$stmtCheckAppointments->execute();
$resultAppointments = $stmtCheckAppointments->get_result();

if ($resultAppointments->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "ID not found in appointments table"]);
    exit();
}

// 4) Begin transaction
$conn->begin_transaction();
try {
    // Delete from appointments table only
    $sqlDeleteAppointments = "DELETE FROM appointments WHERE id = ?";
    $stmtDeleteAppointments = $conn->prepare($sqlDeleteAppointments);
    if (!$stmtDeleteAppointments) {
        throw new Exception("Error preparing delete from appointments: " . $conn->error);
    }
    $stmtDeleteAppointments->bind_param("i", $id);
    if (!$stmtDeleteAppointments->execute()) {
        throw new Exception("Error executing delete from appointments: " . $stmtDeleteAppointments->error);
    }
    $rowsDeletedAppointments = $stmtDeleteAppointments->affected_rows;
    $stmtDeleteAppointments->close();

    if ($rowsDeletedAppointments === 0) {
        throw new Exception("No rows were deleted. The record may have already been removed.");
    }

    // 5) Commit transaction
    $conn->commit();
    
    $response["success"] = true;
    $response["message"] = "Appointment deleted from appointments table successfully.";
} catch (Exception $e) {
    // 6) Roll back on any error
    $conn->rollback();
    $response["success"] = false;
    $response["message"] = "Failed to delete appointment: " . $e->getMessage();
}

// 7) Return JSON response
echo json_encode($response);

// 8) Close DB connection
$conn->close();
?>
