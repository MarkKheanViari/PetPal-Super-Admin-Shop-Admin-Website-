<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include "db.php";

$paymongo_secret_key = "sk_test_B8LiLXJfYa8wRc3mtX8MyPcP"; // Replace with your PayMongo secret key
$encoded_key = base64_encode($paymongo_secret_key);

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

// Log the incoming request data
error_log("🔍 paymongo_appointment_checkout.php: Incoming request data: " . print_r($data, true));

// Check required fields
if (!isset($data['mobile_user_id'], $data['service_type'], $data['service_name'], $data['name'], $data['address'], 
           $data['phone_number'], $data['pet_name'], $data['pet_breed'], $data['appointment_date'], $data['payment_method'], $data['price'])) {
    error_log("❌ paymongo_appointment_checkout.php: Missing required fields");
    echo json_encode(["success" => false, "message" => "❌ Missing required fields!"]);
    exit();
}

$mobile_user_id = $data['mobile_user_id'];
$service_type = $data['service_type'];
$service_name = $data['service_name'];
$name = $data['name'];
$address = $data['address'];
$phone_number = $data['phone_number'];
$pet_name = $data['pet_name'];
$pet_breed = $data['pet_breed'];
$appointment_date = $data['appointment_date'];
$payment_method = $data['payment_method'];
$notes = $data['notes'] ?? "";
$price = number_format($data['price'], 2, '.', '');

// Log the price
error_log("🔍 paymongo_appointment_checkout.php: Price: $price");

// Prepare appointment data to pass to success URL
$appointment_data = [
    "mobile_user_id" => $mobile_user_id,
    "service_type" => $service_type,
    "service_name" => $service_name,
    "name" => $name,
    "address" => $address,
    "phone_number" => $phone_number,
    "pet_name" => $pet_name,
    "pet_breed" => $pet_breed,
    "appointment_date" => $appointment_date,
    "payment_method" => $payment_method,
    "notes" => $notes,
    "price" => $price
];

$encoded_appointment_data = base64_encode(json_encode($appointment_data));

// Prepare PayMongo Checkout Session
$checkout_data = [
    "data" => [
        "attributes" => [
            "line_items" => [
                [
                    "name" => "$service_type - $service_name",
                    "quantity" => 1,
                    "amount" => intval($price * 100), // PayMongo expects amount in cents
                    "currency" => "PHP",
                    "description" => "Appointment for $pet_name ($pet_breed) on $appointment_date"
                ]
            ],
            "payment_method_types" => ["gcash"],
            "description" => "PetPal Appointment",
            "success_url" => "http://192.168.1.65/backend/paymongo_appointment_success.php?appointment_data=$encoded_appointment_data",
            "cancel_url" => "http://192.168.1.65/backend/paymongo_appointment_cancel.php?appointment_data=$encoded_appointment_data"
        ]
    ]
];

// Log the checkout data
error_log("🔍 paymongo_appointment_checkout.php: Checkout data: " . json_encode($checkout_data));

// Make PayMongo API Request
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

// Log the response
error_log("🔍 paymongo_appointment_checkout.php: PayMongo Response: " . $response);
error_log("🔍 paymongo_appointment_checkout.php: HTTP Code: " . $http_code);

if ($http_code == 200 || $http_code == 201) {
    $response_data = json_decode($response, true);
    if (isset($response_data["data"]["attributes"]["checkout_url"])) {
        $checkout_url = $response_data["data"]["attributes"]["checkout_url"];
        error_log("✅ paymongo_appointment_checkout.php: Checkout URL: $checkout_url");
        echo json_encode([
            "success" => true,
            "checkout_url" => $checkout_url
        ]);
    } else {
        error_log("❌ paymongo_appointment_checkout.php: PayMongo response missing checkout_url");
        echo json_encode(["success" => false, "message" => "❌ PayMongo response missing checkout_url"]);
    }
} else {
    error_log("❌ paymongo_appointment_checkout.php: Failed to create PayMongo session, HTTP Code: $http_code");
    echo json_encode(["success" => false, "message" => "❌ Failed to create PayMongo session", "http_code" => $http_code]);
}

$conn->close();
?>