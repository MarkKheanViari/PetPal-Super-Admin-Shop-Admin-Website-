<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include 'db.php';

$response = array();

// Check if request is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $response["error"] = "Invalid Request";
    echo json_encode($response);
    exit();
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);
$email = $data["email"] ?? "";
$password = $data["password"] ?? "";
$role = $data["role"] ?? "";

// SuperAdmin Login
if ($role === "superadmin") {
    $stmt = $conn->prepare("SELECT password FROM super_admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Debugging - Log the stored password
        error_log("Stored Password: " . $row["password"]);
        error_log("Entered Password: " . $password);

        if (password_verify($password, $row["password"])) {
            $response["success"] = true;
            $response["redirect"] = "http://localhost/backend/Frontend/SuperAdmin/superadmin.html";
        } else {
            $response["success"] = false;
            $response["message"] = "Incorrect password";
        }
    } else {
        $response["success"] = false;
        $response["message"] = "SuperAdmin not found";
    }
}
else {
    // Shop Owner Login
    $stmt = $conn->prepare("SELECT password FROM shop_owners WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row["password"])) {
            $response["success"] = true;
            $response["redirect"] = "dashboard.html"; // Redirect to Shop Owner Dashboard
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
