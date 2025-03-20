// 📌 Global Variables
let allUsers = [];
let currentFilter = "all"; // Default filter

// 📌 Initialize on Page Load
document.addEventListener("DOMContentLoaded", function () {
  setupModal();
  setupEventListeners();
  fetchUsers(); // Initial fetch on page load
});

// 📌 Initialize Modal Functionality
function setupModal() {
  const addModal = document.getElementById("addShopOwnerModal");
  const editModal = document.getElementById("userModal");
  const openModalBtn = document.querySelector(".add-user-btn");
  const closeAddModalBtn = document.querySelector(".close-btn");
  const closeEditModalBtn = document.querySelector(".close-user-btn");

  if (openModalBtn) {
    openModalBtn.addEventListener("click", function () {
      addModal.style.display = "flex";
      addModal.setAttribute("aria-hidden", "false");
      addModal.querySelector(".modal-content").style.animation = "slideIn 0.3s ease forwards";
    });
  }

  if (closeAddModalBtn) {
    closeAddModalBtn.addEventListener("click", function () {
      const modalContent = addModal.querySelector(".modal-content");
      modalContent.style.animation = "slideOut 0.3s ease forwards";
      modalContent.addEventListener("animationend", function () {
        addModal.style.display = "none";
        addModal.setAttribute("aria-hidden", "true");
        document.getElementById("addShopOwnerForm").reset();
        document.querySelectorAll(".error-message").forEach(span => span.textContent = "");
      }, { once: true });
    });
  }

  if (closeEditModalBtn) {
    closeEditModalBtn.addEventListener("click", function () {
      const modalContent = editModal.querySelector(".modal-content");
      modalContent.style.animation = "slideOut 0.3s ease forwards";
      modalContent.addEventListener("animationend", function () {
        editModal.style.display = "none";
        editModal.setAttribute("aria-hidden", "true");
        document.getElementById("editUserForm").reset();
        document.querySelectorAll(".error-message").forEach(span => span.textContent = "");
      }, { once: true });
    });
  }

  window.addEventListener("click", function (event) {
    if (event.target === addModal) {
      closeAddModalBtn.click();
    } else if (event.target === editModal) {
      closeEditModalBtn.click();
    }
  });
}

// 📌 Fetch Users and Apply Filters
async function fetchUsers(filter = "all", searchQuery = "") {
  const userTable = document.getElementById("userTable");
  userTable.innerHTML = "<tr><td colspan='5'><div class='spinner'></div></td></tr>";
  try {
    const response = await fetch("http://192.168.1.12/backend/frontend/superadmin/fetch_users.php");
    const data = await response.json();
    allUsers = data.users || [];

    // ✅ Ensure each user has a proper name field (Use 'username' instead of 'name')
    allUsers.forEach((user) => {
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
    userTable.innerHTML = "<tr><td colspan='5'>Error loading users</td></tr>";
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
    filteredUsers = filteredUsers.filter((user) => user.type === "Shop Owner");
  } else if (filterType === "customers") {
    filteredUsers = filteredUsers.filter((user) => user.type === "Customer");
  }

  // ✅ Apply Search Filtering
  if (searchQuery) {
    filteredUsers = filteredUsers.filter(
      (user) =>
        user.username.toLowerCase().includes(searchQuery) ||
        user.email.toLowerCase().includes(searchQuery) ||
        user.type.toLowerCase().includes(searchQuery)
    );
  }

  // ✅ Display Filtered Users in the Table
  if (filteredUsers.length > 0) {
    filteredUsers.forEach((user) => {
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
  document.querySelectorAll(".filter-btn").forEach((button) => {
    button.addEventListener("click", function () {
      document.querySelectorAll(".filter-btn").forEach((btn) => btn.classList.remove("active"));
      this.classList.add("active");

      let filterType = this.getAttribute("data-filter");
      console.log(`🔹 Applying Filter: ${filterType}`); // Debugging
      displayUsers(filterType, document.getElementById("searchBar").value.trim().toLowerCase());
    });
  });

  // ✅ Search Functionality
  const searchBar = document.getElementById("searchBar");
  if (searchBar) {
    searchBar.addEventListener("keyup", function () {
      displayUsers(currentFilter, this.value.trim().toLowerCase());
    });
  }

  // ✅ Add Shop Owner Form Submission
  const addForm = document.getElementById("addShopOwnerForm");
  if (addForm) {
    addForm.addEventListener("submit", async function (event) {
      event.preventDefault();
      const username = document.getElementById("shopOwnerUsername").value.trim();
      const email = document.getElementById("shopOwnerEmail").value.trim();
      const password = document.getElementById("shopOwnerPassword").value.trim();
      const btn = document.getElementById("createShopOwnerBtn");

      let hasError = false;
      document.querySelectorAll(".error-message").forEach(span => span.textContent = "");

      if (!username) {
        document.getElementById("usernameError").textContent = "Username is required";
        hasError = true;
      }
      if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        document.getElementById("emailError").textContent = "Valid email is required";
        hasError = true;
      }
      if (!password || password.length < 6) {
        document.getElementById("passwordError").textContent = "Password must be at least 6 characters";
        hasError = true;
      }

      if (!hasError) {
        btn.classList.add("loading");
        btn.disabled = true;
        await addShopOwner(username, email, password);
        btn.classList.remove("loading");
        btn.disabled = false;
      }
    });
  }

  // ✅ Edit User Form Submission
  const editForm = document.getElementById("editUserForm");
  if (editForm) {
    editForm.addEventListener("submit", async function (event) {
      event.preventDefault();
      const userModal = document.getElementById("userModal");
      const userId = userModal.dataset.userId;
      const username = document.getElementById("userUsername").value.trim();
      const email = document.getElementById("userEmail").value.trim();
      const password = document.getElementById("userPassword").value.trim();
      const btn = document.getElementById("saveUserBtn");

      let hasError = false;
      document.querySelectorAll(".error-message").forEach(span => span.textContent = "");

      if (!username) {
        document.getElementById("editUsernameError").textContent = "Username is required";
        hasError = true;
      }
      if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        document.getElementById("editEmailError").textContent = "Valid email is required";
        hasError = true;
      }
      if (password && password.length < 6) {
        document.getElementById("editPasswordError").textContent = "Password must be at least 6 characters";
        hasError = true;
      }

      if (!hasError) {
        btn.classList.add("loading");
        btn.disabled = true;
        const payload = { id: userId, username, email };
        if (password) payload.password = password;
        await saveUserChanges(payload);
        btn.classList.remove("loading");
        btn.disabled = false;
      }
    });
  }

  // ✅ Edit Button Event Delegation
  document.getElementById("userTable").addEventListener("click", function (e) {
    if (e.target.classList.contains("edit-btn")) {
      const row = e.target.closest("tr");
      const idText = row.querySelector("td").innerText;
      const userId = idText.replace("#", "");
      const user = allUsers.find((u) => u.id == userId);
      if (user) {
        openEditUserModal(user);
      }
    }
  });
}

// 📌 Function to Add Shop Owner
async function addShopOwner(username, email, password) {
  try {
    const response = await fetch("http://192.168.1.12/backend/Frontend/SuperAdmin/add_shop_owner.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ username, email, password }),
    });

    const result = await response.json();

    if (result.success) {
      Toastify({
        text: "✅ Shop Owner Created Successfully!",
        duration: 3000,
        style: { background: "var(--primary-orange)" }
      }).showToast();
      document.getElementById("addShopOwnerForm").reset();
      document.querySelector(".close-btn").click();
      fetchUsers();
    } else {
      throw new Error(result.message || "Failed to add shop owner");
    }
  } catch (error) {
    console.error("❌ Error adding shop owner:", error);
    Toastify({
      text: "❌ Error: " + error.message,
      duration: 3000,
      style: { background: "#ef4444" }
    }).showToast();
  }
}

// 📌 Function to Open Edit User Modal
function openEditUserModal(user) {
  const userModal = document.getElementById("userModal");
  document.getElementById("modalTitle").innerText = "Edit User";
  document.getElementById("userUsername").value = user.username;
  document.getElementById("userEmail").value = user.email;
  document.getElementById("userPassword").value = "";
  userModal.dataset.userId = user.id;
  userModal.style.display = "flex";
  userModal.setAttribute("aria-hidden", "false");
  userModal.querySelector(".modal-content").style.animation = "slideIn 0.3s ease forwards";
}

// 📌 Function to Save User Changes
async function saveUserChanges(payload) {
  try {
    const response = await fetch("http://192.168.1.12/backend/update_user.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    const result = await response.json();

    if (result.success) {
      Toastify({
        text: "✅ User Updated Successfully!",
        duration: 3000,
        style: { background: "var(--primary-orange)" }
      }).showToast();
      document.querySelector(".close-user-btn").click();
      fetchUsers();
    } else {
      throw new Error(result.message || "Failed to update user");
    }
  } catch (error) {
    console.error("❌ Error updating user:", error);
    Toastify({
      text: "❌ Error: " + error.message,
      duration: 3000,
      style: { background: "#ef4444" }
    }).showToast();
  }
}

// 📌 Function to Highlight Active Filter Button
function setActiveFilter(activeBtn) {
  document.querySelectorAll(".filter-btn").forEach((btn) => btn.classList.remove("active"));
  activeBtn.classList.add("active");
}