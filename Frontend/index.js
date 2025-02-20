
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
                fetchProducts(); // Refresh product list
                
                // Close the modal after updating
                toggleEditForm(false);
            } else {
                throw new Error(data.message || '❌ Failed to update product');
            }
        } catch (error) {
            console.error('❌ Error updating product:', error);
            alert('❌ Failed to update product: ' + error.message);
        }
    });
    


document.getElementById('productForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('shop_owner_id', localStorage.getItem('shop_owner_id'));  

    try {
        const response = await fetch('http://192.168.34.203/backend/add_product.php', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();
        if (data.success) {
            alert('✅ Product added successfully!');
            e.target.reset();
            fetchProducts();  // Refresh product list
            
            // Close the modal after adding
            toggleModal('addProductModal', false);
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
    
    if (modal) {
        modal.style.display = show ? "flex" : "none";

        if (show) {
            document.body.classList.add("modal-open"); // Disable scrolling
        } else {
            // Check if ANY modal is still open before enabling scrolling
            const openModals = document.querySelectorAll('.modal[style*="display: flex"]');
            if (openModals.length === 0) {
                document.body.classList.remove("modal-open"); // Restore scrolling only if all modals are closed
            }
        }
    }
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

    // Open the edit form and disable background scrolling
    toggleEditForm(true);
}

function closeEditForm() {
    toggleEditForm(false); // Closes the form and enables scrolling
}
// Function to calculate scrollbar width
function getScrollbarWidth() {
    return window.innerWidth - document.documentElement.clientWidth;
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

function viewOrderDetails(orderId) {
    fetch(`http://localhost/backend/fetch_order_details.php?order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("customerName").value = data.order.username;
                document.getElementById("customerAddress").value = data.order.location || "No Address Provided";
                document.getElementById("customerContact").value = data.order.contact_number || "No Contact Info";
                document.getElementById("paymentMethod").value = data.order.payment_method;
                document.getElementById("orderAmount").value = `₱${parseFloat(data.order.total_price).toFixed(2)}`;

                // Show modal and prevent scrolling
                toggleModal("orderModal", true);
            } else {
                alert("❌ Error fetching order details");
            }
        })
        .catch(error => console.error("❌ ERROR Fetching Order Details:", error));
}


function closeModal() {
    toggleModal("orderModal", false); // Close modal and re-enable scrolling
}








function closeModal() {
    document.getElementById("orderModal").style.display = "none";
}




// Close modal function
function closeModal() {
    document.getElementById("orderModal").style.display = "none";
}

// Approve Order (Dummy function for now)
//function approveOrder() {
    //alert("✅ Order Approved!");
    //closeModal();
//}



document.addEventListener("DOMContentLoaded", function () {
    fetchOrders();
});

function fetchOrders() {
    fetch("http://192.168.34.203/backend/fetch_orders.php") // ✅ Adjust backend endpoint if needed
        .then(response => response.json())
        .then(data => {
            console.log("📦 Orders Data:", data); // ✅ Debugging log
            if (data.success) {
                let ordersContainer = document.getElementById("ordersContainer");
                ordersContainer.innerHTML = ""; // Clear previous content

                data.orders.forEach(order => {
                    let orderElement = document.createElement("div");
                    orderElement.classList.add("order-item");
                    orderElement.innerHTML = `
                        <p><strong>Customer:</strong> ${order.customer_name}</p>
                        <p><strong>Total Price:</strong> ₱${order.total_price}</p>
                        <p><strong>Quantity:</strong> ${order.quantity}</p>
                        <button class="view-details-btn" onclick="viewOrderDetails(${order.order_id})">
                            View Details
                        </button>
                    `;
                    ordersContainer.appendChild(orderElement);
                });
            } else {
                console.error("❌ Failed to load orders.");
            }
        })
        .catch(error => console.error("❌ ERROR Fetching Orders:", error));
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

    if (!editSection) {
        console.error("❌ Error: Edit product form not found.");
        return;
    }

    if (show) {
        document.documentElement.style.setProperty('--scrollbar-width', `${getScrollbarWidth()}px`);
        editSection.style.display = "block";
        document.body.classList.add("modal-open");
    } else {
        document.documentElement.style.setProperty('--scrollbar-width', '0px');
        editSection.style.display = "none";
        document.body.classList.remove("modal-open");
    }
}



document.addEventListener("DOMContentLoaded", function () {
    // Hide all modals on page load
    document.querySelectorAll(".modal, .overlay, .floating-form, .floating-overlay").forEach(modal => {
        modal.style.display = "none";
    });

    // Restore scroll when no modal is open
    if (!document.body.classList.contains("modal-open")) {
        document.body.style.overflow = "auto";
    }


    
    // Fetch orders & products
    fetchOrders();
    fetchProducts();
});

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".modal .close-btn, .modal .cancel-btn").forEach(button => {
        button.addEventListener("click", function () {
            document.body.classList.remove("modal-open"); // Restore scroll when closing modal
        });
    });
});


document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".modal .close-btn, .modal .cancel-btn").forEach(button => {
        button.addEventListener("click", function () {
            toggleModal(button.closest(".modal").id, false); // Close modal properly
        });
    });

    // Ensure scroll restores after modals are closed
    document.querySelectorAll(".modal").forEach(modal => {
        modal.addEventListener("click", function (event) {
            if (event.target === modal) { // Close modal when clicking outside
                toggleModal(modal.id, false);
            }
        });
    });
});

function toggleModal(modalId, show) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = show ? "flex" : "none";
        document.body.classList.toggle("modal-open", show);
    }
}

function toggleEditForm(show) {
    const editSection = document.getElementById("editProductSection");
    if (!editSection) return;
    editSection.style.display = show ? "block" : "none";
    document.body.classList.toggle("modal-open", show);
}



 