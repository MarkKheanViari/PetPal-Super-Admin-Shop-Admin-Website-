<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db.php';

if (!$conn) {
    die("DB connection failed: " . mysqli_connect_error());
}

$query = "SELECT 1";
$result = mysqli_query($conn, $query);

if ($result) {
    echo "Database query successful!";
} else {
    echo "Database query error: " . mysqli_error($conn);
}
