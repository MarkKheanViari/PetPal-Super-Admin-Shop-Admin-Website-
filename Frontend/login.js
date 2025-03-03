document.addEventListener("DOMContentLoaded", function () {
    const shopOwnerForm = document.getElementById("shop-owner-form");
    const superAdminForm = document.getElementById("super-admin-form");
    const imageSection = document.querySelector(".image-section");
    const loginImage = document.getElementById("loginImage");

    const switchToAdmin = document.getElementById("switchToAdmin");
    const switchToShopOwner = document.getElementById("switchToShopOwner");

    switchToAdmin.addEventListener("click", function (event) {
        event.preventDefault();
        console.log("Switching to Super Admin form");
    
        // Show Super Admin Form & Hide Shop Owner Form
        shopOwnerForm.classList.add("hidden"); // Use class to hide
        superAdminForm.classList.remove("hidden"); // Use class to show
    
        // Move the image section left
        imageSection.classList.add("move-left");
    
        // Change the image after transition
        setTimeout(() => {
            loginImage.src = "super-admin-login.jpg";
        }, 250);
    });
    
    switchToShopOwner.addEventListener("click", function (event) {
        event.preventDefault();
        console.log("Switching to Shop Owner form");
    
        // Show Shop Owner Form & Hide Super Admin Form
        superAdminForm.classList.add("hidden"); // Use class to hide
        shopOwnerForm.classList.remove("hidden"); // Use class to show
    
        // Move the image section back
        imageSection.classList.remove("move-left");
    
        // Change the image back
        setTimeout(() => {
            loginImage.src = "shop-owner-login.jpg";
        }, 250);
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

            if (!response.ok) {
                throw new Error(`HTTP Error: ${response.status}`);
            }

            const result = await response.json();
            console.log("🔍 Login Response:", result);

            if (result.success) {
                alert("✅ Login Successful!");

                // ✅ Store shop owner details
                if (result.shop_owner_id) {
                    localStorage.setItem("shop_owner_id", result.shop_owner_id);
                } else {
                    console.error("❌ Error: shop_owner_id is missing in response.");
                }

                localStorage.setItem("shop_owner_token", result.token || ""); // ✅ Store token
                localStorage.setItem("shop_owner_username", result.username || email);

                console.log("🔹 Stored Values:");
                console.log("shop_owner_id:", localStorage.getItem("shop_owner_id"));
                console.log("shop_owner_token:", localStorage.getItem("shop_owner_token"));
                console.log("shop_owner_username:", localStorage.getItem("shop_owner_username"));

                window.location.href = result.redirect;
            } else {
                alert("❌ Error: " + result.message);
            }
        } catch (error) {
            console.error("❌ Login failed:", error);
            alert("❌ Error connecting to server. Please try again.");
        }
    });
});
