document.addEventListener("DOMContentLoaded", function () {
  fetchOrders();
});

function fetchOrders() {
  fetch("http://192.168.1.65/backend/fetch_orders.php")
    .then((response) => response.json())
    .then((data) => {
      const ordersContainer = document.getElementById("ordersContainer");

      if (!ordersContainer) {
        console.warn("ordersContainer element not found.");
        return;
      }

      ordersContainer.innerHTML = ""; // Clear existing data

      if (data.success) {
        data.orders.forEach((order) => {
          const orderCard = document.createElement("div");
          orderCard.classList.add("order-card");

          orderCard.innerHTML = `
            <div class="order-info">
              <strong>Customer: ${order.username || "Unknown"}</strong>
              <p><strong>Order Date:</strong> ${order.created_at}</p>
              <p><strong>Total Price:</strong> ₱${parseFloat(order.total_price).toFixed(2)}</p>
              <p><strong>Status:</strong> <span class="order-status">${order.status}</span></p>
            </div>
            <div class="order-actions">
              <button class="view-details-btn" onclick="viewOrderDetails(${order.id})">View Details</button>
              <button class="update-status-btn" onclick="openUpdateStatus(${order.id}, '${order.status}')">Update Status</button>
            </div>
          `;

          ordersContainer.appendChild(orderCard);
        });
      } else {
        ordersContainer.innerHTML = `<p>No orders found.</p>`;
      }
    })
    .catch((error) => console.error("❌ Error fetching orders:", error));
}


function openUpdateStatus(orderId, currentStatus) {
  console.log("🔄 Opening Update Status Modal for Order:", orderId);

  document.getElementById("updateOrderId").value = orderId;
  document.getElementById("updateStatus").value = currentStatus;

  toggleModal("updateStatusModal", true);
}

function updateOrderStatus() {
  const orderId = document.getElementById("updateOrderId").value;
  const newStatus = document.getElementById("updateStatus").value;

  console.log(`🚀 Updating Order ${orderId} to Status: ${newStatus}`);

  fetch("http://192.168.1.65/backend/update_order_status.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      order_id: orderId,
      status: newStatus,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("✅ Order status updated successfully!");
        fetchOrders(); // Refresh orders list
        toggleModal("updateStatusModal", false);
      } else {
        alert("❌ Error updating order status: " + data.message);
      }
    })
    .catch((error) => console.error("❌ ERROR Updating Order Status:", error));
}



