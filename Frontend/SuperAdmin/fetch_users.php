<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include_once $_SERVER['DOCUMENT_ROOT'] . "/backend/db.php";



$response = array();
$query = "SELECT id, username, 'Customer' AS role FROM mobile_users 
          UNION ALL 
          SELECT id, username, 'Shop Owner' FROM shop_owners 
          ORDER BY id DESC";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    $users = array();
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $response["users"] = $users;
} else {
    $response["users"] = [];
}

echo json_encode($response);
$conn->close();
?>
