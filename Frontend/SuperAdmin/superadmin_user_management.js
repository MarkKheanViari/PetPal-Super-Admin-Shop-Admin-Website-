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
            modal.style.display = "flex"; // Now it will center properly
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
        const response = await fetch("http://192.168.168.203/backend/frontend/superadmin/fetch_users.php");
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
        const response = await fetch("http://192.168.168.203/backend/Frontend/SuperAdmin/add_shop_owner.php", {
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

// Listen for clicks on any edit button (event delegation)
document.getElementById("userTable").addEventListener("click", function(e) {
    if (e.target.classList.contains("edit-btn")) {
        // Get the user's id from the first cell (assuming it's in the format "#id")
        const row = e.target.closest("tr");
        const idText = row.querySelector("td").innerText;
        const userId = idText.replace("#", "");
        
        // Find the user from allUsers array
        const user = allUsers.find(u => u.id == userId);
        if (user) {
            openEditUserModal(user);
        }
    }
});
function openEditUserModal(user) {
    const userModal = document.getElementById("userModal");
    document.getElementById("modalTitle").innerText = "Edit User";
    document.getElementById("userUsername").value = user.username;
    document.getElementById("userEmail").value = user.email; 
    // Clear the password field so that a new password can be entered if needed
    document.getElementById("userPassword").value = "";

    // Store the user's ID in a data attribute for use during update
    userModal.dataset.userId = user.id;

    // Display the modal
    userModal.style.display = "flex";
}



document.querySelector(".close-user-btn").addEventListener("click", function() {
    document.getElementById("userModal").style.display = "none";
});

document.getElementById("saveUserBtn").addEventListener("click", async function() {
    const userModal = document.getElementById("userModal");
    const userId = userModal.dataset.userId;
    const username = document.getElementById("userUsername").value.trim();
    const email = document.getElementById("userEmail").value.trim();
    const password = document.getElementById("userPassword").value.trim();

    // Basic validation
    if (!username || !email) {
        alert("⚠️ Please fill in all required fields.");
        return;
    }

    // Construct the payload; include the password only if provided (so it resets)
    const payload = { id: userId, username, email };
    if (password) {
        payload.password = password;
    }

    try {
        const response = await fetch("update_user.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.success) {
            alert("✅ User updated successfully!");
            userModal.style.display = "none";
            fetchUsers(); // Refresh the user list
        } else {
            alert("❌ Error: " + result.message);
        }
    } catch (error) {
        console.error("❌ Error updating user:", error);
    }
});

