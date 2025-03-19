// Global storage for notifications
let allNotifications = [];

// Function to fetch orders notifications
function fetchOrderNotifications() {
  return fetch("http://192.168.1.13/backend/fetch_orders.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success && Array.isArray(data.orders)) {
        return data.orders.map((order) => {
          return {
            type: "Order",
            message: `Order from ${order.username} - ₱${parseFloat(order.total_price).toFixed(2)}`,
            id: order.id,
          };
        });
      } else {
        return [];
      }
    })
    .catch((error) => {
      console.error("Error fetching orders:", error);
      return [];
    });
}

// Function to fetch appointment notifications (both grooming and veterinary)
function fetchAppointmentNotifications() {
  const groomingPromise = fetch("http://192.168.1.13/backend/fetch_grooming_appointments.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success && Array.isArray(data.appointments)) {
        return data.appointments.map((app) => {
          return {
            type: "Appointment",
            message: `Grooming appointment for ${app.name} on ${app.appointment_date}`,
            id: app.id,
          };
        });
      } else {
        return [];
      }
    })
    .catch((error) => {
      console.error("Error fetching grooming appointments:", error);
      return [];
    });

  const vetPromise = fetch("http://192.168.1.13/backend/fetch_veterinary_appointments.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success && Array.isArray(data.appointments)) {
        return data.appointments.map((app) => {
          return {
            type: "Appointment",
            message: `Veterinary appointment for ${app.name} on ${app.appointment_date}`,
            id: app.id,
          };
        });
      } else {
        return [];
      }
    })
    .catch((error) => {
      console.error("Error fetching veterinary appointments:", error);
      return [];
    });

  return Promise.all([groomingPromise, vetPromise]).then((results) => {
    return [...results[0], ...results[1]];
  });
}

// Load all notifications and update the dropdown
function loadNotifications() {
  Promise.all([
    fetchOrderNotifications(),
    fetchAppointmentNotifications(),
  ]).then((results) => {
    const notifications = [...results[0], ...results[1]];
    allNotifications = notifications; // store globally
    updateNotifBadge(allNotifications.length);
    updateNotifDropdown("all"); // default filter: all
  });
}

// Update the badge count
function updateNotifBadge(count) {
  const badge = document.getElementById("notifCount");
  badge.textContent = count;
  badge.style.display = count > 0 ? "block" : "none";
}

// Function to mark a notification as read (and optionally redirect)
function markNotificationAsRead(notif, redirect) {
  // Remove the clicked notification from the global list.
  allNotifications = allNotifications.filter((n) => n !== notif);
  
  // Update badge count and dropdown.
  updateNotifBadge(allNotifications.length);
  const activeFilter = document.querySelector(".notif-filter-btn.active").getAttribute("data-filter");
  updateNotifDropdown(activeFilter);
  
  // Hide the dropdown.
  document.getElementById("notifDropdown").classList.remove("active");
  
  // If redirect is true, navigate to the related page after a short delay.
  if (redirect) {
    setTimeout(function () {
      if (notif.type.toLowerCase() === "order") {
        window.location.href = "orders.html";
      } else {
        window.location.href = "appointments.html";
      }
    }, 200);
  }
}

// Update notifications dropdown based on filter
function updateNotifDropdown(filter) {
  const container = document.getElementById("notifItemsContainer");
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
    
    // Create the notification message element (clicking it will redirect)
    const messageSpan = document.createElement("span");
    messageSpan.textContent = `[${notif.type}] ${notif.message}`;
    messageSpan.style.cursor = "pointer";
    messageSpan.onclick = function (event) {
      event.stopPropagation();
      event.preventDefault();
      markNotificationAsRead(notif, true); // Mark as read and redirect
    };

    // Create the "Mark as read" button (just removes the notification)
    const markReadBtn = document.createElement("button");
    markReadBtn.textContent = "Mark as read";
    markReadBtn.className = "mark-read-btn";
    markReadBtn.style.marginLeft = "10px";
    markReadBtn.onclick = function (event) {
      event.stopPropagation();
      event.preventDefault();
      markNotificationAsRead(notif, false); // Only mark as read (no redirect)
    };

    item.appendChild(messageSpan);
    item.appendChild(markReadBtn);
    container.appendChild(item);
  });
}

// Set up filter button click handlers
document.querySelectorAll(".notif-filter-btn").forEach((btn) => {
  btn.addEventListener("click", function (event) {
    event.preventDefault();
    event.stopPropagation();
    // Remove active class from all filter buttons, add it to the clicked one
    document.querySelectorAll(".notif-filter-btn").forEach((b) => b.classList.remove("active"));
    this.classList.add("active");

    const filter = this.getAttribute("data-filter");
    updateNotifDropdown(filter);
  });
});

// Toggle notification dropdown visibility when clicking the bell
document.querySelector(".notification-bell").addEventListener("click", function (event) {
  const dropdown = document.getElementById("notifDropdown");
  dropdown.classList.toggle("active");
  event.stopPropagation();
});

// Set up exit button to close the dropdown
document.getElementById("notifExitBtn").addEventListener("click", function (event) {
  event.preventDefault();
  event.stopPropagation();
  document.getElementById("notifDropdown").classList.remove("active");
});

// Hide dropdown if clicking outside
document.addEventListener("click", function () {
  const dropdown = document.getElementById("notifDropdown");
  if (dropdown.classList.contains("active")) {
    dropdown.classList.remove("active");
  }
});

// Load notifications on page load and refresh periodically
document.addEventListener("DOMContentLoaded", function () {
  loadNotifications();
  // Poll every 60 seconds if desired:
  setInterval(loadNotifications, 60000);
});
