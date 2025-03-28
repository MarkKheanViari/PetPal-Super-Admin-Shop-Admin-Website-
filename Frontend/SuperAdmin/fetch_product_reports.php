<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once $_SERVER['DOCUMENT_ROOT'] . "/backend/db.php";

$response = array();

if (!isset($_GET['product_id'])) {
    $response["error"] = "Product ID is required";
    echo json_encode($response);
    exit;
}

$product_id = (int)$_GET['product_id'];

// Fetch reports for the given product
$query = "
    SELECT 
        r.id, 
        r.mobile_user_id, 
        r.reason, 
        r.created_at,
        u.username AS reporter
    FROM reports r
    LEFT JOIN mobile_users u ON r.mobile_user_id = u.id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

$reports = array();
while ($row = $result->fetch_assoc()) {
    $reports[] = [
        "id" => $row["id"],
        "reporter" => $row["reporter"] ?: "Unknown",
        "reason" => $row["reason"],
        "created_at" => $row["created_at"]
    ];
}

$response["reports"] = $reports;
$stmt->close();
$conn->close();

echo json_encode($response);
?>