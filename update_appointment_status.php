<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$response = ["success" => false];

// 1) Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// 2) Check if required fields are present
if (!isset($data["id"]) || !isset($data["status"])) {
    echo json_encode(["success" => false, "message" => "Missing required fields"]);
    exit();
}

$id = intval($data["id"]);
$status = $data["status"];

// 3) Check if the `id` exists in `appointments`
$foundInAppointments = false;
$sqlCheckAppointments = "SELECT id FROM appointments WHERE id = ?";
$stmtCheckAppointments = $conn->prepare($sqlCheckAppointments);
$stmtCheckAppointments->bind_param("i", $id);
$stmtCheckAppointments->execute();
$resultAppointments = $stmtCheckAppointments->get_result();
if ($resultAppointments->num_rows > 0) {
    $foundInAppointments = true;
}

// 4) Check if the `id` exists in `mobile_appointments`
$foundInMobile = false;
$sqlCheckMobile = "SELECT id FROM mobile_appointments WHERE id = ?";
$stmtCheckMobile = $conn->prepare($sqlCheckMobile);
$stmtCheckMobile->bind_param("i", $id);
$stmtCheckMobile->execute();
$resultMobile = $stmtCheckMobile->get_result();
if ($resultMobile->num_rows > 0) {
    $foundInMobile = true;
}

// 5) If not found in either table, stop here
if (!$foundInAppointments && !$foundInMobile) {
    echo json_encode(["success" => false, "message" => "ID not found in either table"]);
    exit();
}

// 6) Begin transaction
$conn->begin_transaction();
try {
    // Keep track of how many rows get updated
    $rowsUpdatedAppointments = 0;
    $rowsUpdatedMobile = 0;

    // 6a) Update `appointments` if record was found there
    if ($foundInAppointments) {
        $sqlAppointments = "UPDATE appointments SET status = ? WHERE id = ?";
        $stmtAppointments = $conn->prepare($sqlAppointments);
        if (!$stmtAppointments) {
            throw new Exception("Error preparing appointments update: " . $conn->error);
        }
        $stmtAppointments->bind_param("si", $status, $id);
        if (!$stmtAppointments->execute()) {
            throw new Exception("Error executing appointments update: " . $stmtAppointments->error);
        }
        $rowsUpdatedAppointments = $stmtAppointments->affected_rows;
        $stmtAppointments->close();
    }

    // 6b) Update `mobile_appointments` if record was found there
    if ($foundInMobile) {
        $sqlMobile = "UPDATE mobile_appointments SET status = ? WHERE id = ?";
        $stmtMobile = $conn->prepare($sqlMobile);
        if (!$stmtMobile) {
            throw new Exception("Error preparing mobile_appointments update: " . $conn->error);
        }
        $stmtMobile->bind_param("si", $status, $id);
        if (!$stmtMobile->execute()) {
            throw new Exception("Error executing mobile_appointments update: " . $stmtMobile->error);
        }
        $rowsUpdatedMobile = $stmtMobile->affected_rows;
        $stmtMobile->close();
    }

    // If neither table actually updated a row, throw an error (optional)
    if ($rowsUpdatedAppointments === 0 && $rowsUpdatedMobile === 0) {
        throw new Exception("No rows were updated in either table (status may already be the same).");
    }

    // 7) Commit transaction
    $conn->commit();
    
    // 8) Build a user-friendly message
    if ($foundInAppointments && $foundInMobile) {
        $response["message"] = "Status updated successfully in both tables.";
    } elseif ($foundInAppointments) {
        $response["message"] = "Status updated successfully in `appointments` only.";
    } else {
        $response["message"] = "Status updated successfully in `mobile_appointments` only.";
    }
    
    $response["success"] = true;

} catch (Exception $e) {
    // 9) Roll back on any error
    $conn->rollback();
    $response["success"] = false;
    $response["message"] = "Failed to update status: " . $e->getMessage();
}

// 10) Return JSON response
echo json_encode($response);

// 11) Close DB connection
$conn->close();
?>
