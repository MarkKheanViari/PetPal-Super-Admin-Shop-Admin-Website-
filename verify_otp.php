<?php
include 'db.php'; // Include your database connection

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    if (!$data || !isset($data->email) || !isset($data->otp)) {
        throw new Exception('Missing required fields');
    }

    $email = $data->email;
    $otp = $data->otp;

    // Check if the OTP exists and is not expired
    $stmt = $conn->prepare("SELECT otp, expires_at FROM otps WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $otpRecord = $result->fetch_assoc();

    if (!$otpRecord) {
        throw new Exception('OTP not found');
    }

    // Check if the OTP has expired
    $currentTime = date('Y-m-d H:i:s');
    if ($currentTime > $otpRecord['expires_at']) {
        // Delete the expired OTP
        $stmt = $conn->prepare("DELETE FROM otps WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        throw new Exception('OTP has expired');
    }

    // Verify the OTP
    if ($otp !== $otpRecord['otp']) {
        throw new Exception('Invalid OTP');
    }

    // OTP is valid, delete it from the database
    $stmt = $conn->prepare("DELETE FROM otps WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'OTP verified']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>