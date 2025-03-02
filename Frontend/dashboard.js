document.addEventListener("DOMContentLoaded", function () {
  console.log("📢 Dashboard.js Loaded!");

  const chibiHelper = document.getElementById("chibi-helper");
  const tooltip = document.getElementById("chibi-tooltip");

  const messages = [
    "Keep going! You're doing great! 🐾",
    "Did you know? Consistency is the key to success! 🔑",
    "Stay pawsitive! 🐶",
    "Remember to take breaks and recharge! ☕",
    "Every small step leads to big achievements! 🏆"
  ];

chibiHelper.addEventListener("click", function () {
    // Show random tip
    tooltip.innerText = messages[Math.floor(Math.random() * messages.length)];
    tooltip.style.display = "block";

    // Hide after 3 seconds
    setTimeout(() => {
        tooltip.style.display = "none";
    }, 3000);
  });

  // Ensure the chart renders only if the element exists
  const salesChartCanvas = document.getElementById("salesChart");
  if (salesChartCanvas) {
    renderSalesChart();
  }

  // Ensure elements exist before proceeding
  const vetContainer = document.getElementById("dashboardVetAppointments");
  const groomingContainer = document.getElementById(
    "dashboardGroomingSchedule"
  );

  if (vetContainer && groomingContainer) {
    fetchVetAppointments();
    fetchGroomingAppointments();
  }

  fetchOrders();
  fetchProducts();
});

function fetchVetAppointments() {
  fetch("http://192.168.1.3/backend/fetch_veterinary_appointments.php")
    .then((response) => {
      if (!response.ok) {
        throw new Error(`HTTP Error! Status: ${response.status}`);
      }
      return response.json();
    })
    .then((data) => {
      if (data.success) {
        let vetContainer = document.getElementById("dashboardVetAppointments");
        vetContainer.innerHTML = "";

        data.appointments.slice(0, 3).forEach((appointment) => {
          let appointmentElement = createAppointmentElement(appointment);
          vetContainer.appendChild(appointmentElement);
        });
      } else {
        console.warn("No veterinary appointments found.");
      }
    })
    .catch((error) =>
      console.error("❌ ERROR Fetching Veterinary Appointments:", error)
    );
}

function fetchGroomingAppointments() {
  fetch("http://192.168.1.3/backend/fetch_grooming_appointments.php")
    .then((response) => response.json())
    .then((data) => {
      console.log("📢 Grooming Appointments Data:", data); // Debugging
      if (data.success) {
        let groomingContainer = document.getElementById(
          "dashboardGroomingSchedule"
        );
        groomingContainer.innerHTML = "";

        data.appointments.slice(0, 3).forEach((appointment) => {
          let appointmentElement = createAppointmentElement(appointment);
          groomingContainer.appendChild(appointmentElement);
        });
      } else {
        console.warn("No grooming appointments found.");
      }
    })
    .catch((error) =>
      console.error("❌ ERROR Fetching Grooming Appointments:", error)
    );
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    month: "2-digit",
    day: "2-digit",
    year: "numeric",
  });
}

function createAppointmentElement(appointment) {
  let appointmentElement = document.createElement("div");
  appointmentElement.classList.add("appointment-card");
  appointmentElement.innerHTML = `
        <div class="appointment-info">
            <h3>${appointment.service}</h3>
            <p>Customer: ${appointment.customer_name}</p>
            <p>Pet: ${appointment.pet_name}</p>
        </div>
        <div class="appointment-time">
            <p>${formatDate(appointment.date)}</p>
        </div>
        <button class="more-options">⋮</button>
    `;
  return appointmentElement;
}

function renderSalesChart() {
  const ctx = document.getElementById("salesChart").getContext("2d");

  new Chart(ctx, {
    type: "bar",
    data: {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul"], // X-axis labels
      datasets: [
        {
          label: "Monthly Sales (₱)", // Update label to Peso
          data: [1000, 1200, 900, 1400, 1100, 1300, 1500], // Static sales data
          backgroundColor: [
            "#FFB74D",
            "#FFA726",
            "#FB8C00",
            "#F57C00",
            "#EF6C00",
            "#E65100",
            "#D84315",
          ],
          borderColor: "black", // Consistent border color
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
              return "₱" + value.toLocaleString(); // Format numbers with ₱
            },
          },
        },
      },
    },
  });
}

function fetchOrders() {
  fetch("http://192.168.1.3/backend/fetch_orders.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        let dashboardOrdersContainer = document.getElementById(
          "dashboardOrdersContainer"
        );
        dashboardOrdersContainer.innerHTML = ""; // Clear previous content

        data.orders.slice(0, 3).forEach((order) => {
          let orderElement = createOrderElement(order);
          dashboardOrdersContainer.appendChild(orderElement);
        });
      }
    })
    .catch((error) => console.error("❌ ERROR Fetching Orders:", error));
}

function fetchProducts() {
  fetch("http://192.168.1.3/backend/fetch_product.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        let dashboardProductsContainer = document.getElementById(
          "dashboardProductsContainer"
        );
        dashboardProductsContainer.innerHTML = ""; // Clear previous content

        data.products.slice(0, 3).forEach((product) => {
          let productElement = createProductElement(product);
          dashboardProductsContainer.appendChild(productElement);
        });
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

// Function to fetch orders notifications
// Global storage for notifications
let allNotifications = [];

// Function to fetch orders notifications
function fetchOrderNotifications() {
  return fetch("http://192.168.1.3/backend/fetch_orders.php")
    .then(response => response.json())
    .then(data => {
      if (data.success && Array.isArray(data.orders)) {
        return data.orders.map(order => {
          return {
            type: 'Order',
            message: `Order from ${order.username} - ₱${parseFloat(order.total_price).toFixed(2)}`,
            id: order.id
          };
        });
      } else {
        return [];
      }
    })
    .catch(error => {
      console.error("Error fetching orders:", error);
      return [];
    });
}

// Function to fetch appointment notifications (both grooming and veterinary)
function fetchAppointmentNotifications() {
  const groomingPromise = fetch("http://192.168.1.3/backend/fetch_grooming_appointments.php")
    .then(response => response.json())
    .then(data => {
      if (data.success && Array.isArray(data.appointments)) {
        return data.appointments.map(app => {
          return {
            type: 'Appointment',
            message: `Grooming appointment for ${app.name} on ${app.appointment_date}`,
            id: app.id
          };
        });
      } else {
        return [];
      }
    })
    .catch(error => {
      console.error("Error fetching grooming appointments:", error);
      return [];
    });

  const vetPromise = fetch("http://192.168.1.3/backend/fetch_veterinary_appointments.php")
    .then(response => response.json())
    .then(data => {
      if (data.success && Array.isArray(data.appointments)) {
        return data.appointments.map(app => {
          return {
            type: 'Appointment',
            message: `Veterinary appointment for ${app.name} on ${app.appointment_date}`,
            id: app.id
          };
        });
      } else {
        return [];
      }
    })
    .catch(error => {
      console.error("Error fetching veterinary appointments:", error);
      return [];
    });

  return Promise.all([groomingPromise, vetPromise]).then(results => {
    return [...results[0], ...results[1]];
  });
}

// Load all notifications and update the dropdown
function loadNotifications() {
  Promise.all([fetchOrderNotifications(), fetchAppointmentNotifications()])
    .then(results => {
      const notifications = [...results[0], ...results[1]];
      allNotifications = notifications; // store globally
      updateNotifBadge(notifications.length);
      updateNotifDropdown("all"); // default filter: all
    });
}

// Update the badge count
function updateNotifBadge(count) {
  const badge = document.getElementById('notifCount');
  badge.textContent = count;
  badge.style.display = count > 0 ? "block" : "none";
}

// Update notifications dropdown based on filter
function updateNotifDropdown(filter) {
  const container = document.getElementById('notifItemsContainer');
  container.innerHTML = "";
  let filteredNotifications = allNotifications;

  if (filter === "order") {
    filteredNotifications = allNotifications.filter(n => n.type.toLowerCase() === "order");
  } else if (filter === "appointment") {
    filteredNotifications = allNotifications.filter(n => n.type.toLowerCase() === "appointment");
  }

  if (filteredNotifications.length === 0) {
    container.innerHTML = `<div class="notif-item">No new notifications</div>`;
    return;
  }

  filteredNotifications.forEach(notif => {
    const item = document.createElement('div');
    item.className = "notif-item";
    item.textContent = `[${notif.type}] ${notif.message}`;
    // Attach click handler inside the loop
    item.onclick = function(event) {
      event.stopPropagation();
      event.preventDefault();
      // Hide the dropdown immediately
      document.getElementById('notifDropdown').classList.remove('active');
      setTimeout(function() {
        if (notif.type.toLowerCase() === "order") {
          window.location.href = "orders.html";
        } else {
          window.location.href = "appointments.html";
        }
      }, 200); // 200ms delay before redirect
    };
    container.appendChild(item);
  });
}

// Set up filter button click handlers
document.querySelectorAll('.notif-filter-btn').forEach(btn => {
  btn.addEventListener('click', function(event) {
    event.preventDefault();
    event.stopPropagation();
    // Remove active class from all filter buttons, add it to the clicked one
    document.querySelectorAll('.notif-filter-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    const filter = this.getAttribute('data-filter');
    updateNotifDropdown(filter);
  });
});

// Toggle notification dropdown visibility when clicking the bell
document.querySelector('.notification-bell').addEventListener('click', function(event) {
  const dropdown = document.getElementById('notifDropdown');
  dropdown.classList.toggle('active');
  event.stopPropagation();
});

// Set up exit button to close the dropdown
document.getElementById('notifExitBtn').addEventListener('click', function(event) {
  event.preventDefault();
  event.stopPropagation();
  document.getElementById('notifDropdown').classList.remove('active');
});

// Hide dropdown if clicking outside
document.addEventListener('click', function() {
  const dropdown = document.getElementById('notifDropdown');
  if (dropdown.classList.contains('active')) {
    dropdown.classList.remove('active');
  }
});

// Load notifications on page load and refresh periodically
document.addEventListener('DOMContentLoaded', function() {
  loadNotifications();
  // Poll every 60 seconds if desired:
  setInterval(loadNotifications, 60000);
});



