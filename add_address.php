<?php
include 'db.php'; 

$response = ["success" => false];

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['full_name'], $data['phone_number'], $data['region'], $data['postal_code'], $data['street'], $data['user_id'])) {
    $fullName = mysqli_real_escape_string($conn, $data['full_name']);
    $phoneNumber = mysqli_real_escape_string($conn, $data['phone_number']);
    $region = mysqli_real_escape_string($conn, $data['region']);
    $postalCode = mysqli_real_escape_string($conn, $data['postal_code']);
    $street = mysqli_real_escape_string($conn, $data['street']);
    $label = mysqli_real_escape_string($conn, $data['label']);
    $isDefault = isset($data['is_default']) && $data['is_default'] == "1" ? 1 : 0;
    $userId = intval($data['user_id']);

    $query = "INSERT INTO addresses (user_id, full_name, phone_number, region, postal_code, street, label, is_default) 
              VALUES ($userId, '$fullName', '$phoneNumber', '$region', '$postalCode', '$street', '$label', $isDefault)";
    
    if (mysqli_query($conn, $query)) {
        $response["success"] = true;
        $response["message"] = "Address added successfully.";
    } else {
        $response["message"] = "Database error: " . mysqli_error($conn);
    }
} else {
    $response["message"] = "Incomplete data.";
}

echo json_encode($response);
?>
