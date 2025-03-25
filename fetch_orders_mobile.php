<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include "db.php"; // Database connection

$response = array();

// ✅ Ensure `mobile_user_id` is provided
if (!isset($_GET["mobile_user_id"])) {
    echo json_encode(["success" => false, "message" => "❌ Missing mobile_user_id"]);
    exit();
}

$mobile_user_id = intval($_GET["mobile_user_id"]);

// ✅ Fetch orders only for the logged-in user
$query = "SELECT o.id, 
                 o.total_price, 
                 o.payment_method, 
                 o.status, 
                 o.created_at
          FROM orders o
          WHERE o.mobile_user_id = ?  
          ORDER BY o.created_at DESC"; // Orders sorted by newest first

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $mobile_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $orders = array();

    while ($row = $result->fetch_assoc()) {
        $order_id = $row["id"];

        // ✅ Convert created_at to a human-readable format
        $formatted_date = date("F j, Y, g:i A", strtotime($row["created_at"]));

        // ✅ Fetch order items for each order (Includes Image & Description)
        $items_query = "SELECT oi.product_id, 
                               oi.name AS product_name, 
                               oi.image AS product_image, 
                               oi.description AS product_description, 
                               oi.quantity, 
                               oi.price 
                        FROM order_items oi
                        WHERE oi.order_id = ?";
        
        $stmt_items = $conn->prepare($items_query);
        $stmt_items->bind_param("i", $order_id);
        $stmt_items->execute();
        $items_result = $stmt_items->get_result();
        $items = array();
        
        // ✅ Define the base URL for images
        $base_url = "http://10.40.70.46/uploads/";

        while ($item = $items_result->fetch_assoc()) {
            // ✅ Fix Image URL: Remove spaces, encode properly
            $image_name = str_replace(" ", "%20", basename($item["product_image"]));
            $image_url = !empty($item["product_image"]) ? $base_url . $image_name : $base_url . "default.png";
        
            $items[] = array(
                "product_id" => $item["product_id"],
                "product_name" => $item["product_name"],
                "image" => $image_url, // ✅ Properly formatted URL
                "description" => $item["product_description"],
                "quantity" => (int)$item["quantity"], 
                "price" => number_format((float)$item["price"], 2)
            );
        }
        $stmt_items->close();

        // ✅ Build the order response
        $orders[] = array(
            "id" => $row["id"],
            "total_price" => number_format((float)$row["total_price"], 2), 
            "payment_method" => $row["payment_method"],
            "status" => $row["status"],
            "created_at" => $formatted_date, 
            "items" => $items 
        );
    }

    $response["success"] = true;
    $response["orders"] = $orders;
} else {
    $response["success"] = false;
    $response["message"] = "No orders found";
}

// ✅ Return the JSON response without escaped slashes
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$conn->close();
?>
