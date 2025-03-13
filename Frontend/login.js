document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.getElementById("loginForm");
  const toggleRole = document.getElementById("toggleRole");
  const loginTitle = document.getElementById("loginTitle");
  const container = document.querySelector(".container");
  const logoImage = document.getElementById("logoImage"); // Target the logo image

  // Error containers for each input
  const emailError = document.getElementById("emailError");
  const passwordError = document.getElementById("passwordError");

  // Show/Hide password elements
  const passwordInput = document.getElementById("password");
  const togglePasswordBtn = document.getElementById("togglePassword");

  let isSuperAdmin = false; // Default role is Shop Owner

  // Clear error message when user types
  document.getElementById("email").addEventListener("input", function () {
    emailError.textContent = "";
  });
  passwordInput.addEventListener("input", function () {
    passwordError.textContent = "";
  });

  // Toggle between Shop Owner and SuperAdmin login
  toggleRole.addEventListener("click", function (event) {
    event.preventDefault();
    isSuperAdmin = !isSuperAdmin;

    // Add or remove the switch-role class to trigger the transition
    if (isSuperAdmin) {
      loginTitle.textContent = "SuperAdmin Login";
      toggleRole.textContent = "Switch to Shop Owner";
      container.classList.add("switch-role");

      // Fade out the current image
      logoImage.classList.add("fade");
      setTimeout(() => {
        // Change the image source after the fade-out
        logoImage.src = "Untitled design (2).png";
        // Fade in the new image
        logoImage.classList.remove("fade");
      }, 250); // Match the fade duration (half of the 0.5s transition)
    } else {
      loginTitle.textContent = "Shop Owner Login";
      toggleRole.textContent = "Switch to SuperAdmin";
      container.classList.remove("switch-role");

      // Fade out the current image
      logoImage.classList.add("fade");
      setTimeout(() => {
        // Change the image source after the fade-out
        logoImage.src = "Bago.png";
        // Fade in the new image
        logoImage.classList.remove("fade");
      }, 250); // Match the fade duration (half of the 0.5s transition)
    }
  });

  // Toggle show/hide password functionality within the input field
  togglePasswordBtn.addEventListener("click", function () {
    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      togglePasswordBtn.textContent = "Hide";
    } else {
      passwordInput.type = "password";
      togglePasswordBtn.textContent = "Show";
    }
  });

  // Handle login form submission
  loginForm.addEventListener("submit", async function (event) {
    event.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = passwordInput.value.trim();

    // Clear previous error messages
    emailError.textContent = "";
    passwordError.textContent = "";

    let hasError = false;

    if (!email) {
      emailError.textContent = "⚠️ Please enter your email.";
      hasError = true;
    }
    if (!password) {
      passwordError.textContent = "⚠️ Please enter your password.";
      hasError = true;
    }
    if (hasError) {
      return;
    }

    const loginData = {
      email: email,
      password: password,
      role: isSuperAdmin ? "superadmin" : "shop_owner",
    };

    try {
      const response = await fetch(
        "http://192.168.1.65/backend/authenticate.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(loginData),
        }
      );

      if (!response.ok) {
        throw new Error(`HTTP Error: ${response.status}`);
      }

      const result = await response.json();
      console.log("🔍 Login Response:", result);

      if (result.success) {
        // Successful login: store credentials as before
        if (result.shop_owner_id) {
          localStorage.setItem("shop_owner_id", result.shop_owner_id);
        } else {
          console.error("❌ Error: shop_owner_id is missing in response.");
        }
        localStorage.setItem("shop_owner_token", result.token || "");
        localStorage.setItem("shop_owner_username", result.username || email);

        console.log("🔹 Stored Values:");
        console.log("shop_owner_id:", localStorage.getItem("shop_owner_id"));
        console.log("shop_owner_token:", localStorage.getItem("shop_owner_token"));
        console.log("shop_owner_username:", localStorage.getItem("shop_owner_username"));

        // Display the redesigned popup overlay
        const popupOverlay = document.getElementById("popupOverlay");
        popupOverlay.style.display = "flex";

        // Delay redirection so user can see the popup (adjust as needed)
        setTimeout(() => {
          window.location.href = result.redirect;
        }, 1500);
      } else {
        // Determine where to display error based on error message content.
        const message = result.message.toLowerCase();
        if (message.includes("email") || message.includes("user")) {
          emailError.textContent = " " + result.message;
        } else if (message.includes("password")) {
          passwordError.textContent = " " + result.message;
        } else {
          // Default error below the password input
          passwordError.textContent = " " + result.message;
        }
      }
    } catch (error) {
      console.error("❌ Login failed:", error);
      // Display connection error below the password input by default.
      passwordError.textContent = "❌ Error connecting to server. Please try again.";
    }
  });
});