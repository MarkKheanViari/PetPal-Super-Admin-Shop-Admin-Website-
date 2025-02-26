<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Include database connection
include_once $_SERVER['DOCUMENT_ROOT'] . "/backend/db.php";

// Get raw POST data
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["username"], $data["email"], $data["password"])) {
    $username = $conn->real_escape_string($data["username"]);
    $email = $conn->real_escape_string($data["email"]);
    $password = password_hash($data["password"], PASSWORD_DEFAULT); // Hash password

    // Check if username or email already exists
    $checkQuery = "SELECT id FROM shop_owners WHERE username = '$username' OR email = '$email'";
    $checkResult = $conn->query($checkQuery);
    if ($checkResult->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Username or email already exists."]);
        exit;
    }

    // Insert into shop_owners table
    $query = "INSERT INTO shop_owners (username, email, password) VALUES ('$username', '$email', '$password')";
    
    if ($conn->query($query) === TRUE) {
        echo json_encode(["success" => true, "message" => "Shop owner added successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Missing required fields."]);
}

$conn->close();
?>
