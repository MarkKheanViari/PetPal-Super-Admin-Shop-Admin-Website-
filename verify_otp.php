<?php
include 'db.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    if (!$data || !isset($data->contact_number) || !isset($data->otp) || !isset($data->new_password)) {
        throw new Exception('Missing required fields');
    }

    $contact_number = $data->contact_number;
    $otp = $data->otp;
    $new_password = password_hash($data->new_password, PASSWORD_DEFAULT);

    // Verify OTP
    $stmt = $conn->prepare("SELECT * FROM otps WHERE contact_number = ? AND otp = ? AND expiry > NOW()");
    $stmt->bind_param("ss", $contact_number, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Invalid or expired OTP');
    }

    // Update the password
    $stmt = $conn->prepare("UPDATE mobile_users SET password = ? WHERE contact_number = ?");
    $stmt->bind_param("ss", $new_password, $contact_number);
    if (!$stmt->execute()) {
        throw new Exception('Failed to update password');
    }

    // Delete the OTP after use
    $stmt = $conn->prepare("DELETE FROM otps WHERE contact_number = ?");
    $stmt->bind_param("s", $contact_number);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Password updated successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>