<?php
include 'db.php';

$mobile_user_id = $_GET['mobile_user_id'];
$response = ["success" => false, "addresses" => []];

if (isset($mobile_user_id) && is_numeric($mobile_user_id)) {
    // Fetch the primary address from mobile_users
    $query = "SELECT username AS full_name, contact_number AS phone_number, location AS street, 'N/A' AS region, 'Primary' AS label, 1 AS is_default FROM mobile_users WHERE id = $mobile_user_id";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $response["addresses"][] = $row;
    }

    // Fetch additional addresses from addresses table
    $query = "SELECT * FROM addresses WHERE user_id = $mobile_user_id";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $response["addresses"][] = $row;
        }
    }

    $response["success"] = true;
} else {
    $response["message"] = "Invalid user ID.";
}

echo json_encode($response);
?>
