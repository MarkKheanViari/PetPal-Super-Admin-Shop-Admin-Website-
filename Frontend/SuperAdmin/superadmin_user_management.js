document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("addShopOwnerModal");
    const openModalBtn = document.querySelector(".add-user-btn");
    const closeModalBtn = document.querySelector(".close-btn");

    // 📌 Open Modal
    openModalBtn.addEventListener("click", function () {
        modal.style.display = "block";
    });

    // 📌 Close Modal
    closeModalBtn.addEventListener("click", function () {
        modal.style.display = "none";
    });

    // 📌 Close Modal When Clicking Outside
    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
});


// 📌 Fetch User Accounts and Store in Global Variable
let allUsers = [];
let currentFilter = "all"; // Track the active filter

async function fetchUsers() {
    try {
        const response = await fetch("http://192.168.1.65/backend/Frontend/SuperAdmin/fetch_users.php");
        const data = await response.json();

        // ✅ Ensure valid data format
        if (!data || !Array.isArray(data.users)) {
            console.error("❌ ERROR: Invalid user data format:", data);
            return;
        }

        allUsers = data.users.map(user => ({
            id: user.id || "N/A",
            username: user.username || "Unknown",
            role: user.role || "Unknown", // Ensure role exists
            status: user.status || "N/A"
        }));

        console.log("✅ Processed User Data:", allUsers); // Debugging

        displayUsers(currentFilter); // Display all users initially

    } catch (error) {
        console.error("❌ Error fetching users:", error);
    }
}

// 📌 Function to Filter and Display Users (Including Search)
function displayUsers(filterType) {
    currentFilter = filterType; // Update current filter
    const userTable = document.getElementById("userTable");
    const searchQuery = document.getElementById("searchBar").value.trim().toLowerCase();

    if (!userTable) {
        console.error("❌ ERROR: 'userTable' element is missing in the HTML.");
        return;
    }

    userTable.innerHTML = ""; // Clear table before adding new data

    let filteredUsers = allUsers;

    // Apply Role-Based Filtering
    if (filterType === "shopOwners") {
        filteredUsers = allUsers.filter(user => user.role.toLowerCase() === "shop owner");
    } else if (filterType === "customers") {
        filteredUsers = allUsers.filter(user => user.role.toLowerCase() === "customer");
    }

    // Apply Search Filtering
    if (searchQuery) {
        filteredUsers = filteredUsers.filter(user =>
            user.username.toLowerCase().includes(searchQuery) ||
            user.role.toLowerCase().includes(searchQuery)
        );
    }

    console.log(`🔹 Filtered Users (${filterType}):`, filteredUsers); // Debugging

    // ✅ Render users in table
    if (filteredUsers.length > 0) {
        filteredUsers.forEach(user => {
            const row = `
                <tr>
                    <td>#${user.id}</td>
                    <td>${user.username}</td>
                    <td>${user.role}</td>
                    <td><span class="${user.status.toLowerCase() === 'active' ? 'status-active' : 'status-inactive'}">${user.status}</span></td>
                    <td><button class="edit-btn">Edit</button></td>
                </tr>
            `;
            userTable.innerHTML += row;
        });
    } else {
        userTable.innerHTML = "<tr><td colspan='5'>No Users Found</td></tr>";
    }
}

// 📌 Attach Filter Buttons Event Listeners
document.addEventListener("DOMContentLoaded", function () {
    fetchUsers(); // Fetch users on page load

    document.getElementById("allUsersBtn").addEventListener("click", function () {
        setActiveFilter(this);
        displayUsers("all");
    });

    document.getElementById("shopOwnersBtn").addEventListener("click", function () {
        setActiveFilter(this);
        displayUsers("shopOwners");
    });

    document.getElementById("customersBtn").addEventListener("click", function () {
        setActiveFilter(this);
        displayUsers("customers");
    });

    // 📌 Attach Search Functionality
    document.getElementById("searchBar").addEventListener("keyup", function () {
        displayUsers(currentFilter); // Re-filter users based on search
    });
});

document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("createShopOwnerBtn").addEventListener("click", addShopOwner);
});

// 📌 Function to Add Shop Owner
async function addShopOwner() {
    const username = document.getElementById("shopOwnerUsername").value.trim();
    const email = document.getElementById("shopOwnerEmail").value.trim();
    const password = document.getElementById("shopOwnerPassword").value.trim();

    if (!username || !email || !password) {
        alert("⚠️ All fields are required.");
        return;
    }

    const shopOwnerData = { username, email, password };

    try {
        const response = await fetch("http://192.168.1.65/backend/Frontend/SuperAdmin/add_shop_owner.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                username: username,
                email: email,
                password: password
            })
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

// 📌 Function to Add Shop Owner
async function addShopOwner() {
    const username = document.getElementById("shopOwnerUsername").value.trim();
    const email = document.getElementById("shopOwnerEmail").value.trim();
    const password = document.getElementById("shopOwnerPassword").value.trim();

    if (!username || !email || !password) {
        alert("⚠️ All fields are required.");
        return;
    }

    const shopOwnerData = { username, email, password };

    try {
        const response = await fetch("http://192.168.1.65/backend/Frontend/SuperAdmin/add_shop_owner.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(shopOwnerData),
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
