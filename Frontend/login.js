document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("loginForm");
    const toggleRole = document.getElementById("toggleRole");
    const loginTitle = document.getElementById("loginTitle");
    const container = document.querySelector(".container");

    let isSuperAdmin = false; // Default role is Shop Owner

    // Toggle between Shop Owner and SuperAdmin login
    toggleRole.addEventListener("click", function (event) {
        event.preventDefault();
        isSuperAdmin = !isSuperAdmin;

        // Remove previous class immediately and force a reflow before adding the new one
        container.classList.remove("reverse");
        void container.offsetWidth; // This forces reflow

        if (isSuperAdmin) {
            loginTitle.textContent = "SuperAdmin Login";
            toggleRole.textContent = "Switch to Shop Owner";
            container.classList.add("switch-role"); // Move login left & reappear from right
        } else {
            loginTitle.textContent = "Shop Owner Login";
            toggleRole.textContent = "Switch to SuperAdmin";
            
            // Add 'reverse' class after transition ends for proper reappearance effect
            setTimeout(() => {
                container.classList.add("reverse");
            }, 600); // Wait for transition to complete
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
            const response = await fetch("http://192.168.1.65/backend/authenticate.php", {
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
