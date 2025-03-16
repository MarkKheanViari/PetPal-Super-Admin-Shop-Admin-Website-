<?php
include 'db.php'; // Use your existing db.php for the connection

header('Content-Type: application/json');

$response = ["success" => false];

// Debug: Log the raw GET parameter
error_log("GET mobile_user_id: " . print_r($_GET['mobile_user_id'], true));

// Get the mobile_user_id parameter
$mobile_user_id = isset($_GET['mobile_user_id']) ? $_GET['mobile_user_id'] : null;

// Debug: Log the value of $mobile_user_id
error_log("mobile_user_id: " . ($mobile_user_id ?? 'null'));

// Check if mobile_user_id is set and numeric
if (!empty($mobile_user_id) && is_numeric($mobile_user_id)) {
    $mobile_user_id = (int) $mobile_user_id; // Cast to integer for safety
    error_log("mobile_user_id is numeric: $mobile_user_id");

    $query = "SELECT username, contact_number, location FROM mobile_users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $mobile_user_id);
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $response["success"] = true;
                $response["username"] = $row["username"];
                $response["contact_number"] = $row["contact_number"];
                $response["location"] = $row["location"];
            } else {
                $response["message"] = "No user found.";
            }
        } else {
            $response["message"] = "Query execution failed: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $response["message"] = "Failed to prepare statement: " . mysqli_error($conn);
    }
} else {
    $response["message"] = "Invalid user ID. Received: " . ($mobile_user_id ?? 'null');
}

mysqli_close($conn);
echo json_encode($response);
?>