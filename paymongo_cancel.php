<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

// No database operations needed since the order was not saved
error_log("🔍 paymongo_cancel.php: Payment canceled, no order saved");

// Redirect to deep link
$deep_link = "petpal://payment/cancel";
header("Location: $deep_link");
exit();
?>