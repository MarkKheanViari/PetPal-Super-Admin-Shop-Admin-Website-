document.addEventListener("DOMContentLoaded", function () {
  fetchUserCounts();
  fetchUsers();
  fetchProducts();
});

// 📌 Fetch User Counts for Bar Chart
async function fetchUserCounts() {
  try {
    const response = await fetch("http://192.168.168.55/backend/Frontend/Superadmin/superadmin_stats.php");
    const data = await response.json();
    
    console.log("✅ User Stats Response:", data); // Debugging

    // ✅ Set default values if API response is missing data
    document.getElementById("totalUsers").textContent = data.totalUsers ?? "N/A";
    document.getElementById("activeShopOwners").textContent = data.activeShopOwners ?? "N/A";
    document.getElementById("activeCustomers").textContent = data.activeCustomers ?? "N/A";
    document.getElementById("pendingProducts").textContent = data.pendingProducts ?? "N/A";

    // ✅ Pass data to graph function
    renderUserChart(["Active Customers", "Active Shop Owners"], [data.activeCustomers, data.activeShopOwners]);
  } catch (error) {
    console.error("❌ Error fetching user stats:", error);
  }
}


// 📌 Fetch User Statistics for Graph
async function fetchUserStatistics() {
  try {
    const response = await fetch("http://192.168.168.55/backend/Frontend/Superadmin/fetch_user_counts.php"); // ✅ Fix directory
    const data = await response.json();

    renderUserChart(data.months, data.mobile_users, data.shop_owners);
  } catch (error) {
    console.error("❌ Error fetching user statistics:", error);
  }
}

// Tab Switching Logic
function openTab(event, tabName) {
  document.querySelectorAll(".tab-content").forEach(tab => tab.classList.remove("active"));
  document.querySelectorAll(".tab-btn").forEach(btn => btn.classList.remove("active"));

  document.getElementById(tabName).classList.add("active");
  event.currentTarget.classList.add("active");
}

// Fetch Users and Limit to 3
async function fetchUsers() {
  try {
    const response = await fetch("http://192.168.168.55/backend/frontend/superadmin/fetch_users.php");
    const data = await response.json();

    const userTable = document.getElementById("userTable");
    userTable.innerHTML = "";

    // ✅ Limit to first 3 Users
    const usersToShow = data.users.slice(0, 3);
    if (usersToShow.length > 0) {
      usersToShow.forEach(user => {
        userTable.innerHTML += `
          <tr>
            <td>#${user.id}</td>
            <td>${user.name}</td>
            <td>${user.type}</td>
            <td>${user.status}</td>
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

// 📌 Fetch All Products
// Fetch Products and Limit to 3
async function fetchProducts() {
  try {
      const response = await fetch("http://192.168.168.55/backend/Frontend/SuperAdmin/fetch_products.php?page=dashboard");
      const data = await response.json();

      const productTable = document.getElementById("productTable");
      productTable.innerHTML = "";

      // ✅ Display only ID, Name, Owner, and Actions
      data.products.slice(0, 3).forEach(product => {
          productTable.innerHTML += `
              <tr>
                  <td>${product.id}</td>
                  <td>${product.name}</td>
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


async function fetchRecentActivities() {
  try {
      const response = await fetch("http://192.168.168.55/backend/Frontend/Superadmin/fetch_recent_activities.php");
      const data = await response.json();

      const activityList = document.getElementById("activityList");
      activityList.innerHTML = ""; // Clear previous content

      if (data.activities.length > 0) {
          data.activities.forEach(activity => {
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
// 📌 Store chart instance to prevent duplicates
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
          backgroundColor: ["rgba(255, 140, 0, 0.8)", "rgba(255, 87, 34, 0.8)"], // Orange shades
          borderWidth: 2,
          hoverBackgroundColor: ["rgba(255, 165, 0, 1)", "rgba(255, 69, 0, 1)"], // Brighter orange on hover
        },
      ],
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: { color: "#cc5500" }, // Dark orange tick labels
          grid: { color: "rgba(255, 140, 0, 0.3)" }, // Light orange grid lines
        },
        x: {
          ticks: { color: "#cc5500" }, // Dark orange tick labels
          grid: { color: "rgba(255, 140, 0, 0.3)" }, // Light orange grid lines
        },
      },
      plugins: {
        legend: {
          labels: {
            color: "#ff4500", // Bright orange legend text
            font: { weight: "bold" },
          },
        },
      },
    },
  });
}

