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
$password = $data["password"] ?? "";
$role = $data["role"] ?? "";

// Validate input
if (empty($email) || empty($password) || empty($role)) {
    $response["success"] = false;
    $response["message"] = "Email, password, and role are required";
    echo json_encode($response);
    exit();
}

// SuperAdmin Login
if ($role === "superadmin") {
    $stmt = $conn->prepare("SELECT password FROM super_admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row["password"])) {
            $response["success"] = true;
            $response["redirect"] = "http://192.168.137.14/backend/Frontend/SuperAdmin/superadmin.html";
            $response["token"] = bin2hex(random_bytes(16));
        } else {
            $response["success"] = false;
            $response["message"] = "Incorrect password";
        }
    } else {
        $response["success"] = false;
        $response["message"] = "SuperAdmin not found";
    }
} else {
    // Fetch `id` as `shop_owner_id`
    $stmt = $conn->prepare("SELECT id, username, password FROM shop_owners WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        file_put_contents("debug.log", "🔍 Login Attempt: Email: $email | Retrieved ID: " . $row["id"] . "\n", FILE_APPEND);

        if (password_verify($password, $row["password"])) {
            $response["success"] = true;
            $response["shop_owner_id"] = $row["id"];
            $response["username"] = $row["username"];
            $response["redirect"] = "dashboard.html";
            $response["token"] = bin2hex(random_bytes(16));
        } else {
            $response["success"] = false;
            $response["message"] = "Incorrect password";
        }
    } else {
        $response["success"] = false;
        $response["message"] = "User not found";
    }
}

// Send JSON response
echo json_encode($response);
$conn->close();
?>