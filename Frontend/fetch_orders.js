document.addEventListener("DOMContentLoaded", function () {
  fetchOrders();
});

function fetchOrders() {
  fetch("http://192.168.168.203/backend/fetch_orders.php")
    .then((response) => response.json())
    .then((data) => {
      const ordersContainer = document.getElementById("ordersContainer");

      // Check if the element exists before updating innerHTML
      if (!ordersContainer) {
        console.warn("ordersContainer element not found. Skipping orders update.");
        return;
      }

      ordersContainer.innerHTML = ""; // Clear previous data

      if (data.success) {
        data.orders.forEach((order) => {
          // Calculate total quantity if order.items exists, else default to 0
          const totalQuantity = order.items
            ? order.items.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0)
            : 0;

          const orderCard = document.createElement("div");
          orderCard.classList.add("order-card");

          orderCard.innerHTML = `
            <div class="order-info">
              <div class="order-image"></div>
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
        ordersContainer.innerHTML = `<p>No orders found.</p>`;
      }
    })
    .catch((error) => console.error("❌ Error fetching orders:", error));
}
