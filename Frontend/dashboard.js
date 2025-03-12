document.addEventListener("DOMContentLoaded", function () {
  console.log("📢 Dashboard.js Loaded!");

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
  fetch("http://192.168.1.65/backend/fetch_all_appointments.php")
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
// Display appointments as cards with no time and without the placeholder square
function displayAppointmentCards(appointments) {
  const container = document.getElementById("appointmentsContainer");
  if (!container) return;
  container.innerHTML = "";

  appointments.forEach((appointment) => {
    const card = document.createElement("div");
    card.classList.add("appointment-card");

    // Card layout: only details, date, and options button (no image placeholder)
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
  const ctx = document.getElementById("salesChart").getContext("2d");
  new Chart(ctx, {
    type: "bar",
    data: {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul"],
      datasets: [
        {
          label: "Monthly Sales (₱)",
          data: [1000, 1200, 900, 1400, 1100, 1300, 1500],
          backgroundColor: [
            "#FFB74D",
            "#FFA726",
            "#FB8C00",
            "#F57C00",
            "#EF6C00",
            "#E65100",
            "#D84315",
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
}

/* 
  ========== ORDERS & PRODUCTS CODE (unchanged) ==========
*/
function fetchOrders() {
  fetch("http://192.168.1.65/backend/fetch_orders.php")
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
  fetch("http://192.168.1.65/backend/fetch_product.php")
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
  return fetch("http://192.168.1.65/backend/fetch_orders.php")
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
    "http://192.168.1.65/backend/fetch_grooming_appointments.php"
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
    "http://192.168.1.65/backend/fetch_veterinary_appointments.php"
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

//
// Placeholder logout function (customize as needed)
//
function logout() {
  alert("Logging out...");
  // Add your logout logic here.
}
