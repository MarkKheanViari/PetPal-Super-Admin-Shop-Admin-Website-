document.addEventListener("DOMContentLoaded", function () {
  console.log("📢 Dashboard.js Loaded!");

  fetchSalesMetrics();

  // --- Chibi Helper & Tooltip ---
  const chibiHelper = document.getElementById("chibi-helper");
  const tooltip = document.getElementById("chibi-tooltip");
  const messages = [
    "Keep going! You're doing great! 🐾",
    "Did you know? Consistency is the key to success! 🔑",
    "Stay pawsitive! 🐶",
    "Remember to take breaks and recharge! ☕",
    "Every small step leads to big achievements! 🏆",
  ];
  if (chibiHelper && tooltip) {
    chibiHelper.addEventListener("click", function () {
      tooltip.innerText = messages[Math.floor(Math.random() * messages.length)];
      tooltip.style.display = "block";
      setTimeout(() => {
        tooltip.style.display = "none";
      }, 3000);
    });
  }

  // --- Render Sales Chart if the canvas exists ---
  const salesChartCanvas = document.getElementById("salesChart");
  if (salesChartCanvas) {
    renderSalesChart();
  }

  // --- Fetch ALL Appointments & Display as Cards (no time) ---
  fetchAllAppointments();

  // Listen for changes in the Service Filter (All/Grooming/Veterinary)
  const serviceFilter = document.getElementById("serviceFilter");
  if (serviceFilter) {
    serviceFilter.addEventListener("change", applyAppointmentsFilter);
  }

  // --- Other Dashboard Data ---
  fetchOrders();
  fetchProducts();

  // --- Load Notifications ---
  loadNotifications();

  // --- Tab Button Functionality (if present) ---
  const tabButtons = document.querySelectorAll(".tab-btn");
  tabButtons.forEach((btn) => {
    btn.addEventListener("click", function () {
      tabButtons.forEach((b) => b.classList.remove("active"));
      document.querySelectorAll(".tab-content").forEach((content) => {
        content.style.display = "none";
      });
      this.classList.add("active");
      const tab = this.getAttribute("data-tab");
      document.getElementById(tab + "-tab").style.display = "block";
    });
  });
});

/* 
  ========== APPOINTMENTS CODE ==========
  Fetch all appointments from your backend and display them as cards.
  A global array 'allAppointments' is used to store the fetched data.
  The user can filter by "All", "Grooming", or "Veterinary" using <select id="serviceFilter">
*/

let allAppointments = [];

// Fetch all appointments from backend
function fetchAllAppointments() {
  fetch("http://192.168.1.12/backend/fetch_all_appointments.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        allAppointments = data.appointments;
        applyAppointmentsFilter(); // Display immediately with default filter
      } else {
        console.warn("No appointments found.");
        const container = document.getElementById("appointmentsContainer");
        if (container) container.innerHTML = "<p>No appointments found.</p>";
      }
    })
    .catch((error) => console.error("Error fetching appointments:", error));
}

// Fetch sales metrics for the top cards
function fetchSalesMetrics() {
  fetch("http://192.168.1.12/backend/fetch_orders.php")
    .then((response) => response.json())
    .then((data) => {
      console.log("📊 Raw Orders Data for Sales Metrics:", data); // Debug log: Raw JSON response

      if (!data.success || !Array.isArray(data.orders)) {
        console.warn("No orders found for sales metrics.");
        // Fallback values for cards
        document.querySelector(".metrics .metric-card:nth-child(1) .metric-value").textContent = "₱0";
        document.querySelector(".metrics .metric-card:nth-child(1) .metric-label").textContent = "YTD";
        document.querySelector(".metrics .metric-card:nth-child(2) .metric-value").textContent = "N/A";
        document.querySelector(".metrics .metric-card:nth-child(2) .metric-label").textContent = "0 units";
        document.querySelector(".metrics .metric-card:nth-child(3) .metric-value").textContent = "N/A";
        document.querySelector(".metrics .metric-card:nth-child(3) .metric-label").textContent = "0 units";
        return;
      }

      // Step 1: Filter for Delivered Orders
      const deliveredOrders = data.orders.filter(order => order.status === "Delivered");
      console.log("📦 Delivered Orders:", deliveredOrders); // Debug log: Filtered orders

      // Step 2: Calculate Total Sales for Delivered Orders
      let totalSales = 0;
      deliveredOrders.forEach(order => {
        const rawPrice = order.total_price || "0.00"; // Use raw string
        const parsedPrice = parseFloat(rawPrice.replace(/[^0-9.-]+/g, '')) || 0; // Remove non-numeric characters and parse
        // Fallback: Calculate total from items if parsedPrice is suspiciously low
        const itemsTotal = order.items ? order.items.reduce((sum, item) => {
          const itemPrice = parseFloat(item.price) || 0;
          const itemQuantity = parseInt(item.quantity) || 0;
          return sum + (itemPrice * itemQuantity);
        }, 0) : 0;
        const finalPrice = parsedPrice > 10 ? parsedPrice : itemsTotal; // Use itemsTotal if parsedPrice is too low
        console.log(`Order ID: ${order.id}, Raw Total Price: ${rawPrice}, Parsed Price: ${parsedPrice}, Items Total: ${itemsTotal}, Final Price: ${finalPrice}`); // Debug log
        totalSales += finalPrice;
      });

      // Ensure totalSales is formatted with two decimal places
      totalSales = totalSales.toFixed(2);
      console.log("💰 Total Sales (before display):", totalSales); // Debug log: Final total

      // Update the first card (Total Sales) with peso sign and proper formatting
      document.querySelector(".metrics .metric-card:nth-child(1) .metric-value").textContent = `₱${parseFloat(totalSales).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
      document.querySelector(".metrics .metric-card:nth-child(1) .metric-label").textContent = "YTD";

      // Step 3: Calculate Top Selling Product (by quantity)
      const productQuantities = {};
      data.orders.forEach(order => {
        if (order.status === "Delivered" && Array.isArray(order.items)) {
          order.items.forEach(item => {
            const productId = item.product_id;
            const quantity = parseInt(item.quantity || 0);
            productQuantities[productId] = (productQuantities[productId] || 0) + quantity;
          });
        }
      });

      console.log("📈 Product Quantities:", productQuantities); // Debug log: Product quantities

      let topProductId = null;
      let topProductName = "N/A";
      let maxQuantity = 0;
      for (const [productId, quantity] of Object.entries(productQuantities)) {
        if (quantity > maxQuantity) {
          maxQuantity = quantity;
          topProductId = productId;
        }
      }

      if (topProductId) {
        let topProductFound = false;
        for (const order of data.orders) {
          if (topProductFound) break;
          if (Array.isArray(order.items)) {
            const topProductItem = order.items.find(item => item.product_id == topProductId);
            if (topProductItem) {
              topProductName = topProductItem.product_name || "Unknown Product";
              topProductFound = true;
            }
          }
        }
      }

      // Update the second card (Top Selling Product)
      document.querySelector(".metrics .metric-card:nth-child(2) .metric-value").textContent = topProductName;
      document.querySelector(".metrics .metric-card:nth-child(2) .metric-label").textContent = `${maxQuantity} units`;

      // Step 4: Calculate Least Sold Product (by quantity)
      let leastProductId = null;
      let leastProductName = "N/A";
      let minQuantity = Infinity;
      for (const [productId, quantity] of Object.entries(productQuantities)) {
        if (quantity < minQuantity) {
          minQuantity = quantity;
          leastProductId = productId;
        }
      }

      if (leastProductId !== null) {
        let leastProductFound = false;
        for (const order of data.orders) {
          if (leastProductFound) break;
          if (Array.isArray(order.items)) {
            const leastProductItem = order.items.find(item => item.product_id == leastProductId);
            if (leastProductItem) {
              leastProductName = leastProductItem.product_name || "Unknown Product";
              leastProductFound = true;
            }
          }
        }
      }

      // Update the third card (Least Sold Product)
      document.querySelector(".metrics .metric-card:nth-child(3) .metric-value").textContent = leastProductName;
      document.querySelector(".metrics .metric-card:nth-child(3) .metric-label").textContent = `${minQuantity} units`;

      // Fallback if no products are found
      if (!topProductName || !leastProductName) {
        document.querySelector(".metrics .metric-card:nth-child(2) .metric-value").textContent = "N/A";
        document.querySelector(".metrics .metric-card:nth-child(2) .metric-label").textContent = "0 units";
        document.querySelector(".metrics .metric-card:nth-child(3) .metric-value").textContent = "N/A";
        document.querySelector(".metrics .metric-card:nth-child(3) .metric-label").textContent = "0 units";
      }
    })
    .catch((error) => {
      console.error("Error fetching sales metrics:", error);
      // Fallback values for cards on error
      document.querySelector(".metrics .metric-card:nth-child(1) .metric-value").textContent = "₱0";
      document.querySelector(".metrics .metric-card:nth-child(1) .metric-label").textContent = "YTD";
      document.querySelector(".metrics .metric-card:nth-child(2) .metric-value").textContent = "N/A";
      document.querySelector(".metrics .metric-card:nth-child(2) .metric-label").textContent = "0 units";
      document.querySelector(".metrics .metric-card:nth-child(3) .metric-value").textContent = "N/A";
      document.querySelector(".metrics .metric-card:nth-child(3) .metric-label").textContent = "0 units";
    });
}

// Filter the appointments by service type (All, Grooming, Veterinary)
function applyAppointmentsFilter() {
  const serviceFilter = document.getElementById("serviceFilter");
  if (!serviceFilter) return;

  const filterValue = serviceFilter.value; // "All", "Grooming", or "Veterinary"
  let filteredAppointments = allAppointments;

  if (filterValue !== "All") {
    filteredAppointments = allAppointments.filter(
      (app) => app.service_type === filterValue
    );
  }

  displayAppointmentCards(filteredAppointments);
}

// Convert date to "20 January, 2025" (NO time)
function formatAppointmentDate(dateString) {
  const date = new Date(dateString);
  const day = date.getDate();
  const year = date.getFullYear();
  const monthName = date.toLocaleString("default", { month: "long" });
  return `${day} ${monthName}, ${year}`;
}

// Display appointments as cards with no time
function displayAppointmentCards(appointments) {
  const container = document.getElementById("appointmentsContainer");
  if (!container) return;
  container.innerHTML = "";

  appointments.forEach((appointment) => {
    const card = document.createElement("div");
    card.classList.add("appointment-card");

    card.innerHTML = `
      <div class="appointment-details">
        <h3>${appointment.service_name}</h3>
        <p>Customer: ${appointment.name}</p>
      </div>
      <div class="appointment-date">
        <p>${formatAppointmentDate(appointment.appointment_date)}</p>
      </div>
      <button class="more-options">⋮</button>
    `;

    container.appendChild(card);
  });

  if (appointments.length === 0) {
    container.innerHTML = "<p>No matching appointments found.</p>";
  }
}

/* 
  ========== SALES CHART CODE (unchanged) ==========
*/
function renderSalesChart() {
  fetch("http://192.168.1.12/backend/fetch_orders.php")
    .then((response) => response.json())
    .then((data) => {
      if (!data.success || !Array.isArray(data.orders)) {
        console.warn("No orders found for sales chart.");
        return;
      }

      // Step 1: Filter delivered orders and group by month
      const monthlySales = {};
      const currentYear = new Date().getFullYear(); // Only consider the current year for YTD

      data.orders.forEach(order => {
        if (order.status !== "Delivered") return; // Only count delivered orders

        const orderDate = new Date(order.created_at);
        if (orderDate.getFullYear() !== currentYear) return; // Skip orders not in the current year

        const month = orderDate.toLocaleString("default", { month: "short" }); // e.g., "Jan"

        // Calculate total price with fallback
        const rawPrice = order.total_price || "0.00";
        const parsedPrice = parseFloat(rawPrice.replace(/[^0-9.-]+/g, '')) || 0;
        const itemsTotal = order.items ? order.items.reduce((sum, item) => {
          const itemPrice = parseFloat(item.price) || 0;
          const itemQuantity = parseInt(item.quantity) || 0;
          return sum + (itemPrice * itemQuantity);
        }, 0) : 0;
        const finalPrice = parsedPrice > 10 ? parsedPrice : itemsTotal; // Use itemsTotal if parsedPrice is too low

        console.log(`[Chart] Order ID: ${order.id}, Month: ${month}, Raw Total Price: ${rawPrice}, Parsed Price: ${parsedPrice}, Items Total: ${itemsTotal}, Final Price: ${finalPrice}`); // Debug log

        monthlySales[month] = (monthlySales[month] || 0) + finalPrice;
      });

      console.log("📅 Monthly Sales:", monthlySales); // Debug log: Monthly totals

      // Step 2: Prepare chart data
      const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
      const salesData = months.map(month => monthlySales[month] || 0);

      // Step 3: Render the chart
      const ctx = document.getElementById("salesChart").getContext("2d");
      new Chart(ctx, {
        type: "bar",
        data: {
          labels: months.slice(0, new Date().getMonth() + 1), // Only show months up to the current month
          datasets: [
            {
              label: "Monthly Sales (₱)",
              data: salesData.slice(0, new Date().getMonth() + 1),
              backgroundColor: [
                "#FFB74D",
                "#FFA726",
                "#FB8C00",
                "#F57C00",
                "#EF6C00",
                "#E65100",
                "#D84315",
                "#FFB74D",
                "#FFA726",
                "#FB8C00",
                "#F57C00",
                "#EF6C00",
              ],
              borderColor: "black",
              borderWidth: 1,
            },
          ],
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function (value) {
                  return "₱" + value.toLocaleString();
                },
              },
            },
          },
        },
      });
    })
    .catch((error) => console.error("Error fetching orders for sales chart:", error));
}

/* 
  ========== ORDERS & PRODUCTS CODE (unchanged) ==========
*/
function fetchOrders() {
  fetch("http://192.168.1.12/backend/fetch_orders.php")
    .then((response) => response.json())
    .then((data) => {
      const ordersContainer = document.getElementById("dashboardOrdersContainer");
      if (!ordersContainer) {
        console.warn("dashboardOrdersContainer element not found. Skipping orders update.");
        return;
      }

      ordersContainer.innerHTML = ""; // Clear previous content

      if (data.success) {
        // Limit display to the first 3 orders (assuming the array is sorted by latest first)
        data.orders.slice(0, 3).forEach((order) => {
          // Calculate total quantity based on order items if available
          const totalQuantity = order.items
            ? order.items.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0)
            : 0;

          // Create a card element for each order
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
          `;

          ordersContainer.appendChild(orderCard);
        });
      } else {
        ordersContainer.innerHTML = `<p>No orders found.</p>`;
      }
    })
    .catch((error) => console.error("❌ Error fetching orders:", error));
}

function fetchProducts() {
  fetch("http://192.168.1.12/backend/fetch_product.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        let dashboardProductsContainer = document.getElementById(
          "dashboardProductsContainer"
        );
        if (dashboardProductsContainer) {
          dashboardProductsContainer.innerHTML = "";
          // Limit the display to the first 3 products
          data.products.slice(0, 3).forEach((product) => {
            let productElement = createProductElement(product);
            dashboardProductsContainer.appendChild(productElement);
          });
        }
      }
    })
    .catch((error) => console.error("❌ ERROR Fetching Products:", error));
}

function createOrderElement(order) {
  let orderElement = document.createElement("div");
  orderElement.classList.add("order-card");
  orderElement.innerHTML = `
    <div class="order-icon">📦</div>
    <p class="order-number">Order #${order.order_id}</p>
    <p class="customer-name">${order.customer_name}</p>
    <p class="order-total">Total: ₱${order.total_price}</p>
  `;
  return orderElement;
}

function createProductElement(product) {
  let productElement = document.createElement("div");
  productElement.classList.add("product-card");
  productElement.innerHTML = `
    <div class="product-icon">🐕</div>
    <h3>${product.name}</h3>
    <p class="product-desc">${product.description}</p>
    <p class="stock">In Stock: ${product.quantity}</p>
  `;
  return productElement;
}

/*
  ========== NOTIFICATIONS SECTION (unchanged) ==========
*/
let allNotifications = [];

function fetchOrderNotifications() {
  return fetch("http://192.168.1.12/backend/fetch_orders.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success && Array.isArray(data.orders)) {
        return data.orders.map((order) => ({
          type: "Order",
          message: `Order from ${order.username} - ₱${parseFloat(
            order.total_price
          ).toFixed(2)}`,
          id: order.id,
        }));
      }
      return [];
    })
    .catch((error) => {
      console.error("Error fetching orders:", error);
      return [];
    });
}

function fetchAppointmentNotifications() {
  const groomingPromise = fetch(
    "http://192.168.1.12/backend/fetch_grooming_appointments.php"
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success && Array.isArray(data.appointments)) {
        return data.appointments.map((app) => ({
          type: "Appointment",
          message: `Grooming appointment for ${app.name} on ${app.appointment_date}`,
          id: app.id,
        }));
      }
      return [];
    })
    .catch((error) => {
      console.error("Error fetching grooming appointments:", error);
      return [];
    });

  const vetPromise = fetch(
    "http://192.168.1.12/backend/fetch_veterinary_appointments.php"
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success && Array.isArray(data.appointments)) {
        return data.appointments.map((app) => ({
          type: "Appointment",
          message: `Veterinary appointment for ${app.name} on ${app.appointment_date}`,
          id: app.id,
        }));
      }
      return [];
    })
    .catch((error) => {
      console.error("Error fetching veterinary appointments:", error);
      return [];
    });

  return Promise.all([groomingPromise, vetPromise]).then((results) => [
    ...results[0],
    ...results[1],
  ]);
}

function loadNotifications() {
  Promise.all([
    fetchOrderNotifications(),
    fetchAppointmentNotifications(),
  ]).then((results) => {
    const notifications = [...results[0], ...results[1]];
    allNotifications = notifications;
    updateNotifBadge(notifications.length);
    updateNotifDropdown("all");
  });
}

function updateNotifBadge(count) {
  const badge = document.getElementById("notifCount");
  if (badge) {
    badge.textContent = count;
    badge.style.display = count > 0 ? "block" : "none";
  }
}

function updateNotifDropdown(filter) {
  const container = document.getElementById("notifItemsContainer");
  if (!container) return;
  container.innerHTML = "";
  let filteredNotifications = allNotifications;
  if (filter === "order") {
    filteredNotifications = allNotifications.filter(
      (n) => n.type.toLowerCase() === "order"
    );
  } else if (filter === "appointment") {
    filteredNotifications = allNotifications.filter(
      (n) => n.type.toLowerCase() === "appointment"
    );
  }
  if (filteredNotifications.length === 0) {
    container.innerHTML = `<div class="notif-item">No new notifications</div>`;
    return;
  }
  filteredNotifications.forEach((notif) => {
    const item = document.createElement("div");
    item.className = "notif-item";
    item.textContent = `[${notif.type}] ${notif.message}`;
    item.onclick = function (event) {
      event.stopPropagation();
      event.preventDefault();
      document.getElementById("notifDropdown").classList.remove("active");
      setTimeout(function () {
        if (notif.type.toLowerCase() === "order") {
          window.location.href = "orders.html";
        } else {
          window.location.href = "appointments.html";
        }
      }, 200);
    };
    container.appendChild(item);
  });
}

// Notification filter button click handlers
document.querySelectorAll(".notif-filter-btn").forEach((btn) => {
  btn.addEventListener("click", function (event) {
    event.preventDefault();
    event.stopPropagation();
    document
      .querySelectorAll(".notif-filter-btn")
      .forEach((b) => b.classList.remove("active"));
    this.classList.add("active");
    const filter = this.getAttribute("data-filter");
    updateNotifDropdown(filter);
  });
});

// Toggle notification dropdown visibility when clicking the bell
document
  .querySelector(".notification-bell")
  .addEventListener("click", function (event) {
    const dropdown = document.getElementById("notifDropdown");
    if (dropdown) dropdown.classList.toggle("active");
    event.stopPropagation();
  });

// Exit button to close the notification dropdown
document
  .getElementById("notifExitBtn")
  .addEventListener("click", function (event) {
    event.preventDefault();
    event.stopPropagation();
    document.getElementById("notifDropdown").classList.remove("active");
  });

// Hide dropdown if clicking outside
document.addEventListener("click", function () {
  const dropdown = document.getElementById("notifDropdown");
  if (dropdown && dropdown.classList.contains("active")) {
    dropdown.classList.remove("active");
  }
});

// Periodically refresh notifications (every 60 seconds)
setInterval(loadNotifications, 60000);

// Placeholder logout function (customize as needed)
function logout() {
  alert("Logging out...");
  // Add your logout logic here.
}