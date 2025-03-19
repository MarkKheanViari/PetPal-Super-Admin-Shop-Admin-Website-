<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    // Debugging log
    file_put_contents("debug_log.txt", "Reset Password Request: " . json_encode($data, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

    if (!$data || !isset($data->contact_number) || !isset($data->new_password)) {
        throw new Exception('Missing required fields');
    }

    $contact_number = $data->contact_number;
    $new_password = password_hash($data->new_password, PASSWORD_DEFAULT); // Hash the new password

    // Check if the contact number exists in the database
    $stmt = $conn->prepare("SELECT id FROM mobile_users WHERE contact_number = ?");
    $stmt->bind_param("s", $contact_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        file_put_contents("debug_log.txt", "Contact number not found: $contact_number\n", FILE_APPEND);
        throw new Exception('Contact number not registered');
    }

    // Update the password
    $stmt = $conn->prepare("UPDATE mobile_users SET password = ? WHERE contact_number = ?");
    $stmt->bind_param("ss", $new_password, $contact_number);
    if ($stmt->execute()) {
        file_put_contents("debug_log.txt", "Password reset successful for contact: $contact_number\n", FILE_APPEND);
        echo json_encode([
            'success' => true,
            'message' => 'Password reset successful'
        ]);
    } else {
        throw new Exception('Failed to reset password');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>