document.addEventListener("DOMContentLoaded", function () {
    fetchOrders();
});

document.addEventListener("DOMContentLoaded", function () {
    fetch("http://192.168.137.239/backend/fetch_orders.php")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const ordersContainer = document.getElementById("ordersContainer");
                ordersContainer.innerHTML = ""; // Clear previous data

                data.orders.forEach(order => {
                    const totalQuantity = order.items ? order.items.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0) : 0;
                    
                    const orderCard = document.createElement("div");
                    orderCard.classList.add("order-card");

                    orderCard.innerHTML = `
                        <div class="order-info">
                            <div class="order-image"></div> <!-- Placeholder for image -->
                            <div class="order-details">
                                <strong>Customer: ${order.username || "Unknown"}</strong>
                                <p><strong>Total Price:</strong> ₱${parseFloat(order.total_price).toFixed(2)}</p>
                                <p><strong>Quantity:</strong> x${totalQuantity}</p>
                            </div>
                        </div>
                        <div class="order-actions">
                            <button class="view-details-btn" onclick="viewOrderDetails(${order.id})">View Details</button>
                        </div>
                    `;

                    ordersContainer.appendChild(orderCard);
                });
            } else {
                document.getElementById("ordersContainer").innerHTML = `<p>No orders found.</p>`;
            }
        })
        .catch(error => console.error("❌ Error fetching orders:", error));
});




function updateOrderStatus(orderId, status) {
    fetch("http://192.168.137.239/backend/update_order_status.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ order_id: orderId, status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Order ${status}!`);
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Error updating order status:", error));
}
