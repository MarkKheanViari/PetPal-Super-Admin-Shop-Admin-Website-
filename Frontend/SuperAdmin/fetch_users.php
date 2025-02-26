<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET");

// Include database connection
include $_SERVER['DOCUMENT_ROOT'] . "/backend/db.php";

$response = array();
$response["users"] = [];

$query = "SELECT id, username, 'Shop Owner' AS type, 'Active' AS status FROM shop_owners
          UNION ALL
          SELECT id, username, 'Customer' AS type, 'Active' AS status FROM mobile_users";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response["users"][] = $row;
    }
} else {
    $response["message"] = "No users found";
}

// Return JSON response
echo json_encode($response);
$conn->close();
?>
