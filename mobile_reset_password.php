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

    // Check if required fields are present (new_password and either contact_number or email)
    if (!$data || !isset($data->new_password) || (!isset($data->contact_number) && !isset($data->email))) {
        throw new Exception('Missing required fields');
    }

    $new_password = password_hash($data->new_password, PASSWORD_DEFAULT); // Hash the new password

    // Determine which field to use for lookup
    $field = isset($data->contact_number) ? 'contact_number' : 'email';
    $value = $field == 'contact_number' ? $data->contact_number : $data->email;

    // Check if the user exists in the database
    $stmt = $conn->prepare("SELECT id FROM mobile_users WHERE $field = ?");
    $stmt->bind_param("s", $value);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($field == 'contact_number' && !$user) {
        // For contact number, require the user to be registered
        file_put_contents("debug_log.txt", "Contact number not found: $value\n", FILE_APPEND);
        throw new Exception('Contact number not registered');
    } elseif ($field == 'email') {
        if ($user) {
            // If email exists, update the password
            $stmt = $conn->prepare("UPDATE mobile_users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $new_password, $value);
            if (!$stmt->execute()) {
                throw new Exception('Failed to reset password');
            }
            file_put_contents("debug_log.txt", "Password reset successful for email: $value\n", FILE_APPEND);
            $response = ['success' => true, 'message' => 'Password reset successful', 'new_user' => false];
        } else {
            // If email doesn't exist, create a new user with default values
            $username = $value; // Use email as username
            $location = 'Unknown'; // Default location
            $age = 0; // Default age (you might want to adjust this)
            $contact_number = '00000000000'; // Default contact number (11 digits)

            $stmt = $conn->prepare("INSERT INTO mobile_users (username, email, password, location, age, contact_number) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssis", $username, $value, $new_password, $location, $age, $contact_number);
            if (!$stmt->execute()) {
                throw new Exception('Failed to create new user and reset password');
            }
            file_put_contents("debug_log.txt", "New user created and password set for email: $value\n", FILE_APPEND);
            $response = ['success' => true, 'message' => 'Password reset successful. New account created.', 'new_user' => true];
        }
    } else {
        // For contact number (if user exists), update the password
        $stmt = $conn->prepare("UPDATE mobile_users SET password = ? WHERE contact_number = ?");
        $stmt->bind_param("ss", $new_password, $value);
        if (!$stmt->execute()) {
            throw new Exception('Failed to reset password');
        }
        file_put_contents("debug_log.txt", "Password reset successful for contact_number: $value\n", FILE_APPEND);
        $response = ['success' => true, 'message' => 'Password reset successful', 'new_user' => false];
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>