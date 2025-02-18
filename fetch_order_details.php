<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");

include "db.php"; 

if (!isset($_GET["order_id"])) {
    echo json_encode(["success" => false, "message" => "Missing order ID"]);
    exit;
}

$order_id = intval($_GET["order_id"]); // Prevent SQL injection

$query = "SELECT o.id AS order_id, 
                 o.total_price, 
                 o.payment_method, 
                 o.status, 
                 o.created_at, 
                 mu.username AS customer_name, 
                 mu.contact_number, 
                 mu.location
          FROM orders o
          JOIN mobile_users mu ON o.mobile_user_id = mu.id
          WHERE o.id = $order_id";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    $order = $result->fetch_assoc();

    // Fetch items for the order
    $items_query = "SELECT oi.product_id, 
                           p.name AS product_name, 
                           SUM(oi.quantity) AS total_quantity, 
                           oi.price 
                    FROM order_items oi
                    JOIN products p ON oi.product_id = p.id
                    WHERE oi.order_id = $order_id
                    GROUP BY oi.product_id, p.name, oi.price";

    $items_result = $conn->query($items_query);
    $items = array();

    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }

    $order["items"] = $items;
    $order["total_quantity"] = array_sum(array_column($items, "total_quantity"));

    echo json_encode(["success" => true, "order" => $order]);
} else {
    echo json_encode(["success" => false, "message" => "Order not found"]);
}

$conn->close();
?>
