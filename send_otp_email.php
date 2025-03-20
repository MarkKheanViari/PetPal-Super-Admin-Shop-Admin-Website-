<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Path to Composer's autoloader
include 'db.php'; // Include your database connection

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

ob_start(); // Start output buffering to capture debug output

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    if (!$data || !isset($data->email) || !isset($data->otp)) {
        throw new Exception('Missing required fields');
    }

    $email = $data->email;
    $otp = $data->otp;

    // Calculate expiration time (15 minutes from now)
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

    // Store the OTP in the database (replace if exists)
    $stmt = $conn->prepare("INSERT INTO otps (email, otp, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE otp = ?, expires_at = ?");
    $stmt->bind_param("sssss", $email, $otp, $expiresAt, $otp, $expiresAt);
    if (!$stmt->execute()) {
        throw new Exception('Failed to store OTP in database');
    }

    // Log the email and OTP
    file_put_contents("gmail_debug_log.txt", "Email: $email, OTP: $otp, Expires At: $expiresAt\n", FILE_APPEND);

    $mail = new PHPMailer(true);
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'kheantastic@gmail.com';
    $mail->Password = 'huvn seuy vvbn vyms'; // Replace with your new App Password (no spaces)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('PetPalSupport@gmail.com', 'PetPal Support Service');
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Code';
    $mail->Body    = "Your password reset code is: <b>$otp</b>. This code will expire in 15 minutes.";
    $mail->AltBody = "Your password reset code is: $otp. This code will expire in 15 minutes.";

    $mail->send();
    $debugOutput = ob_get_clean(); // Capture the debug output
    file_put_contents("gmail_debug_log.txt", "Gmail Debug Output:\n$debugOutput\n", FILE_APPEND);
    echo json_encode(['success' => true, 'message' => 'OTP sent to email']);
} catch (Exception $e) {
    $debugOutput = ob_get_clean(); // Capture the debug output in case of error
    file_put_contents("gmail_debug_log.txt", "Gmail Debug Error:\n$debugOutput\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => "Failed to send OTP: {$mail->ErrorInfo}"]);
}

$conn->close();
?>