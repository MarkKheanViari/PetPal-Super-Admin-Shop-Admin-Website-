document.addEventListener("DOMContentLoaded", function () {
  fetchOrders();
});

function fetchOrders() {
  fetch("http://192.168.1.13/backend/fetch_orders.php")
    .then((response) => response.json())
    .then((data) => {
      console.log("Fetched Orders Data:", data); // Debug log to check the full JSON response
      const ordersContainer = document.getElementById("ordersContainer");

      if (!ordersContainer) {
        console.warn("ordersContainer element not found.");
        return;
      }

      ordersContainer.innerHTML = ""; // Clear existing data

      if (data.success) {
        const table = document.createElement("table");
        table.classList.add("orders-table");

        const thead = document.createElement("thead");
        thead.innerHTML = `
          <tr>
            <th>Customer</th>
            <th>Order Date</th>
            <th>Total Price</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        `;
        table.appendChild(thead);

        const tbody = document.createElement("tbody");

        data.orders.forEach((order) => {
          console.log("Order ID:", order.id, "Total Price:", order.total_price); // Debug log for each order
          const statusColor = getStatusColor(order.status);

          const tr = document.createElement("tr");
          tr.innerHTML = `
            <td>${order.username || "Unknown"}</td>
            <td>${order.created_at}</td>
            <td>₱${order.total_price ? order.total_price : "0.00"}</td>
            <td>
              <span class="order-status" style="color: ${statusColor}">
                ${order.status}
              </span>
            </td>
            <td>
              <button class="view-details-btn" onclick="viewOrderDetails(${order.id})">
                View Details
              </button>
              <button class="update-status-btn" onclick="openUpdateStatus(${order.id}, '${order.status}')">
                Update Status
              </button>
            </td>
          `;
          tbody.appendChild(tr);
        });

        table.appendChild(tbody);
        ordersContainer.appendChild(table);
      } else {
        ordersContainer.innerHTML = `<p>No orders found.</p>`;
      }
    })
    .catch((error) => console.error("❌ Error fetching orders:", error));
}

/**
 * Helper function to return a color code for each status
 */
function getStatusColor(status) {
  switch (status) {
    case "Pending":
      return "#F39C12"; // Orange
    case "To Ship":
      return "#2980B9"; // Blue
    case "Shipped":
      return "#E67E22"; // Darker orange
    case "Delivered":
      return "#27AE60"; // Green
    default:
      return "#333";    // Fallback color (dark gray)
  }
}

function openUpdateStatus(orderId, currentStatus) {
  console.log("🔄 Opening Update Status Modal for Order:", orderId);

  document.getElementById("updateOrderId").value = orderId;
  document.getElementById("updateStatus").value = currentStatus;

  toggleModal("updateStatusModal", true);
}

function viewOrderDetails(orderId) {
  console.log("🔍 Fetching Order Details for Order ID:", orderId); // ✅ Check if function runs

  fetch(`http://192.168.1.13/backend/fetch_order_details.php?order_id=${orderId}`)
    .then(response => response.json())
    .then(data => {
      console.log("✅ Order Data Received:", data); // ✅ See API response

      if (data.success) {
        document.getElementById("customerName").value = data.order.customer_name;
        document.getElementById("customerAddress").value = data.order.location || "No Address Provided";
        document.getElementById("customerContact").value = data.order.contact_number || "No Contact Info";
        document.getElementById("paymentMethod").value = data.order.payment_method;
        document.getElementById("orderAmount").value = `₱${parseFloat(data.order.total_price).toFixed(2)}`;
        
        // ✅ Display Order Date
        document.getElementById("orderDate").value = data.order.created_at;

        // ✅ Display Order Status
        document.getElementById("orderStatus").value = data.order.status || "Unknown";

        toggleModal("orderModal", true);
      } else {
        console.error("❌ API Returned Error:", data.message);
        alert("❌ Error fetching order details: " + data.message);
      }
    })
    .catch(error => console.error("❌ ERROR Fetching Order Details:", error));
}

function updateOrderStatus() {
  const orderId = document.getElementById("updateOrderId").value;
  const newStatus = document.getElementById("updateStatus").value;

  console.log(`🚀 Updating Order ${orderId} to Status: ${newStatus}`);

  fetch("http://192.168.1.13/backend/update_order_status.php", {
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