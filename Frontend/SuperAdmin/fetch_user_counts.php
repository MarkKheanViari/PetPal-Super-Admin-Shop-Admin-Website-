<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
include_once $_SERVER['DOCUMENT_ROOT'] . "/backend/db.php";



$response = array();

$query1 = "SELECT COUNT(*) AS customers FROM mobile_users";
$query2 = "SELECT COUNT(*) AS shopOwners FROM shop_owners";

$result1 = $conn->query($query1);
$result2 = $conn->query($query2);

if ($result1 && $result2) {
    $row1 = $result1->fetch_assoc();
    $row2 = $result2->fetch_assoc();
    $response["customers"] = $row1["customers"];
    $response["shopOwners"] = $row2["shopOwners"];
    echo json_encode($response);
} else {
    echo json_encode(["error" => "Query failed"]);
}

$conn->close();
?>
