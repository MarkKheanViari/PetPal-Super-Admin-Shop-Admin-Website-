document.addEventListener("DOMContentLoaded", function () {
    fetchUserCounts();
    fetchUsers();
    fetchProducts();
});

// 📌 Fetch User Counts for Chart
function fetchUserCounts() {
    fetch("http://192.168.137.239/backend/Frontend/SuperAdmin/superadmin_stats.php")
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById("userChart").getContext("2d");
            new Chart(ctx, {
                type: "bar",
                data: {
                    labels: ["Mobile Users", "Shop Owners"],
                    datasets: [{
                        label: "User Count",
                        data: [data.customers, data.shopOwners],
                        backgroundColor: ["blue", "orange"]
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        })
        .catch(error => console.error("❌ Error fetching stats:", error));
}

// 📌 Fetch All Users
function fetchUsers() {
    fetch("http://192.168.137.239/backend/Frontend/SuperAdmin/fetch_users.php")
        .then(response => response.text())
        .then(text => {
            console.log("Raw Response:", text);
            return JSON.parse(text);
        })
        .then(data => {
            const userTable = document.getElementById("userTable");
            userTable.innerHTML = "";
            if (!data.users) {
                console.error("No users found");
                return;
            }

            data.users.forEach(user => {
                userTable.innerHTML += `
                    <tr>
                        <td>${user.id}</td>
                        <td>${user.username}</td>
                        <td>${user.role}</td>
                        <td>
                            <button class="delete-btn" onclick="deleteUser(${user.id})">Delete</button>
                        </td>
                    </tr>
                `;
            });
        })
        .catch(error => console.error("❌ Error fetching users:", error));
}

// 📌 Fetch All Products
function fetchProducts() {
    fetch("http://192.168.137.239/backend/Frontend/SuperAdmin/fetch_products.php")
        .then(response => response.json())
        .then(data => {
            console.log("Product Data:", data);
            const productTable = document.getElementById("productTable");
            productTable.innerHTML = "";

            if (data.products && data.products.length > 0) {
                data.products.forEach(product => {
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
        })
        .catch(error => console.error("❌ Error fetching products:", error));
}


