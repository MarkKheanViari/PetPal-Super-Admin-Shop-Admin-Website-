
        document.addEventListener('DOMContentLoaded', function () {
    // 🔒 Authentication check
    function checkAuth() {
        const token = localStorage.getItem('shop_owner_token');
        const username = localStorage.getItem('shop_owner_username');
        const userId = localStorage.getItem('shop_owner_id');

        if (!token || !username || !userId) {
            window.location.href = 'login.html';
            return false;
        }

        document.getElementById('welcomeText').textContent = `Welcome, ${username}`;
        return true;
    }

    // 🔑 Logout function
    function logout() {
        localStorage.removeItem('shop_owner_token');
        localStorage.removeItem('shop_owner_username');
        localStorage.removeItem('shop_owner_id');
        window.location.href = 'login.html';
    }

    // ❌ Show error message
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        document.body.insertBefore(errorDiv, document.body.firstChild);
        setTimeout(() => errorDiv.remove(), 5000);
    }

    // 📝 Handle product update submission
    document.getElementById('editProductForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);
    formData.append('shop_owner_id', localStorage.getItem('shop_owner_id'));

    try {
        const response = await fetch('http://192.168.34.203/backend/update_product.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();
        if (data.success) {
            alert('✅ Product updated successfully!');
            fetchProducts(); // Refresh products list
            cancelEdit(); // Close edit form
        } else {
            throw new Error(data.message || '❌ Failed to update product');
        }
    } catch (error) {
        console.error('❌ Error updating product:', error);
        alert('❌ Failed to update product: ' + error.message);
    }
});


    // 📌 Handle product addition (CREATE)
    document.getElementById('productForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('shop_owner_id', localStorage.getItem('shop_owner_id'));  // Attach shop owner ID

    try {
        const response = await fetch('http://192.168.34.203/backend/add_product.php', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();
        if (data.success) {
            alert('✅ Product added successfully!');
            e.target.reset();
            fetchProducts();  // Refresh the product list
        } else {
            throw new Error(data.error || 'Failed to add product');
        }
    } catch (error) {
        console.error('❌ Error adding product:', error);
        alert('❌ Failed to add product: ' + error.message);
    }
});


    if (checkAuth()) {
    filterProducts(); 
    fetchProducts();
    fetchServices();  // ✅ Make sure this line exists
    setInterval(fetchServices, 30000); // Refresh services every 30 seconds
}



});




// ✅ Add this logout function at the bottom of <script> in index.html
    function logout() {
    localStorage.removeItem('shop_owner_token');
    localStorage.removeItem('shop_owner_username');
    localStorage.removeItem('shop_owner_id');
    window.location.href = 'login.html'; // Redirect to login page after logout
}

// 📦 Fetch and display products
function fetchProducts() {
    const shopOwnerId = localStorage.getItem('shop_owner_id'); 
    const category = document.getElementById('categoryFilter').value;  // Get selected category
    if (!shopOwnerId) {
        console.error("❌ No shop owner ID found. Cannot fetch products.");
        return;
    }

    const url = `http://192.168.34.203/backend/fetch_product.php?shop_owner_id=${shopOwnerId}&category=${category}`;
    console.log(`Fetching products from: ${url}`);
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (!data.success || !Array.isArray(data.products)) {
                console.error("❌ Invalid response from server:", data);
                document.getElementById('productList').innerHTML = '<p>No products found.</p>';
                return;
            }
            displayProducts(data.products);
        })
        .catch(error => console.error('❌ Error fetching products:', error));
}



// ✅ Extracted function for better readability
function displayProducts(products) {
    const productList = document.getElementById('productList');
    productList.innerHTML = ''; // Clear previous content

    if (products.length === 0) {
        productList.innerHTML = '<p>No products available for the selected category.</p>';
        return;
    }

    products.forEach(product => {
        if (!product.id) {
            console.warn(`⚠️ Missing product ID for`, product);
            return;
        }

        const productItem = document.createElement('div');
        productItem.className = 'product-card';

        productItem.innerHTML = `
            <div class="product-header">
                <span class="price-badge">Price: ₱${product.price}</span>
                <button class="menu-btn" onclick="toggleMenu(${product.id})">⋮</button>
                
                <!-- Dropdown Menu -->
                <div class="menu-dropdown" id="menu-${product.id}" style="display: none;">
                    <button onclick="editProduct(${product.id}, '${product.name}', '${product.price}', 
                        '${product.description}', '${product.quantity}', '${product.image}')">Edit</button>
                    <button onclick="deleteProduct(${product.id})">Delete</button>
                </div>
            </div>
            <div class="product-image">
                <img src="${product.image.replace("\\", "")}" alt="Product Image">
            </div>
            <div class="product-details">
                <h3>${product.name}</h3>
                <p>In Stock: ${product.quantity}</p>
            </div>
        `;

        productList.appendChild(productItem);
    });

    console.log("✅ Products displayed successfully.");
}



// Toggle menu visibility
function toggleMenu(productId) {
    const menu = document.getElementById(`menu-${productId}`);
    
    if (menu.style.display === "none" || menu.style.display === "") {
        // Hide other open menus first
        document.querySelectorAll(".menu-dropdown").forEach(m => m.style.display = "none");
        menu.style.display = "block";
        
        // Close the menu when clicking outside
        document.addEventListener("click", function closeMenu(event) {
            if (!menu.contains(event.target) && !event.target.classList.contains("menu-btn")) {
                menu.style.display = "none";
                document.removeEventListener("click", closeMenu);
            }
        });
    } else {
        menu.style.display = "none";
    }
}






function toggleModal(modalId, show) {
    const modal = document.getElementById(modalId);
    modal.style.display = show ? "block" : "none";
}

// Auto-hide modals when clicking outside
window.onclick = function (event) {
    if (event.target.classList.contains("modal")) {
        event.target.style.display = "none";
    }
};




// Delete Product Function (Calls PHP API)
function deleteProduct(productId) {
    if (!confirm("Are you sure you want to delete this product?")) return;

    fetch("delete_product.php", {
        method: "POST",
        body: JSON.stringify({ product_id: productId }),
        headers: { "Content-Type": "application/json" }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("✅ Product deleted successfully!");
            displayProducts(data.products); // Refresh product list
        } else {
            alert("❌ Error deleting product: " + data.message);
        }
    })
    .catch(error => console.error("Error:", error));

    // Hide the menu after clicking delete
    const menu = document.getElementById(`menu-${productId}`);
    if (menu) menu.style.display = "none";
}

function editProduct(id, name, price, description, quantity, image) {
    document.getElementById('editProductId').value = id;
    document.getElementById('editProductName').value = name;
    document.getElementById('editProductPrice').value = price;
    document.getElementById('editProductDescription').value = description;
    document.getElementById('editProductQuantity').value = quantity;

    // Show product image in the edit form
    const imagePreview = document.getElementById('editProductImagePreview');
    if (imagePreview) {
        imagePreview.src = image;
        imagePreview.style.display = "block";
    }

    // Show the floating form
    toggleEditForm(true);

    // Hide the menu after clicking edit
    const menu = document.getElementById(`menu-${id}`);
    if (menu) menu.style.display = "none";
}






// Close Modal Function
function closeEditModal() {
    document.getElementById('editModal').style.display = "none";
}

// Save Changes Function
function saveEdit() {
    const id = document.getElementById('editProductId').value;
    const newName = document.getElementById('editProductName').value;
    const newPrice = document.getElementById('editProductPrice').value;
    const newDescription = document.getElementById('editProductDescription').value;
    const newQuantity = document.getElementById('editProductQuantity').value;
    const newImage = document.getElementById('editProductImage').files[0];

    if (!newName || !newPrice || !newDescription || !newQuantity) {
        alert("❌ All fields are required.");
        return;
    }

    const formData = new FormData();
    formData.append("product_id", id);
    formData.append("product_name", newName);
    formData.append("product_price", newPrice);
    formData.append("product_description", newDescription);
    formData.append("product_quantity", newQuantity);

    if (newImage) {
        formData.append("product_image", newImage);
    }

    fetch("update_product.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("✅ Product updated successfully!");
            closeEditModal();
            displayProducts(data.products); // Refresh product list
        } else {
            alert("❌ Error updating product: " + data.message);
        }
    })
    .catch(error => console.error("Error:", error));
}









// ✅ Move `fetchProducts()` above this function
function deleteProduct(productId) {
    if (!confirm("Are you sure you want to delete this product?")) {
        return;
    }

    const shopOwnerId = localStorage.getItem('shop_owner_id'); 

    fetch('http://192.168.34.203/backend/delete_product.php', {  
        method: 'POST',
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            product_id: parseInt(productId, 10),  
            shop_owner_id: parseInt(shopOwnerId, 10)
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log("Response received:", data);
        if (data.success) {
            alert('✅ Product deleted successfully!');

            // ✅ REMOVE PRODUCT FROM UI
            document.getElementById(`product-${productId}`)?.remove();

            // ✅ FORCE MOBILE APP TO REFRESH PRODUCTS
            fetch('http://192.168.34.203/backend/fetch_product.php?refresh=true')
                .then(() => console.log("Mobile app will fetch latest products"));

            // ✅ WAIT FOR 1 SECOND, THEN REFRESH LIST
            setTimeout(fetchProducts, 1000);
        } else {
            alert('❌ Failed to delete product: ' + data.error);
        }
    })
    .catch(error => {
        console.error('❌ Error deleting product:', error);
        alert('❌ Failed to delete product: ' + error.message);
    });
}

function fetchServices() {
    fetch('http://192.168.34.203/backend/fetch_services.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success || !Array.isArray(data.services)) {
                console.error("Invalid response format:", data);
                alert("Failed to load services.");
                return;
            }

            const services = data.services;
            const serviceList = document.getElementById('serviceList');
            serviceList.innerHTML = '';

            if (services.length === 0) {
                serviceList.innerHTML = '<p>No services available.</p>';
                return;
            }

            services.forEach(service => {
                const serviceItem = document.createElement('div');
                serviceItem.className = 'list-item';
                serviceItem.innerHTML = `
                    <strong>${service.service_name}</strong> - $${service.price}
                    <p>${service.description}</p>
                    <p>Status: <span class="status-${service.status.toLowerCase()}">${service.status}</span></p>
                `;
                serviceList.appendChild(serviceItem);
            });
        })
        .catch(error => {
            console.error('❌ Error fetching services:', error);
            alert("Failed to fetch services: " + error.message);
        });
}


// Call fetchServices when the page loads
document.addEventListener('DOMContentLoaded', () => {
    if (typeof fetchServices === "function") {
        fetchServices();
    } else {
        console.error("❌ fetchServices function is not defined!");
    }
});

document.getElementById('serviceForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(e.target);
    formData.append('shop_owner_id', localStorage.getItem('shop_owner_id')); // Attach shop owner ID

    try {
        const response = await fetch('http://192.168.34.203/backend/add_service.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();
        if (data.success) {
            alert('✅ Service added successfully!');
            e.target.reset();
            fetchServices(); // Refresh service list after adding
        } else {
            throw new Error(data.error || 'Failed to add service');
        }
    } catch (error) {
        console.error('❌ Error adding service:', error);
        alert('❌ Failed to add service: ' + error.message);
    }
});

function fetchServiceRequests() {
    console.log("🔄 Fetching Service Requests...");

    fetch("http://192.168.34.203/backend/fetch_service_request.php")
        .then(response => response.json())
        .then(data => {
            console.log("🔄 Service Requests Data:", data);

            let serviceRequestsContainer = document.getElementById("serviceRequestsContainer");

            if (!serviceRequestsContainer) {
                console.error("❌ ERROR: Element with ID 'serviceRequestsContainer' not found!");
                return;
            }

            serviceRequestsContainer.innerHTML = ""; // Clear previous data

            if (!Array.isArray(data) || data.length === 0) {
                serviceRequestsContainer.innerHTML = "<p>No pending service requests.</p>";
                return;
            }

            data.forEach(request => {
                let requestElement = document.createElement("div");
                requestElement.classList.add("list-item");
                requestElement.innerHTML = `
                    <div class="service-info">
                        <strong>Service:</strong> ${request.service_name} <br>
                        <strong>User:</strong> ${request.user_name} <br>
                        <strong>Date:</strong> ${request.selected_date} <br>
                        <strong>Status:</strong> <span class="status-${request.status.toLowerCase()}">${request.status}</span>
                    </div>
                    <div class="service-actions">
                        <button class="confirm-btn" onclick="updateRequestStatus(${request.id}, 'confirmed')">Approve</button>
                        <button class="decline-btn" onclick="updateRequestStatus(${request.id}, 'declined')">Decline</button>
                    </div>
                `;
                serviceRequestsContainer.appendChild(requestElement);
            });
        })
        .catch(error => console.error("❌ Error fetching service requests:", error));
}







// Ensure function only runs once on page load
document.addEventListener('DOMContentLoaded', () => {
    fetchServiceRequests(); 
    setInterval(fetchServiceRequests, 30000); // Refresh every 30 seconds
});



function displayServiceRequests(requests) {
    const serviceRequestList = document.getElementById('serviceRequestList');
    serviceRequestList.innerHTML = '';

    if (!Array.isArray(requests) || requests.length === 0) {
        serviceRequestList.innerHTML = '<p>No pending service requests.</p>';
        return;
    }

    requests.forEach(request => {
        const requestItem = document.createElement('div');
        requestItem.className = 'list-item';
        requestItem.innerHTML = `
            <div class="service-info">
                <strong>Service:</strong> ${request.service_name} <br>
                <strong>Customer:</strong> ${request.user_name} <br>
                <strong>Date:</strong> ${request.selected_date} <br>
                <strong>Status:</strong> <span class="status-${request.status.toLowerCase()}">${request.status}</span>
            </div>
            <div class="service-actions">
                <button class="confirm-btn" onclick="updateRequestStatus(${request.id}, 'confirmed')">Confirm</button>
                <button class="decline-btn" onclick="updateRequestStatus(${request.id}, 'declined')">Decline</button>
            </div>
        `;
        serviceRequestList.appendChild(requestItem);
    });
}

// ✅ Ensure function is outside DOMContentLoaded
function showToast(message, isError = false) {
    const toast = document.getElementById("toast");
    toast.textContent = message;
    toast.classList.remove("hidden");
    toast.classList.toggle("error", isError);

    setTimeout(() => {
        toast.classList.add("hidden");
    }, 3000); // Hide after 3 seconds
}

async function updateRequestStatus(id, status) {
    console.log(`📤 Updating Request ID ${id} to ${status}...`);

    try {
        const response = await fetch("http:///backend/update_service_status.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id: id, status: status })
        });

        const responseBody = await response.text(); // ✅ Debug response
        console.log("🔄 Raw Response:", responseBody);

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = JSON.parse(responseBody);
        console.log("✅ Update Response:", data);

        if (data.success) {
            showToast(`✅ Service request ${status} successfully!`);
            fetchServiceRequests(); // Refresh the service requests list
        } else {
            throw new Error(data.message || "Failed to update status");
        }
    } catch (error) {
        console.error("❌ Error updating status:", error);
        showToast("❌ Failed to update status: " + error.message, true);
    }
}

// ✅ Ensure function runs after page load
document.addEventListener('DOMContentLoaded', () => {
    fetchServiceRequests();
    setInterval(fetchServiceRequests, 30000); // Refresh every 30 seconds
});

function filterProducts() {
    const category = document.getElementById('categoryFilter').value;
    const shopOwnerId = localStorage.getItem('shop_owner_id');

    const url = `http://192.168.34.203/backend/fetch_product.php?shop_owner_id=${shopOwnerId}&category=${category}`;
    console.log(`Fetching products from: ${url}`);

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayProducts(data.products);
            } else {
                document.getElementById('productList').innerHTML = '<p>No products found.</p>';
            }
        })
        .catch(error => console.error('❌ Error fetching products:', error));
}

// Toggle Add Product Form
function toggleAddForm(show) {
    document.getElementById("addProductSection").style.display = show ? "block" : "none";
}

function toggleEditForm(show) {
    const editSection = document.getElementById("editProductSection");
    const overlay = document.getElementById("editOverlay");

    if (!editSection || !overlay) {
        console.error("❌ Error: Edit product form or overlay not found.");
        return;
    }

    if (show) {
        editSection.style.display = "block";
        overlay.style.display = "block";
    } else {
        editSection.style.display = "none";
        overlay.style.display = "none";
    }
}





 