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
                "quantity" => isset($item["quantity"]) ? (int)$item["quantity"] : 0, // ✅ Fix missing quantity
                "price" => number_format((float)$item["price"], 2) // ✅ Standardized price format
            );
        }
        $stmt->close();

        // Build order response
        $orders[] = array(
            "id" => $row["id"],
            "mobile_user_id" => $row["mobile_user_id"],
            "username" => $row["username"],
            "contact_number" => $row["contact_number"] ?: "No Contact Info", // ✅ Avoids undefined values
            "location" => $row["location"] ?: "No Address Provided", // ✅ Avoids undefined values
            "total_price" => number_format((float)$row["total_price"], 2), // ✅ Ensure price is formatted
            "payment_method" => $row["payment_method"],
            "status" => $row["status"],
            "created_at" => $row["created_at"],
            "items" => $items
        );
    }
    $response["success"] = true;
    $response["orders"] = $orders;
} else {
    $response["success"] = false;
    $response["message"] = "No orders found";
}

echo json_encode($response, JSON_PRETTY_PRINT); // ✅ Returns structured JSON response
$conn->close();
?>
