<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

include "db.php"; // Ensure database connection works

$response = array();

$query = "SELECT o.id, o.mobile_user_id, o.total_price, o.payment_method, o.status, o.created_at, 
                 mu.username, mu.contact_number, mu.location
          FROM orders o
          JOIN mobile_users mu ON o.mobile_user_id = mu.id
          ORDER BY o.created_at DESC";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    $orders = array();
    while ($row = $result->fetch_assoc()) {
        $order_id = $row["id"];

        // Fetch order items for each order
        $items_query = "SELECT oi.product_id, p.name AS product_name, oi.quantity, oi.price 
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.id
                        WHERE oi.order_id = $order_id";
        $items_result = $conn->query($items_query);
        $items = array();
        
        while ($item = $items_result->fetch_assoc()) {
            $items[] = $item;
        }

        // Build order response
        $row["items"] = $items;
        $orders[] = $row;
    }
    $response["success"] = true;
    $response["orders"] = $orders;
} else {
    $response["success"] = false;
    $response["message"] = "No orders found";
}

echo json_encode($response);
$conn->close();
?>
