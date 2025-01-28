<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    if (!$data || !isset($data->username) || !isset($data->password)) {
        throw new Exception('Missing required fields');
    }

    $username = $data->username;
    $password = $data->password;

    // Get user from database
    $stmt = $conn->prepare("SELECT id, username, password FROM mobile_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || !password_verify($password, $user['password'])) {
        throw new Exception('Invalid username or password');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user_id' => $user['id'],
        'username' => $user['username']
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

