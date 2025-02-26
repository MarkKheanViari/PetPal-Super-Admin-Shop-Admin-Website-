<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include 'db.php';

$response = array();

$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ?? "";
$password = $data["password"] ?? "";

// SuperAdmin Credentials
$superadmin_email = "admin@example.com";
$superadmin_password = "admin123"; // Change this and hash it for security

if ($email === $superadmin_email) {
    // Authenticate SuperAdmin
    if ($password === $superadmin_password) {
        $response["success"] = true;
        $response["role"] = "SuperAdmin";
        $response["message"] = "Login successful as SuperAdmin";
    } else {
        $response["success"] = false;
        $response["message"] = "Invalid SuperAdmin credentials";
    }
} else {
    // Authenticate Shop Owner
    $stmt = $conn->prepare("SELECT id, username, email, password FROM shop_owners WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row["password"])) {
            $response["success"] = true;
            $response["role"] = "ShopOwner";
            $response["user"] = [
                "id" => $row["id"],
                "username" => $row["username"],
                "email" => $row["email"]
            ];
            $response["message"] = "Login successful as Shop Owner";
        } else {
            $response["success"] = false;
            $response["message"] = "Incorrect password";
        }
    } else {
        $response["success"] = false;
        $response["message"] = "User not found";
    }
}

echo json_encode($response);
$conn->close();
?>
