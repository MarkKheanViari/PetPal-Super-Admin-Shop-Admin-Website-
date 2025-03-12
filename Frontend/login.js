document.addEventListener("DOMContentLoaded", function () {
  const loginForm = document.getElementById("loginForm");
  const toggleRole = document.getElementById("toggleRole");
  const loginTitle = document.getElementById("loginTitle");
  const container = document.querySelector(".container");
  const dialogueBox = document.getElementById("dialogueBox"); // a dialogue container in your HTML

  let isSuperAdmin = false; // Default role is Shop Owner

  // Function to show dialogue messages with different styles based on type (e.g., "success", "error", "info")
  // The autoHide parameter controls whether the dialogue should auto-disappear.
  function showDialogue(message, type = "info", autoHide = true) {
    dialogueBox.textContent = message;
    dialogueBox.className = "";
    dialogueBox.classList.add("dialogue", type);
    dialogueBox.style.display = "block";

    if (autoHide) {
      setTimeout(() => {
        dialogueBox.style.display = "none";
      }, 3000);
    }
  }

  // Toggle between Shop Owner and SuperAdmin login
  toggleRole.addEventListener("click", function (event) {
    event.preventDefault();
    isSuperAdmin = !isSuperAdmin;

    container.classList.remove("reverse");
    void container.offsetWidth; // Forces reflow

    if (isSuperAdmin) {
      loginTitle.textContent = "SuperAdmin Login";
      toggleRole.textContent = "Switch to Shop Owner";
      container.classList.add("switch-role");
    } else {
      loginTitle.textContent = "Shop Owner Login";
      toggleRole.textContent = "Switch to SuperAdmin";
      setTimeout(() => {
        container.classList.add("reverse");
      }, 600);
    }
  });

  // Handle login form submission
  loginForm.addEventListener("submit", async function (event) {
    event.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();

    if (!email || !password) {
      showDialogue("⚠️ Please enter both email and password.", "error");
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
        // Show success message without auto hiding
        showDialogue("✅ Login Successful!", "success", false);

        // Store shop owner details
        if (result.shop_owner_id) {
          localStorage.setItem("shop_owner_id", result.shop_owner_id);
        } else {
          console.error("❌ Error: shop_owner_id is missing in response.");
        }
        localStorage.setItem("shop_owner_token", result.token || ""); // Store token
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

        // Delay redirection so the user sees the message until dashboard loads.
        setTimeout(() => {
          window.location.href = result.redirect;
        }, 1500);
      } else {
        showDialogue("❌ Error: " + result.message, "error");
      }
    } catch (error) {
      console.error("❌ Login failed:", error);
      showDialogue("❌ Error connecting to server. Please try again.", "error");
    }
  });
});
