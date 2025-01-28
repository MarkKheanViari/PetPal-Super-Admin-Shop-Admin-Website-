<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    if (!$data || !isset($data->username) || !isset($data->password)) {
        throw new Exception('Missing required fields');
    }

    $username = $data->username;
    $password = $data->password;

    // Get user from database
    $stmt = $conn->prepare("SELECT id, username, password FROM shop_owners WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($password, $user['password'])) {
        throw new Exception('Invalid username or password');
    }

    // Generate a simple token
    $token = bin2hex(random_bytes(32));

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'token' => $token,
        'username' => $user['username'],
        'user_id' => $user['id']  // Include user ID in response
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

