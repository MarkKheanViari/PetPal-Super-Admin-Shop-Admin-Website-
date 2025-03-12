<?php
include 'db.php';
require_once 'vendor/autoload.php'; // Include Composer autoloader

use Twilio\Rest\Client;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    if (!$data || !isset($data->contact_number)) {
        throw new Exception('Contact number is required');
    }

    $contact_number = $data->contact_number;

    // Check if contact number exists in the database
    $stmt = $conn->prepare("SELECT id FROM mobile_users WHERE contact_number = ?");
    $stmt->bind_param("s", $contact_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Contact number not found');
    }

    // Generate a 6-digit OTP
    $otp = str_pad(rand(0, 999999), 6, "0", STR_PAD_LEFT);
    $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes')); // OTP valid for 10 minutes

    // Store OTP in the otps table
    $stmt = $conn->prepare("INSERT INTO otps (contact_number, otp, expiry) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $contact_number, $otp, $expiry);
    if (!$stmt->execute()) {
        throw new Exception('Failed to store OTP');
    }

    // Send OTP via Twilio
    $account_sid = "AC3c220916700e2b965cc6370150ebff1e";
    $auth_token = "7bc53fe182735c1f8bb5c34b246946b8";
    $twilio_number = "09084538768"; // E.g., "+1234567890"

    $client = new Client($account_sid, $auth_token);
    $message = $client->messages->create(
        $contact_number,
        [
            'from' => $twilio_number,
            'body' => "Your OTP for password reset is: $otp. It is valid for 10 minutes."
        ]
    );

    echo json_encode([
        'success' => true,
        'message' => 'OTP sent successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>