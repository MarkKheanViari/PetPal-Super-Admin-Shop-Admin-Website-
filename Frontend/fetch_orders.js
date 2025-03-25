// Global variable to store order items for the receipt
let currentOrderItems = [];

document.addEventListener("DOMContentLoaded", function () {
    fetchOrders();
});

function fetchOrders() {
    fetch("http://10.40.70.46/backend/fetch_orders.php")
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
                        <td>PHP ${order.total_price ? order.total_price : "0.00"}</td>
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
            return "#333"; // Fallback color (dark gray)
    }
}

function openUpdateStatus(orderId, currentStatus) {
    console.log("🔄 Opening Update Status Modal for Order:", orderId);

    document.getElementById("updateOrderId").value = orderId;
    document.getElementById("updateStatus").value = currentStatus;

    toggleModal("updateStatusModal", true);
}

function viewOrderDetails(orderId) {
    console.log("🔍 Fetching Order Details for Order ID:", orderId);

    fetch(
        `http://10.40.70.46/backend/fetch_order_details.php?order_id=${orderId}`
    )
        .then((response) => response.json())
        .then((data) => {
            console.log("✅ Order Data Received:", data);

            if (data.success) {
                // Populate customer details
                document.getElementById("customerName").value =
                    data.order.customer_name || "Unknown";
                document.getElementById("customerAddress").value =
                    data.order.location || "No Address Provided";
                document.getElementById("customerContact").value =
                    data.order.contact_number || "No Contact Info";
                document.getElementById("paymentMethod").value =
                    data.order.payment_method || "Not Specified";
                document.getElementById("orderAmount").value = `PHP ${parseFloat(
                    data.order.total_price
                ).toFixed(2)}`;
                document.getElementById("orderDate").value =
                    data.order.created_at || "Unknown";
                document.getElementById("orderStatus").value =
                    data.order.status || "Unknown";

                // Store items globally for the receipt
                currentOrderItems = data.order.items || [];

                // Populate order items
                const orderItemsList = document.getElementById("orderItemsList");
                orderItemsList.innerHTML = ""; // Clear existing items

                if (data.order.items && data.order.items.length > 0) {
                    data.order.items.forEach((item) => {
                        const tr = document.createElement("tr");
                        const total = (parseFloat(item.price) * parseInt(item.total_quantity)).toFixed(2);
                        tr.innerHTML = `
                            <td>${item.product_name || "Unknown Product"}</td>
                            <td>${item.total_quantity || 0}</td>
                            <td>PHP ${parseFloat(item.price || 0).toFixed(2)}</td>
                            <td>PHP ${total}</td>
                        `;
                        orderItemsList.appendChild(tr);
                    });
                } else {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `<td colspan="4">No items found for this order.</td>`;
                    orderItemsList.appendChild(tr);
                }

                toggleModal("orderModal", true);
            } else {
                console.error("❌ API Returned Error:", data.message);
                alert("❌ Error fetching order details: " + data.message);
            }
        })
        .catch((error) => console.error("❌ ERROR Fetching Order Details:", error));
}

function updateOrderStatus() {
    const orderId = document.getElementById("updateOrderId").value;
    const newStatus = document.getElementById("updateStatus").value;

    console.log(`🚀 Updating Order ${orderId} to Status: ${newStatus}`);

    fetch("http://10.40.70.46/backend/update_order_status.php", {
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

function downloadReceipt() {
    // Access jsPDF from the global window object (loaded via CDN)
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Retrieve receipt details from the modal fields
    const customerName = document.getElementById("customerName").value;
    const customerAddress = document.getElementById("customerAddress").value;
    const customerContact = document.getElementById("customerContact").value;
    const paymentMethod = document.getElementById("paymentMethod").value;
    const orderAmount = document.getElementById("orderAmount").value;
    const orderDate = document.getElementById("orderDate").value;

    // Get the seller's name from localStorage
    const sellerName = localStorage.getItem("shop_owner_username") || "PetPal Seller";

    // --- PDF Styling and Layout ---

    // Add a border around the page
    doc.setDrawColor(40, 40, 40); // Dark gray border
    doc.setLineWidth(0.5);
    doc.rect(10, 10, 190, 277); // Border around the A4 page (210x297 mm)

    // Set font for the document
    doc.setFont("helvetica", "normal");

    // Header: Shop Name and Title
    doc.setFontSize(20);
    doc.setTextColor(40, 40, 40); // Dark gray
    doc.text("Order Receipt", 105, 20, { align: "center" }); // Centered title
    doc.setFontSize(12);
    doc.setTextColor(100, 100, 100); // Lighter gray
    doc.text(`Seller: ${sellerName}`, 105, 30, { align: "center" });

    // Draw a line under the header
    doc.setLineWidth(0.5);
    doc.setDrawColor(200, 200, 200); // Light gray line
    doc.line(20, 35, 190, 35); // Horizontal line

    // Customer Information Section
    doc.setFontSize(14);
    doc.setTextColor(40, 40, 40);
    doc.text("Customer Information", 20, 45);
    doc.setFontSize(10);
    doc.setTextColor(60, 60, 60);
    doc.text(`Name: ${customerName}`, 20, 55);
    doc.text(`Address: ${customerAddress}`, 20, 62);
    doc.text(`Contact: ${customerContact}`, 20, 69);

    // Order Items Section
    doc.setFontSize(14);
    doc.setTextColor(40, 40, 40);
    doc.text("Order Items", 20, 85);

    // Define table headers and data
    const tableHeaders = ["Product Name", "Qty", "Price", "Total"];
    const tableData = [];

    if (currentOrderItems.length > 0) {
        currentOrderItems.forEach((item) => {
            const total = (parseFloat(item.price) * parseInt(item.total_quantity)).toFixed(2);
            tableData.push([
                item.product_name || "Unknown Product",
                item.total_quantity || 0,
                `PHP ${parseFloat(item.price || 0).toFixed(2)}`, // Use "PHP" instead of peso sign
                `PHP ${total}` // Use "PHP" instead of peso sign
            ]);
        });
    } else {
        tableData.push(["No items found for this order.", "", "", ""]);
    }

    // Draw the table using autoTable (jsPDF plugin)
    doc.autoTable({
        startY: 90,
        head: [tableHeaders],
        body: tableData,
        theme: "striped", // Professional striped theme
        headStyles: {
            fillColor: [255, 140, 0], // Orange (matches your button color #ff8c00)
            textColor: [255, 255, 255], // White text
            fontSize: 10,
        },
        bodyStyles: {
            fontSize: 9,
            textColor: [60, 60, 60], // Dark gray text
        },
        margin: { left: 20, right: 20 },
        styles: {
            lineColor: [200, 200, 200], // Light gray borders
            lineWidth: 0.1,
        },
    });

    // Get the final Y position after the table
    const finalY = doc.lastAutoTable.finalY || 90;

    // Order Information Section
    doc.setFontSize(14);
    doc.setTextColor(40, 40, 40);
    doc.text("Order Information", 20, finalY + 15);
    doc.setFontSize(10);
    doc.setTextColor(60, 60, 60);
    doc.text(`Payment Method: ${paymentMethod}`, 20, finalY + 25);
    doc.text(`Order Amount: ${orderAmount}`, 20, finalY + 32);
    doc.text(`Order Date: ${orderDate}`, 20, finalY + 39);

    // Adjust footer position if content is too long
    let footerY = finalY + 60;
    if (footerY > 250) {
        doc.addPage(); // Add a new page if content overflows
        footerY = 20;
    }

    // Footer: Thank You Message
    doc.setFontSize(10);
    doc.setTextColor(100, 100, 100);
    doc.text("Thank you for your purchase!", 105, footerY, { align: "center" });
    doc.setFontSize(8);
    doc.text("Generated on: " + new Date().toLocaleString(), 105, footerY + 10, { align: "center" });

    // Draw a line above the footer
    doc.setLineWidth(0.5);
    doc.setDrawColor(200, 200, 200);
    doc.line(20, footerY - 5, 190, footerY - 5);

    // Save the PDF
    doc.save("receipt.pdf");
}