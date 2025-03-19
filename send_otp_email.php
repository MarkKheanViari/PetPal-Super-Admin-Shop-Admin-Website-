<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php'; // Path to Composer's autoloader

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

    // Log the email and OTP
    file_put_contents("gmail_debug_log.txt", "Email: $email, OTP: $otp\n", FILE_APPEND);

    $mail = new PHPMailer(true);
    // Enable verbose debug output
    $mail->SMTPDebug = 2; // 2 for detailed debug output
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'kheantastic@gmail.com'; // Replace with your Gmail address
    $mail->Password = 'huvn seuy vvbn vyms'; // Replace with your new App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('myemail@gmail.com', 'Your App Name');
    $mail->addAddress($email);

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset OTP';
    $mail->Body    = "Your OTP for password reset is: <b>$otp</b>";
    $mail->AltBody = "Your OTP for password reset is: $otp";

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
?>