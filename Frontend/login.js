document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.getElementById("loginForm");
  const toggleRole = document.getElementById("toggleRole");
  const loginTitle = document.getElementById("loginTitle");

  let isSuperAdmin = false; // Default role is Shop Owner

  // Toggle between Shop Owner and SuperAdmin login
  toggleRole.addEventListener("click", function (event) {
      event.preventDefault();
      isSuperAdmin = !isSuperAdmin;

      if (isSuperAdmin) {
          loginTitle.textContent = "SuperAdmin Login";
          toggleRole.textContent = "Switch to Shop Owner";
      } else {
          loginTitle.textContent = "Shop Owner Login";
          toggleRole.textContent = "Switch to SuperAdmin";
      }
  });

  // Handle login form submission
  loginForm.addEventListener("submit", async function (event) {
      event.preventDefault();

      const email = document.getElementById("email").value.trim();
      const password = document.getElementById("password").value.trim();

      if (!email || !password) {
          alert("⚠️ Please enter both email and password.");
          return;
      }

      const loginData = {
          email: email,
          password: password,
          role: isSuperAdmin ? "superadmin" : "shop_owner"
      };

      try {
          const response = await fetch("http://localhost/backend/authenticate.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify(loginData)
          });

          const result = await response.json();

          if (result.success) {
              alert("✅ Login Successful!");
              window.location.href = result.redirect; // Redirect to respective dashboard
          } else {
              alert("❌ Error: " + result.message);
          }
      } catch (error) {
          console.error("❌ Login failed:", error);
          alert("❌ Error connecting to server. Please try again.");
      }
  });
});
