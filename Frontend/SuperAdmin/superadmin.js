document.addEventListener("DOMContentLoaded", function () {
  fetchUserCounts();
  fetchUsers();
  fetchProducts();
});

// 📌 Fetch User Counts for Bar Chart
async function fetchUserCounts() {
  try {
    const response = await fetch("http://192.168.1.65/backend/Frontend/Superadmin/superadmin_stats.php"); // ✅ Fix directory
    const data = await response.json();

    const ctx = document.getElementById("userChart").getContext("2d");
    new Chart(ctx, {
      type: "bar",
      data: {
        labels: ["Mobile Users", "Shop Owners"],
        datasets: [
          {
            label: "User Count",
            data: [data.customers, data.shopOwners],
            backgroundColor: ["blue", "orange"],
          },
        ],
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true },
        },
      },
    });
  } catch (error) {
    console.error("❌ Error fetching user stats:", error);
  }
}

// 📌 Fetch User Statistics for Graph
async function fetchUserStatistics() {
  try {
    const response = await fetch("http://192.168.1.65/backend/Frontend/Superadmin/fetch_user_counts.php"); // ✅ Fix directory
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
    const response = await fetch("http://192.168.1.65/backend/frontend/superadmin/fetch_users.php");
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
    const response = await fetch("http://192.168.1.65/backend/frontend/superadmin/fetch_products.php");
    const data = await response.json();

    const productTable = document.getElementById("productTable");
    productTable.innerHTML = "";

    // ✅ Limit to first 3 Products
    const productsToShow = data.products.slice(0, 3);
    if (productsToShow.length > 0) {
      productsToShow.forEach(product => {
        productTable.innerHTML += `
          <tr>
            <td>${product.id}</td>
            <td>${product.name}</td>
            <td>${product.owner || "Unknown"}</td>
            <td>
              <button class="approve-btn" onclick="approveProduct(${product.id})">Approve</button>
              <button class="delete-btn" onclick="deleteProduct(${product.id})">Delete</button>
            </td>
          </tr>
        `;
      });
    } else {
      productTable.innerHTML = "<tr><td colspan='4'>No Products Available</td></tr>";
    }
  } catch (error) {
    console.error("❌ Error fetching products:", error);
  }
}


// 📌 Render Bar Graph Chart
function renderUserChart(labels, mobileUsers, shopOwners) {
  const ctx = document.getElementById("userChart").getContext("2d");

  new Chart(ctx, {
    type: "bar",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Mobile Users",
          data: mobileUsers,
          backgroundColor: "rgba(54, 162, 235, 0.6)",
        },
        {
          label: "Shop Owners",
          data: shopOwners,
          backgroundColor: "rgba(255, 99, 132, 0.6)",
        },
      ],
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
}
