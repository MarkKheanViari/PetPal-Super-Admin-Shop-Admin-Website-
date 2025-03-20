document.addEventListener("DOMContentLoaded", function () {
  fetchUserCounts();
  fetchUsers();
  fetchProducts();
  fetchRecentActivities();
  setupEventListeners();
});

// 📌 Setup Event Listeners
function setupEventListeners() {
  const logoutBtn = document.getElementById("logoutBtn");
  if (logoutBtn) {
      logoutBtn.addEventListener("click", function () {
          // Add confirmation prompt before logging out
          if (confirm("Are you sure you want to log out?")) {
              // Show logout notification
              Toastify({
                  text: "✅ Logged out successfully!",
                  duration: 3000,
                  style: { background: "var(--primary-orange)" }
              }).showToast();

              // Perform logout (e.g., clear session storage if used)
              // Example: sessionStorage.clear();

              // Redirect to login.html in the parent directory (Frontend)
              setTimeout(() => {
                  window.location.href = "../login.html"; // Changed to relative path
              }, 1000);
          }
      });
  }
}

// 📌 Fetch User Counts for Bar Chart
async function fetchUserCounts() {
  try {
      const response = await fetch("http://192.168.1.12/backend/Frontend/Superadmin/superadmin_stats.php");
      const data = await response.json();

      console.log("✅ User Stats Response:", data); // Debugging

      // ✅ Set default values if API response is missing data
      document.getElementById("totalUsers").textContent = data.totalUsers ?? "N/A";
      document.getElementById("activeShopOwners").textContent = data.activeShopOwners ?? "N/A";
      document.getElementById("activeCustomers").textContent = data.activeCustomers ?? "N/A";
      document.getElementById("pendingProducts").textContent = data.pendingProducts ?? "N/A";

      // ✅ Pass data to graph function
      renderUserChart(
          ["Active Customers", "Active Shop Owners"],
          [data.activeCustomers || 0, data.activeShopOwners || 0]
      );
  } catch (error) {
      console.error("❌ Error fetching user stats:", error);
  }
}

// 📌 Fetch User Statistics for Graph (Unused in this version, but kept for reference)
async function fetchUserStatistics() {
  try {
      const response = await fetch("http://192.168.1.12/backend/Frontend/Superadmin/fetch_user_counts.php");
      const data = await response.json();
      renderUserChart(data.months, data.mobile_users, data.shop_owners);
  } catch (error) {
      console.error("❌ Error fetching user statistics:", error);
  }
}

// Tab Switching Logic
function openTab(event, tabName) {
  document.querySelectorAll(".tab-content").forEach((tab) => tab.classList.remove("active"));
  document.querySelectorAll(".tab-btn").forEach((btn) => btn.classList.remove("active"));

  document.getElementById(tabName).classList.add("active");
  event.currentTarget.classList.add("active");
}

// Fetch Users and Limit to 3
async function fetchUsers() {
  try {
      const response = await fetch("http://192.168.1.12/backend/frontend/superadmin/fetch_users.php");
      const data = await response.json();

      const userTable = document.getElementById("userTable");
      userTable.innerHTML = "";

      // ✅ Limit to first 3 Users
      const usersToShow = data.users.slice(0, 3);
      if (usersToShow.length > 0) {
          usersToShow.forEach((user) => {
              userTable.innerHTML += `
                  <tr>
                      <td>#${user.id}</td>
                      <td>${user.name || "Unknown"}</td>
                      <td>${user.type || "N/A"}</td>
                      <td>${user.status || "N/A"}</td>
                      <td><button>Edit</button></td>
                  </tr>
              `;
          });
      } else {
          userTable.innerHTML = "<tr><td colspan='5'>No Users Available</td></tr>";
      }
  } catch (error) {
      console.error("❌ Error fetching users:", error);
  }
}

// Fetch Products and Limit to 3
async function fetchProducts() {
  try {
      const response = await fetch("http://192.168.1.12/backend/Frontend/SuperAdmin/fetch_products.php?page=dashboard");
      const data = await response.json();

      const productTable = document.getElementById("productTable");
      productTable.innerHTML = "";

      // ✅ Display only ID, Name, Owner, and Actions
      data.products.slice(0, 3).forEach((product) => {
          productTable.innerHTML += `
              <tr>
                  <td>${product.id}</td>
                  <td>${product.name || "Unknown"}</td>
                  <td>${product.owner || "Unknown"}</td>
                  <td>
                      <button class="approve-btn">Approve</button>
                      <button class="delete-btn">Delete</button>
                  </td>
              </tr>
          `;
      });
  } catch (error) {
      console.error("❌ Error fetching products:", error);
  }
}

// Fetch Recent Activities
async function fetchRecentActivities() {
  try {
      const response = await fetch("http://192.168.1.12/backend/Frontend/Superadmin/fetch_recent_activities.php");
      const data = await response.json();

      const activityList = document.getElementById("activityList");
      activityList.innerHTML = ""; // Clear previous content

      if (data.activities.length > 0) {
          data.activities.forEach((activity) => {
              const li = document.createElement("li");
              li.textContent = activity;
              activityList.appendChild(li);
          });
      } else {
          activityList.innerHTML = "<li>No Recent Activities</li>";
      }
  } catch (error) {
      console.error("❌ Error fetching recent activities:", error);
  }
}

// 📌 Render Bar Graph Chart
let userChartInstance = null;

function renderUserChart(labels, dataValues) {
  const ctx = document.getElementById("userChart").getContext("2d");

  // ✅ Destroy previous chart before creating a new one
  if (userChartInstance) {
      userChartInstance.destroy();
  }

  console.log("✅ Rendering Chart with Data:", labels, dataValues); // Debugging

  // ✅ Create new Chart.js bar graph
  userChartInstance = new Chart(ctx, {
      type: "bar",
      data: {
          labels: labels,
          datasets: [
              {
                  label: "User Count",
                  data: dataValues,
                  backgroundColor: ["rgba(255, 140, 0, 0.8)", "rgba(255, 87, 34, 0.8)"],
                  borderWidth: 2,
                  hoverBackgroundColor: ["rgba(255, 165, 0, 1)", "rgba(255, 69, 0, 1)"],
              },
          ],
      },
      options: {
          responsive: true,
          scales: {
              y: {
                  beginAtZero: true,
                  ticks: { color: "#cc5500" },
                  grid: { color: "rgba(255, 140, 0, 0.3)" },
              },
              x: {
                  ticks: { color: "#cc5500" },
                  grid: { color: "rgba(255, 140, 0, 0.3)" },
              },
          },
          plugins: {
              legend: {
                  labels: {
                      color: "#ff4500",
                      font: { weight: "bold" },
                  },
              },
          },
      },
  });
}