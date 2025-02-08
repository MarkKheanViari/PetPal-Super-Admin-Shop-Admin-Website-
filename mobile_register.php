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
    file_put_contents("debug_log.txt", json_encode($data, JSON_PRETTY_PRINT), FILE_APPEND);

    if (!$data || !isset($data->username) || !isset($data->email) || !isset($data->password) || !isset($data->location) || !isset($data->age) || !isset($data->contact_number)) {
        throw new Exception('Missing required fields');
    }

    $username = $data->username;
    $email = $data->email;
    $password = password_hash($data->password, PASSWORD_DEFAULT);
    $location = $data->location;
    $age = intval($data->age); // Ensure age is an integer
    $contact_number = $data->contact_number;

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM mobile_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Username already exists');
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM mobile_users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('Email already exists');
    }

    // Insert new user with additional fields
    $stmt = $conn->prepare("INSERT INTO mobile_users (username, email, password, location, age, contact_number) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $username, $email, $password, $location, $age, $contact_number);

    if (!$stmt->execute()) {
        throw new Exception('Database error: ' . $stmt->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
