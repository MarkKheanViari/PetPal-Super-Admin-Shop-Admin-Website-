<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    // Debugging log to see the received data
    file_put_contents("debug_log.txt", "Login Request: " . json_encode($data, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

    if (!$data || !isset($data->username) || !isset($data->password)) {
        throw new Exception('Missing required fields');
    }

    $username = $data->username;
    $password = $data->password;

    // Get user from database, including email
    $stmt = $conn->prepare("SELECT id, username, email, password FROM mobile_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        file_put_contents("debug_log.txt", "Username not found: $username\n", FILE_APPEND);
        throw new Exception('Invalid username or password');
    }

    // Log the password and hash for debugging
    file_put_contents("debug_log.txt", "Username: $username, Password Sent: $password, Hashed Password: " . $user['password'] . "\n", FILE_APPEND);

    if (!password_verify($password, $user['password'])) {
        file_put_contents("debug_log.txt", "Password verification failed for $username\n", FILE_APPEND);
        throw new Exception('Invalid username or password');
    }

    // Include email in the response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user_id' => $user['id'],
        'username' => $user['username'],
        'email' => $user['email'] // Add email to the response
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>