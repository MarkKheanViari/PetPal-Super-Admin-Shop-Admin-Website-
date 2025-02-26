document.addEventListener("DOMContentLoaded", function () {
  fetchUserCounts();
  fetchUsers();
  fetchProducts();
  fetchRecentActivities();
});

// 📌 Global Chart Instance (To Prevent Overwriting)
let userChartInstance = null;

// 📌 Fetch and Display User Statistics for the Cards and Bar Graph
async function fetchUserCounts() {
  try {
    const response = await fetch("http://192.168.1.65/backend/Frontend/Superadmin/superadmin_stats.php");
    const data = await response.json();

    // ✅ Update the statistics cards
    document.getElementById("totalUsers").textContent = data.totalUsers ?? "N/A";
    document.getElementById("activeShopOwners").textContent = data.activeShopOwners ?? "N/A";
    document.getElementById("activeCustomers").textContent = data.activeCustomers ?? "N/A";
    document.getElementById("pendingProducts").textContent = data.pendingProducts ?? "N/A";

    // ✅ Render Bar Chart with User Data
    renderUserChart(["Active Customers", "Active Shop Owners"], [data.activeCustomers, data.activeShopOwners]);

  } catch (error) {
    console.error("❌ Error fetching user stats:", error);
  }
}

function renderUserChart(labels, dataValues) {
  const ctx = document.getElementById("userChart").getContext("2d");

  // ✅ Clear existing chart before rendering a new one
  if (userChartInstance) {
    userChartInstance.destroy();
  }

  // ✅ Create New Chart with Orange Theme
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
          ticks: {
            color: "#cc5500", // Dark orange tick labels
          },
          grid: {
            color: "rgba(255, 140, 0, 0.3)", // Light orange grid lines
          },
        },
        x: {
          ticks: {
            color: "#cc5500", // Dark orange tick labels
          },
          grid: {
            color: "rgba(255, 140, 0, 0.3)", // Light orange grid lines
          },
        },
      },
      plugins: {
        legend: {
          labels: {
            color: "#ff4500", // Bright orange legend text
            font: {
              weight: "bold",
            },
          },
        },
      },
    },
  });
}


// 📌 Fetch Recent Activities
async function fetchRecentActivities() {
  try {
    const response = await fetch("http://192.168.1.65/backend/Frontend/Superadmin/fetch_recent_activities.php");
    const data = await response.json();

    const activityList = document.getElementById("activityList");
    activityList.innerHTML = ""; // Clear previous content

    if (data.activities && data.activities.length > 0) {
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

// 📌 Fetch and Display Users in Table
async function fetchUsers() {
  try {
    const response = await fetch("http://192.168.1.65/backend/frontend/superadmin/fetch_users.php");
    const data = await response.json();

    const userTable = document.getElementById("userTable");
    userTable.innerHTML = "";

    // ✅ Ensure users data exists
    if (!data.users || data.users.length === 0) {
      userTable.innerHTML = "<tr><td colspan='5'>No Users Available</td></tr>";
      return;
    }

    // ✅ Limit to first 3 Users
    data.users.slice(0, 3).forEach(user => {
      userTable.innerHTML += `
        <tr>
          <td>#${user.id || "N/A"}</td>
          <td>${user.username || "N/A"}</td>  <!-- Ensure correct key -->
          <td>${user.type || "N/A"}</td>
          <td>${user.status || "N/A"}</td>
          <td><button>Edit</button></td>
        </tr>
      `;
    });

  } catch (error) {
    console.error("❌ Error fetching users:", error);
    document.getElementById("userTable").innerHTML = "<tr><td colspan='5'>Error loading users</td></tr>";
  }
}


// 📌 Fetch and Display Products in Table
async function fetchProducts() {
  try {
    const response = await fetch("http://192.168.1.65/backend/Frontend/SuperAdmin/fetch_products.php?page=dashboard");
    const data = await response.json();

    const productTable = document.getElementById("productTable");
    productTable.innerHTML = "";

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

// 📌 Tab Switching Logic
function openTab(event, tabName) {
  document.querySelectorAll(".tab-content").forEach(tab => tab.classList.remove("active"));
  document.querySelectorAll(".tab-btn").forEach(btn => btn.classList.remove("active"));

  document.getElementById(tabName).classList.add("active");
  event.currentTarget.classList.add("active");
}
