<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include_once $_SERVER['DOCUMENT_ROOT'] . "/backend/db.php";

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->id)) {
    $id = $conn->real_escape_string($data->id);

    // Check if the user exists in the shop_owners table
    $checkQuery = "SELECT id FROM shop_owners WHERE id = '$id'";
    $result = $conn->query($checkQuery);

    if ($result->num_rows > 0) {
        // Delete the shop owner
        $deleteQuery = "DELETE FROM shop_owners WHERE id = '$id'";
        if ($conn->query($deleteQuery) === TRUE) {
            echo json_encode(["success" => true, "message" => "Shop Owner Deleted"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error: " . $conn->error]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Shop Owner not found"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "ID is required"]);
}

$conn->close();
?>