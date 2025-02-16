<?php
include 'db.php'; // Use your existing db.php for the connection

$mobile_user_id = $_GET['mobile_user_id'];
$response = ["success" => false];

if (isset($mobile_user_id) && is_numeric($mobile_user_id)) {
    $query = "SELECT username, contact_number, location FROM mobile_users WHERE id = $mobile_user_id";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $response["success"] = true;
        $response["username"] = $row["username"];
        $response["contact_number"] = $row["contact_number"];
        $response["location"] = $row["location"];
    } else {
        $response["message"] = "No user found.";
    }
} else {
    $response["message"] = "Invalid user ID.";
}

echo json_encode($response);
?>
