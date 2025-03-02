document.addEventListener("DOMContentLoaded", function () {
    setupModal();
    setupEventListeners();
    fetchUsers(); // Initial fetch on page load
});

// 📌 Initialize Modal Functionality
function setupModal() {
    const modal = document.getElementById("addShopOwnerModal");
    const openModalBtn = document.querySelector(".add-user-btn");
    const closeModalBtn = document.querySelector(".close-btn");

    if (openModalBtn) {
        openModalBtn.addEventListener("click", function () {
            modal.style.display = "block";
        });
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener("click", function () {
            modal.style.display = "none";
        });
    }

    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
}

// 📌 Global Variables
let allUsers = [];
let currentFilter = "all"; // Default filter

// 📌 Fetch Users and Apply Filters
async function fetchUsers(filter = "all", searchQuery = "") {
    try {
        const response = await fetch("http://192.168.1.3/backend/frontend/superadmin/fetch_users.php");
        const data = await response.json();
        allUsers = data.users || [];

        // ✅ Ensure each user has a proper name field (Use 'username' instead of 'name')
        allUsers.forEach(user => {
            if (!user.username) {
                user.username = user.name || "Unknown"; // Use 'name' if 'username' is missing
            }

            // ✅ Ensure the type is assigned properly (Shop Owner or Customer)
            if (!user.type) {
                user.type = user.shop_owner ? "Shop Owner" : "Customer";
            }
        });

        displayUsers(filter, searchQuery);
    } catch (error) {
        console.error("❌ Error fetching users:", error);
    }
}

// 📌 Display Users Based on Filter and Search
function displayUsers(filterType = "all", searchQuery = "") {
    currentFilter = filterType; // ✅ Update the active filter
    const userTable = document.getElementById("userTable");
    if (!userTable) return console.error("❌ ERROR: 'userTable' element is missing in the HTML.");

    userTable.innerHTML = "";

    let filteredUsers = allUsers;

    // ✅ Apply Filter by Role
    if (filterType === "shopOwners") {
        filteredUsers = filteredUsers.filter(user => user.type === "Shop Owner");
    } else if (filterType === "customers") {
        filteredUsers = filteredUsers.filter(user => user.type === "Customer");
    }

    // ✅ Apply Search Filtering
    if (searchQuery) {
        filteredUsers = filteredUsers.filter(user =>
            user.username.toLowerCase().includes(searchQuery) ||
            user.email.toLowerCase().includes(searchQuery) ||
            user.type.toLowerCase().includes(searchQuery)
        );
    }

    // ✅ Display Filtered Users in the Table
    if (filteredUsers.length > 0) {
        filteredUsers.forEach(user => {
            userTable.innerHTML += `
                <tr>
                    <td>#${user.id}</td>
                    <td>${user.username || "Unknown"}</td>
                    <td>${user.type}</td>
                    <td>${user.email}</td>
                    <td><button class="edit-btn">Edit</button></td>
                </tr>
            `;
        });
    } else {
        userTable.innerHTML = "<tr><td colspan='5'>No Users Found</td></tr>";
    }
}

// 📌 Setup Event Listeners
function setupEventListeners() {
    // ✅ Attach Filter Buttons and Make Them Work!
    document.querySelectorAll(".filter-btn").forEach(button => {
        button.addEventListener("click", function () {
            document.querySelectorAll(".filter-btn").forEach(btn => btn.classList.remove("active"));
            this.classList.add("active");

            let filterType = this.getAttribute("data-filter");
            console.log(`🔹 Applying Filter: ${filterType}`); // Debugging

            displayUsers(filterType); // ✅ Apply filter to displayed users
        });
    });

    // ✅ Search Functionality
    const searchBar = document.getElementById("searchBar");
    if (searchBar) {
        searchBar.addEventListener("keyup", function () {
            displayUsers(currentFilter, searchBar.value.trim().toLowerCase());
        });
    }

    // ✅ Create Shop Owner Button
    const createShopOwnerBtn = document.getElementById("createShopOwnerBtn");
    if (createShopOwnerBtn) {
        createShopOwnerBtn.addEventListener("click", addShopOwner);
    }
}

// 📌 Function to Add Shop Owner
async function addShopOwner() {
    const username = document.getElementById("shopOwnerUsername").value.trim();
    const email = document.getElementById("shopOwnerEmail").value.trim();
    const password = document.getElementById("shopOwnerPassword").value.trim();

    if (!username || !email || !password) {
        alert("⚠️ All fields are required.");
        return;
    }

    try {
        const response = await fetch("http://192.168.1.3/backend/Frontend/SuperAdmin/add_shop_owner.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ username, email, password })
        });

        const result = await response.json();

        if (result.success) {
            alert("✅ Shop Owner Created Successfully!");
            document.getElementById("shopOwnerUsername").value = "";
            document.getElementById("shopOwnerEmail").value = "";
            document.getElementById("shopOwnerPassword").value = "";

            fetchUsers(); // Refresh table
        } else {
            alert("❌ Error: " + result.message);
        }
    } catch (error) {
        console.error("❌ Error adding shop owner:", error);
    }
}

// 📌 Function to Highlight Active Filter Button
function setActiveFilter(activeBtn) {
    document.querySelectorAll(".filter-btn").forEach(btn => btn.classList.remove("active"));
    activeBtn.classList.add("active");
}
