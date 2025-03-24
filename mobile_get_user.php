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
    file_put_contents("debug_log.txt", "Get User Request: " . json_encode($data, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

    if (!$data || !isset($data->user_id)) {
        throw new Exception('Missing user_id');
    }

    $user_id = $data->user_id;

    // Get user from database
    $stmt = $conn->prepare("SELECT id, username, email, created_at, location, age, contact_number FROM mobile_users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        file_put_contents("debug_log.txt", "User not found: $user_id\n", FILE_APPEND);
        throw new Exception('User not found');
    }

    echo json_encode([
        'success' => true,
        'message' => 'User data retrieved successfully',
        'user' => $user
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>