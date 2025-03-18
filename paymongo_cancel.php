<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

include "db.php"; // Include your database connection

if (!isset($_GET['order_id'])) {
    echo json_encode(["success" => false, "message" => "❌ Missing order ID"]);
    exit();
}

$order_id = intval($_GET['order_id']);

// ✅ Keep order as "Pending"
echo json_encode(["success" => true, "message" => "⚠ Payment canceled. Order remains pending."]);
?>
