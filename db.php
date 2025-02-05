<?php
$host = "localhost";  // ✅ Database host
$user = "root";       // ✅ Database username
$pass = "";           // ✅ Database password
$dbname = "marketplace";  // ✅ Your database name


// ✅ Enable error reporting for debugging (DO NOT USE IN PRODUCTION)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Keep this disabled in production

try {
    // ✅ Properly initialize the database connection
    $conn = new mysqli($host, $user, $pass, $dbname);

    // ✅ Check if connection fails
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // ✅ Set charset for proper encoding
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    // ✅ Log error for debugging
    error_log("Database connection error: " . $e->getMessage());

    // ✅ Return JSON error response
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]));
}
?>
