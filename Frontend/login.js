document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.getElementById("loginForm");
  const toggleRole = document.getElementById("toggleRole");
  const loginTitle = document.getElementById("loginTitle");
  const container = document.querySelector(".container");
  const logoImage = document.getElementById("logoImage");

  // Error containers for each input
  const emailError = document.getElementById("emailError");
  const passwordError = document.getElementById("passwordError");

  // Show/Hide password elements for login form
  const passwordInput = document.getElementById("password");
  const togglePasswordBtn = document.getElementById("togglePassword");

  // Forgot Password elements
  const forgotPasswordLink = document.getElementById("forgotPasswordLink");
  const forgotPasswordPopup = document.getElementById("forgotPasswordPopup");
  const cancelResetBtn = document.getElementById("cancelReset");
  const forgotPasswordForm = document.getElementById("forgotPasswordForm");
  const resetEmail = document.getElementById("resetEmail");
  const currentPasswordInput = document.getElementById("currentPassword");
  const newPasswordInput = document.getElementById("newPassword");
  const toggleCurrentPasswordBtn = document.getElementById(
    "toggleCurrentPassword"
  );
  const toggleNewPasswordBtn = document.getElementById("toggleNewPassword");
  const emailResetError = document.getElementById("emailResetError");
  const currentPasswordError = document.getElementById("currentPasswordError");
  const newPasswordError = document.getElementById("newPasswordError");

  let isSuperAdmin = false; // Default role is Shop Owner

  // Clear error message when user types
  document.getElementById("email").addEventListener("input", function () {
    emailError.textContent = "";
  });
  passwordInput.addEventListener("input", function () {
    passwordError.textContent = "";
  });
  resetEmail.addEventListener("input", function () {
    emailResetError.textContent = "";
  });
  currentPasswordInput.addEventListener("input", function () {
    currentPasswordError.textContent = "";
  });
  newPasswordInput.addEventListener("input", function () {
    newPasswordError.textContent = "";
  });

  // Toggle between Shop Owner and SuperAdmin login
  toggleRole.addEventListener("click", function (event) {
    event.preventDefault();
    isSuperAdmin = !isSuperAdmin;

    if (isSuperAdmin) {
      loginTitle.textContent = "SuperAdmin Login";
      toggleRole.textContent = "Switch to Shop Owner";
      container.classList.add("switch-role");

      logoImage.classList.add("fade");
      setTimeout(() => {
        logoImage.src = "HAHA1.png";
        logoImage.classList.remove("fade");
      }, 250);
    } else {
      loginTitle.textContent = "Shop Owner Login";
      toggleRole.textContent = "Switch to SuperAdmin";
      container.classList.remove("switch-role");

      logoImage.classList.add("fade");
      setTimeout(() => {
        logoImage.src = "Bago.png";
        logoImage.classList.remove("fade");
      }, 250);
    }
  });

  // Toggle show/hide password functionality for login form
  togglePasswordBtn.addEventListener("click", function () {
    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      togglePasswordBtn.textContent = "Hide";
    } else {
      passwordInput.type = "password";
      togglePasswordBtn.textContent = "Show";
    }
  });

  // Show forgot password popup
  forgotPasswordLink.addEventListener("click", function (event) {
    event.preventDefault();
    forgotPasswordPopup.style.display = "flex";
    emailResetError.textContent = "";
    currentPasswordError.textContent = "";
    newPasswordError.textContent = "";
  });

  // Hide forgot password popup on cancel
  cancelResetBtn.addEventListener("click", function () {
    forgotPasswordPopup.style.display = "none";
    forgotPasswordForm.reset();
    emailResetError.textContent = "";
    currentPasswordError.textContent = "";
    newPasswordError.textContent = "";
  });

  // Toggle show/hide for current password
  toggleCurrentPasswordBtn.addEventListener("click", function () {
    if (currentPasswordInput.type === "password") {
      currentPasswordInput.type = "text";
      toggleCurrentPasswordBtn.textContent = "Hide";
    } else {
      currentPasswordInput.type = "password";
      toggleCurrentPasswordBtn.textContent = "Show";
    }
  });

  // Toggle show/hide for new password
  toggleNewPasswordBtn.addEventListener("click", function () {
    if (newPasswordInput.type === "password") {
      newPasswordInput.type = "text";
      toggleNewPasswordBtn.textContent = "Hide";
    } else {
      newPasswordInput.type = "password";
      toggleNewPasswordBtn.textContent = "Show";
    }
  });

  // Handle forgot password form submission
  forgotPasswordForm.addEventListener("submit", async function (event) {
    event.preventDefault();

    const email = resetEmail.value.trim();
    const currentPassword = currentPasswordInput.value.trim();
    const newPassword = newPasswordInput.value.trim();

    // Clear previous error messages
    emailResetError.textContent = "";
    currentPasswordError.textContent = "";
    newPasswordError.textContent = "";

    let hasError = false;

    if (!email) {
      emailResetError.textContent = "⚠️ Please enter your email.";
      hasError = true;
    }
    if (!currentPassword) {
      currentPasswordError.textContent =
        "⚠️ Please enter your current password.";
      hasError = true;
    }
    if (!newPassword) {
      newPasswordError.textContent = "⚠️ Please enter your new password.";
      hasError = true;
    }
    if (hasError) {
      return;
    }

    try {
      // Verify the current password
      const verifyData = {
        email: email,
        password: currentPassword,
        role: "shop_owner", // Assuming this is for shop owners
      };

      const verifyResponse = await fetch(
        "http://192.168.1.65/backend/authenticate.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(verifyData),
        }
      );

      if (!verifyResponse.ok) {
        throw new Error(`HTTP Error: ${verifyResponse.status}`);
      }

      const verifyResult = await verifyResponse.json();

      if (!verifyResult.success) {
        currentPasswordError.textContent = "⚠️ Current password is incorrect.";
        return;
      }

      // If current password is correct, proceed to update password
      const updateData = {
        email: email,
        new_password: newPassword,
        role: "shop_owner",
      };

      const updateResponse = await fetch(
        "http://192.168.1.65/backend/reset_password.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(updateData),
        }
      );

      if (!updateResponse.ok) {
        throw new Error(`HTTP Error: ${updateResponse.status}`);
      }

      const updateResult = await updateResponse.json();

      if (updateResult.success) {
        forgotPasswordPopup.style.display = "none";
        forgotPasswordForm.reset();
        alert("Password updated successfully!");
      } else {
        newPasswordError.textContent = "⚠️ " + updateResult.message;
      }
    } catch (error) {
      console.error("❌ Password reset failed:", error);
      newPasswordError.textContent =
        "❌ Error connecting to server. Please try again.";
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
        if (result.shop_owner_id) {
          localStorage.setItem("shop_owner_id", result.shop_owner_id);
        } else {
          console.error("❌ Error: shop_owner_id is missing in response.");
        }
        localStorage.setItem("shop_owner_token", result.token || "");
        localStorage.setItem("shop_owner_username", result.username || email);

        console.log("🔹 Stored Values:");
        console.log("shop_owner_id:", localStorage.getItem("shop_owner_id"));
        console.log(
          "shop_owner_token:",
          localStorage.getItem("shop_owner_token")
        );
        console.log(
          "shop_owner_username:",
          localStorage.getItem("shop_owner_username")
        );

        const popupOverlay = document.getElementById("popupOverlay");
        popupOverlay.style.display = "flex";

        setTimeout(() => {
          window.location.href = result.redirect;
        }, 1500);
      } else {
        const message = result.message.toLowerCase();
        if (message.includes("email") || message.includes("user")) {
          emailError.textContent = " " + result.message;
        } else if (message.includes("password")) {
          passwordError.textContent = " " + result.message;
        } else {
          passwordError.textContent = " " + result.message;
        }
      }
    } catch (error) {
      console.error("❌ Login failed:", error);
      passwordError.textContent =
        "❌ Error connecting to server. Please try again.";
    }
  });
});
