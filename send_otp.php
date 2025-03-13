<?php
include 'db.php';
require_once 'vendor/autoload.php';

use Twilio\Rest\Client;

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $json = file_get_contents('php://input');
    if (empty($json)) {
        throw new Exception('No input data received');
    }
    $data = json_decode($json);

    if (!$data || !isset($data->contact_number)) {
        throw new Exception('Contact number is required');
    }

    $contact_number = trim($data->contact_number);

    if (substr($contact_number, 0, 2) === '09' && strlen($contact_number) === 11) {
        $contact_number = '+63' . substr($contact_number, 2);
    } elseif (substr($contact_number, 0, 1) !== '+' || strlen($contact_number) < 10 || strlen($contact_number) > 15) {
        throw new Exception('Invalid contact number format');
    }

    error_log("Formatted contact_number: " . $contact_number);
    error_log("Connected to database: " . $dbname);

    $result = $conn->query("SHOW TABLES LIKE 'mobile_users'");
    error_log("Table mobile_users exists: " . ($result->num_rows > 0 ? "Yes" : "No"));

    $result = $conn->query("SELECT contact_number FROM mobile_users LIMIT 1");
    if ($result) {
        error_log("First contact_number in table: " . ($result->fetch_assoc()['contact_number'] ?? 'None'));
    } else {
        error_log("Query failed: " . $conn->error);
    }

    $stmt = $conn->prepare("SELECT id FROM mobile_users WHERE contact_number = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $contact_number);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    error_log("Query result rows: " . $result->num_rows);

    if ($result->num_rows === 0) {
        throw new Exception('Contact number not found');
    }

    $otp = str_pad(random_int(0, 999999), 6, "0", STR_PAD_LEFT);
    $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $stmt = $conn->prepare("INSERT INTO otps (contact_number, otp, expiry) VALUES (?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("sss", $contact_number, $otp, $expiry);
    if (!$stmt->execute()) {
        throw new Exception("Failed to store OTP: " . $stmt->error);
    }

    $account_sid = "AC3c220916700e2b965cc6370150ebff1e";
    $auth_token = "7bc53fe182735c1f8bb5c34b246946b8";
    $messaging_service_sid = "MG6cd9b32ab7d029a5c2fa26fd701bed66";

    $client = new Client($account_sid, $auth_token);
    $message = $client->messages->create(
        $contact_number,
        [
            'messagingServiceSid' => $messaging_service_sid,
            'body' => "Your OTP for password reset is: $otp. It is valid for 10 minutes."
        ]
    );

    echo json_encode([
        'success' => true,
        'message' => 'OTP sent successfully'
    ]);

} catch (Twilio\Exceptions\RestException $e) {
    http_response_code(400);
    error_log("Twilio Error: [HTTP {$e->getStatusCode()}] " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => "[HTTP {$e->getStatusCode()}] Unable to send OTP: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(400);
    error_log("General Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>