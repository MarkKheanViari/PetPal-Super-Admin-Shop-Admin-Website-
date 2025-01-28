<?php
// Database configuration
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'marketplace';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable error output in JSON response

try {
    $conn = new mysqli($host, $user, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to ensure proper encoding
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode([
        "error" => true,
        "message" => "Database connection failed"
    ]));
}
?>