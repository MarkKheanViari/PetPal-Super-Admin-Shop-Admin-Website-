
<?php
$host = "localhost";  // ✅ Or your actual database host
$user = "root";       // ✅ Database username
$pass = "";           // ✅ Database password
$dbname = "marketplace";  // ✅ Your database name

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database Connection Failed: " . $conn->connect_error]));
}

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