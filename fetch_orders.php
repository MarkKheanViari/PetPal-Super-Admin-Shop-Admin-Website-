<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include "db.php"; // Database connection

$response = array();

// ✅ Fetch all orders with user details
$query = "SELECT o.id, 
                 o.mobile_user_id, 
                 o.total_price, 
                 o.payment_method, 
                 o.status, 
                 o.created_at, 
                 mu.username, 
                 mu.contact_number, 
                 mu.location
          FROM orders o
          JOIN mobile_users mu ON o.mobile_user_id = mu.id
          ORDER BY o.created_at DESC"; // Orders sorted by newest first

$result = $conn->query($query);

if ($result->num_rows > 0) {
    $orders = array();

    while ($row = $result->fetch_assoc()) {
        $order_id = $row["id"];
        error_log("Order ID: $order_id, Raw Total Price: " . $row["total_price"]); // Debug log

        // ✅ Convert created_at to a human-readable format
        $formatted_date = date("F j, Y, g:i A", strtotime($row["created_at"]));

        // ✅ Fetch order items for each order
        $items_query = "SELECT oi.product_id, 
                               p.name AS product_name, 
                               oi.quantity, 
                               oi.price 
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.id
                        WHERE oi.order_id = ?";
        
        $stmt = $conn->prepare($items_query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $items_result = $stmt->get_result();
        $items = array();
        
        while ($item = $items_result->fetch_assoc()) {
            $items[] = array(
                "product_id" => $item["product_id"],
                "product_name" => $item["product_name"],
                "quantity" => isset($item["quantity"]) ? (int)$item["quantity"] : 0,
                "price" => number_format((float)$item["price"], 2)
            );
        }
        $stmt->close();

        // ✅ Build the order response
        $orders[] = array(
            "id" => $row["id"],
            "mobile_user_id" => $row["mobile_user_id"],
            "username" => $row["username"],
            "contact_number" => $row["contact_number"] ?: "No Contact Info",
            "location" => $row["location"] ?: "No Address Provided",
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

// ✅ Return the JSON response
echo json_encode($response, JSON_PRETTY_PRINT);
$conn->close();
?>