<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php';

$response = array();

// Check if request is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response["success"] = false;
    $response["message"] = "Invalid Request";
    echo json_encode($response);
    exit();
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ?? "";
$new_password = $data["new_password"] ?? "";
$role = $data["role"] ?? "";

// Validate input
if (empty($email) || empty($new_password) || empty($role)) {
    $response["success"] = false;
    $response["message"] = "Email, new password, and role are required";
    echo json_encode($response);
    exit();
}

// Hash the new password (using the same method as in authenticate.php)
$new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

// Update the password in the appropriate table
if ($role === "shop_owner") {
    $stmt = $conn->prepare("UPDATE shop_owners SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $new_password_hashed, $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $response["success"] = true;
        $response["message"] = "Password updated successfully";
    } else {
        $response["success"] = false;
        $response["message"] = "No account found with that email";
    }
} else {
    $response["success"] = false;
    $response["message"] = "Invalid role";
}

echo json_encode($response);
$conn->close();
?>