document.addEventListener("DOMContentLoaded", function () {
    const shopOwnerForm = document.getElementById("shop-owner-form");
    const superAdminForm = document.getElementById("super-admin-form");
    const imageSection = document.querySelector(".image-section");
    const formSection = document.querySelector(".form-section");
    const loginImage = document.getElementById("loginImage");

    const switchToAdmin = document.getElementById("switchToAdmin");
    const switchToShopOwner = document.getElementById("switchToShopOwner");

    switchToAdmin.addEventListener("click", function (event) {
        event.preventDefault();

        // Move image left and form right
        imageSection.classList.add("move-left");
        formSection.classList.add("move-right");

        // Smooth fade out current form
        shopOwnerForm.classList.add("move-left-form");

        setTimeout(() => {
            loginImage.src = "super-admin-login.jpg";
            shopOwnerForm.classList.add("hidden");
            superAdminForm.classList.remove("hidden");

            // Reset animation class for smooth blending
            formSection.classList.remove("move-left");
            shopOwnerForm.classList.remove("move-left-form");
        }, 400); // Reduced delay to improve blending
    });

    switchToShopOwner.addEventListener("click", function (event) {
        event.preventDefault();

        // Move image right and bring form back
        imageSection.classList.remove("move-left");
        formSection.classList.remove("move-right");

        // Smooth fade out current form
        superAdminForm.classList.add("move-left-form");

        setTimeout(() => {
            loginImage.src = "shop-owner-login.jpg";
            superAdminForm.classList.add("hidden");
            shopOwnerForm.classList.remove("hidden");

            // Reset animation class for smooth blending
            formSection.classList.remove("move-left");
            superAdminForm.classList.remove("move-left-form");
        }, 400);
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
