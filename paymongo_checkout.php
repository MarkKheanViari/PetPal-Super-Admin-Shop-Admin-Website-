<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

include "db.php";

$paymongo_secret_key = "sk_test_B8LiLXJfYa8wRc3mtX8MyPcP"; // Replace with your actual key
$encoded_key = base64_encode($paymongo_secret_key); // Correct Base64 encoding

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!isset($data['mobile_user_id'], $data['total_price'], $data['cart_items'])) {
    echo json_encode(["success" => false, "message" => "❌ Missing required fields!"]);
    exit();
}

$mobile_user_id = $data['mobile_user_id'];
$total_price = number_format($data['total_price'], 2, '.', '');

$cart_items = [];
foreach ($data['cart_items'] as $item) {
    $cart_items[] = [
        "name" => $item['name'],
        "quantity" => $item['quantity'],
        "amount" => intval($item['price'] * 100),  // Convert PHP amount to centavos
        "currency" => "PHP",
        "description" => $item['description']
    ];
}

// ✅ Create PayMongo Checkout Session
$checkout_data = [
    "data" => [
        "attributes" => [
            "line_items" => $cart_items,
            "payment_method_types" => ["gcash"],
            "description" => "Order from PetPal",
            "success_url" => "http://192.168.1.13/backend/paymongo_success.php?order_id=123",
            "cancel_url" => "http://192.168.1.13/backend/paymongo_cancel.php"
        ]
    ]
];

// ✅ Make PayMongo API request
$ch = curl_init("https://api.paymongo.com/v1/checkout_sessions");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Accept: application/json",
    "Authorization: Basic " . $encoded_key
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($checkout_data));

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

error_log("🔍 PayMongo Response: " . $response);
error_log("🔍 HTTP Code: " . $http_code);

if ($http_code == 200 || $http_code == 201) {
    $response_data = json_decode($response, true);

    if (isset($response_data["data"]["attributes"]["checkout_url"])) {
        $checkout_url = $response_data["data"]["attributes"]["checkout_url"]; // Extract correctly
        echo json_encode([
            "success" => true,
            "checkout_url" => $checkout_url
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "❌ PayMongo response is missing checkout_url",
            "api_response" => json_encode($response_data, JSON_PRETTY_PRINT)
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "❌ Failed to create PayMongo session",
        "http_code" => $http_code,
        "api_response" => json_encode($response, JSON_PRETTY_PRINT)
    ]);
}
?>
