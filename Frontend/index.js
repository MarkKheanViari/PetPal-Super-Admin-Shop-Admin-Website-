document.addEventListener("DOMContentLoaded", function () {
  function checkAuth() {
    const token = localStorage.getItem("shop_owner_token");
    const username = localStorage.getItem("shop_owner_username");
    const userId = localStorage.getItem("shop_owner_id");

    console.log("🔍 Checking Auth:");
    console.log("token:", token);
    console.log("username:", username);
    console.log("userId:", userId);

    if (!userId || !token) {
      // ✅ Ensure both shop_owner_id and token exist
      console.warn("⚠️ Authentication failed. Redirecting to login.");
      window.location.href = "login.html";
      return false;
    }

    const welcomeText = document.getElementById("welcomeText");
    if (welcomeText) {
      welcomeText.textContent = `Welcome, ${username}`;
    }
    return true;
  }

  function logout() {
    localStorage.clear();
    console.log("🔴 User logged out. Redirecting to login.");
    window.location.href = "login.html";
  }

  if (checkAuth()) {
    fetchProducts();
  }

  document.querySelector(".logout-btn")?.addEventListener("click", logout);

  // ❌ Show error message
  function showError(message) {
    const errorDiv = document.createElement("div");
    errorDiv.className = "error-message";
    errorDiv.textContent = message;
    document.body.insertBefore(errorDiv, document.body.firstChild);
    setTimeout(() => errorDiv.remove(), 5000);
  }

  const editProductForm = document.getElementById("editProductForm");
  if (editProductForm) {
    editProductForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);
  
      // Validate price input (must be > 0)
      const priceValue = parseFloat(document.getElementById("editProductPrice").value);
      if (priceValue <= 0) {
        alert("❌ Price must be greater than 0.");
        return;
      }
      // Validate quantity input (must be > 0)
      const quantityValue = parseFloat(document.getElementById("editProductQuantity").value);
      if (quantityValue <= 0) {
        alert("❌ Quantity must be greater than 0.");
        return;
      }
  
      const selectedCategory = document.getElementById("editProductCategory").value;
      formData.append("category", selectedCategory);
  
      const shopOwnerId = localStorage.getItem("shop_owner_id");
      formData.append("shop_owner_id", shopOwnerId);
  
      // Append new image file if selected; otherwise, preserve existing image
      const newImageFile = document.getElementById("editProductImage").files[0];
      if (newImageFile) {
        formData.append("product_image", newImageFile);
      } else {
        const existingImageValue = document.getElementById("existingImage").value;
        formData.append("existing_image", existingImageValue);
      }

  
      console.log("🔍 Form Data Before Sending:");
      for (const [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
      }
  
      try {
        const response = await fetch("http://192.168.137.14/backend/update_product.php", {
          method: "POST",
          body: formData,
        });
  
        const data = await response.json();
        console.log("✅ Server Response:", data);
  
        if (data.success) {
          alert("✅ Product updated successfully!");
          fetchProducts(); // Refresh product list
          toggleEditForm(false); // Close modal
        } else {
          console.error("❌ Failed to update product:", data.message);
        }
      } catch (error) {
        console.error("❌ Error updating product:", error);
      }
    });
  }
  

  // 📝 Handle product add submission (only if form exists)
  const productForm = document.getElementById("productForm");
  if (productForm) {
    productForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      const formData = new FormData(e.target);
  
      // Validate the price input
      const priceInput = e.target.querySelector('[name="product_price"]');
      if (!priceInput) {
        alert("❌ Price input field not found. Please check your form markup.");
        return;
      }
      const priceValue = parseFloat(priceInput.value);
      if (priceValue <= 0) {
        alert("❌ Price must be greater than 0.");
        return;
      }
  
      // Validate the quantity input
      const quantityInput = e.target.querySelector('[name="product_quantity"]');
      if (!quantityInput) {
        alert("❌ Quantity input field not found. Please check your form markup.");
        return;
      }
      const quantityValue = parseFloat(quantityInput.value);
      if (quantityValue <= 0) {
        alert("❌ Quantity must be greater than 0.");
        return;
      }
  
      // Now append your other form data
      formData.append("shop_owner_id", localStorage.getItem("shop_owner_id"));
  
      try {
        const response = await fetch("http://192.168.137.14/backend/add_product.php", {
          method: "POST",
          body: formData,
        });
  
        const data = await response.json();
        if (data.success) {
          alert("✅ Product added successfully!");
          e.target.reset();
          fetchProducts(); // Refresh product list
          toggleModal("addProductModal", false); // Close the modal
        } else {
          throw new Error(data.error || "Failed to add product");
        }
      } catch (error) {
        console.error("❌ Error adding product:", error);
        alert("❌ Failed to add product: " + error.message);
      }
    });
  }
  


  if (checkAuth()) {
    filterProducts();
    fetchProducts();
  }
});

// 🔑 Global logout function
function logout() {
  localStorage.removeItem("shop_owner_token");
  localStorage.removeItem("shop_owner_username");
  localStorage.removeItem("shop_owner_id");
  window.location.href = "login.html"; // Redirect to login page after logout
}
// 📦 Fetch and display products
function fetchProducts() {
  const shopOwnerId = localStorage.getItem("shop_owner_id");
  const categoryElement = document.getElementById("categoryFilter");
  if (!categoryElement) {
    console.warn("Category filter element not found.");
    return;
  }
  const category = categoryElement.value;

  if (!shopOwnerId) {
    console.error("❌ No shop owner ID found. Cannot fetch products.");
    return;
  }

  const url = `http://192.168.137.14/backend/fetch_product.php?shop_owner_id=${shopOwnerId}&category=${category}`;
  console.log(`Fetching products from: ${url}`);

  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      if (!data.success || !Array.isArray(data.products)) {
        console.error("❌ Invalid response from server:", data);
        const productList = document.getElementById("productList");
        if (productList) productList.innerHTML = "<p>No products found.</p>";
        return;
      }
      displayProducts(data.products);
    })
    .catch((error) => console.error("❌ Error fetching products:", error));
}

// Toggle menu visibility
function toggleMenu(productId) {
  const menu = document.getElementById(`menu-${productId}`);

  if (menu.style.display === "none" || menu.style.display === "") {
    // Hide other open menus first
    document
      .querySelectorAll(".menu-dropdown")
      .forEach((m) => (m.style.display = "none"));
    menu.style.display = "block";

    // Close the menu when clicking outside
    document.addEventListener("click", function closeMenu(event) {
      if (
        !menu.contains(event.target) &&
        !event.target.classList.contains("menu-btn")
      ) {
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
      const openModals = document.querySelectorAll(
        '.modal[style*="display: flex"]'
      );
      if (openModals.length === 0) {
        document.body.classList.remove("modal-open"); // Restore scrolling only if all modals are closed
      }
    }
  }
}

window.onclick = function (event) {
  if (event.target.classList.contains("modal")) {
    event.target.style.display = "none";
  }
};

async function deleteProduct(productId) {
  // Show confirmation dialog and wait for the user's response.
  const confirmed = await confirm("Are you sure you want to delete this product?");
  if (!confirmed) {
    return; // If the user clicks Cancel, exit without deleting.
  }

  const shopOwnerId = localStorage.getItem("shop_owner_id");

  fetch("http://192.168.137.14/backend/delete_product.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      product_id: parseInt(productId, 10),
      shop_owner_id: parseInt(shopOwnerId, 10)
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("✅ Product deleted successfully!");
        // Optionally, remove the product from the UI directly:
        document.getElementById(`product-${productId}`)?.remove();
        // Or refresh the product list:
        setTimeout(fetchProducts, 1000);
      } else {
        alert("❌ Failed to delete product: " + data.error);
      }
    })
    .catch((error) => {
      console.error("❌ Error deleting product:", error);
      alert("❌ Failed to delete product: " + error.message);
    });
}





function editProduct(id, name, price, description, quantity, image, category) {
  console.log(`🔍 Editing Product ID: ${id}`);
  console.log(
    `📌 Name: ${name}, Price: ${price}, Desc: ${description}, Quantity: ${quantity}, Category: ${category}, Image: ${image}`
  );

  // Set form fields with existing product data
  document.getElementById("editProductId").value = id;
  document.getElementById("editProductName").value = name;
  document.getElementById("editProductPrice").value = price;
  document.getElementById("editProductDescription").value = description;
  document.getElementById("editProductQuantity").value = quantity;

  // ✅ Fix Category Selection
  const categoryDropdown = document.getElementById("editProductCategory");
  if (categoryDropdown) {
    let optionExists = false;
    for (let option of categoryDropdown.options) {
      if (option.value === category) {
        option.selected = true;
        optionExists = true;
        break;
      }
    }
    if (!optionExists) {
      console.warn(`⚠️ Category "${category}" not found in dropdown.`);
    }
  } else {
    console.error("❌ Category dropdown not found.");
  }

  // ✅ Preserve existing image
  document.getElementById("existingImage").value = image;

  // ✅ Display Image Preview
  const imagePreview = document.getElementById("editProductImagePreview");
  if (imagePreview) {
    imagePreview.src = image;
    imagePreview.style.display = "block";
  }

  // Show modal
  toggleEditForm(true);
}

function closeEditForm() {
  toggleEditForm(false); // Closes the form and enables scrolling
}

function getScrollbarWidth() {
  return window.innerWidth - document.documentElement.clientWidth;
}

function saveEdit() {
  const id = document.getElementById("editProductId").value;
  const newName = document.getElementById("editProductName").value;
  const newPrice = document.getElementById("editProductPrice").value;
  const newDescription = document.getElementById(
    "editProductDescription"
  ).value;
  const newQuantity = document.getElementById("editProductQuantity").value;
  const newImage = document.getElementById("editProductImage").files[0];

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
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("✅ Product updated successfully!");
        closeEditModal();
        displayProducts(data.products); // Refresh product list
      } else {
        alert("❌ Error updating product: " + data.message);
      }
    })
    .catch((error) => console.error("Error:", error));
}

function closeModal() {
  toggleModal("orderModal", false);
}

function closeModal() {
  document.getElementById("orderModal").style.display = "none";
}

function closeModal() {
  document.getElementById("orderModal").style.display = "none";
}

document.addEventListener("DOMContentLoaded", function () {
  fetchOrders();
});

function fetchOrders() {
  fetch("http://192.168.137.14/backend/fetch_orders.php")
    .then((response) => response.json())
    .then((data) => {
      const ordersContainer = document.getElementById("ordersContainer");

      if (!ordersContainer) {
        console.warn(
          "ordersContainer element not found. Skipping orders update."
        );
        return;
      }

      ordersContainer.innerHTML = "";

      if (data.success) {
        data.orders.forEach((order) => {
          const orderElement = document.createElement("div");
          orderElement.classList.add("order-item");
          orderElement.innerHTML = `
                <p><strong>Customer:</strong> ${order.customer_name}</p>
                <p><strong>Total Price:</strong> ₱${order.total_price}</p>
                <p><strong>Quantity:</strong> ${order.quantity}</p>
                <button class="view-details-btn" onclick="viewOrderDetails(${order.order_id})">View Details</button>
              `;
          ordersContainer.appendChild(orderElement);
        });
      } else {
        ordersContainer.innerHTML = `<p>No orders found.</p>`;
      }
    })
    .catch((error) => console.error("❌ ERROR Fetching Orders:", error));
}

async function deleteProduct(productId) {
  // Show confirmation dialog and wait for the user's response.
  const confirmed = await confirm("Are you sure you want to delete this product?");
  if (!confirmed) {
    return; // If the user clicks Cancel, exit without deleting.
  }

  const shopOwnerId = localStorage.getItem("shop_owner_id");

  fetch("http://192.168.137.14/backend/delete_product.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      product_id: parseInt(productId, 10),
      shop_owner_id: parseInt(shopOwnerId, 10)
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("✅ Product deleted successfully!");
        // Optionally, remove the product from the UI directly:
        document.getElementById(`product-${productId}`)?.remove();
        // Or refresh the product list:
        setTimeout(fetchProducts, 1000);
      } else {
        alert("❌ Failed to delete product: " + data.error);
      }
    })
    .catch((error) => {
      console.error("❌ Error deleting product:", error);
      alert("❌ Failed to delete product: " + error.message);
    });
}


function showToast(message, isError = false) {
  const toast = document.getElementById("toast");
  toast.textContent = message;
  toast.classList.remove("hidden");
  toast.classList.toggle("error", isError);

  setTimeout(() => {
    toast.classList.add("hidden");
  }, 3000);
}

function filterProducts() {
  const categoryElement = document.getElementById("categoryFilter");
  if (!categoryElement) {
    console.warn("⚠️ Category filter element not found.");
    return;
  }
  const category = categoryElement.value;
  const shopOwnerId = localStorage.getItem("shop_owner_id");

  if (!shopOwnerId) {
    console.error("❌ No shop owner ID found. Cannot filter products.");
    return;
  }

  let url = `http://192.168.137.14/backend/fetch_product.php?shop_owner_id=${shopOwnerId}`;
  if (category !== "all") {
    url += `&category=${encodeURIComponent(category)}`;
  }

  console.log(`🔄 Fetching filtered products from: ${url}`);

  fetch(url)
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayProducts(data.products);
      } else {
        console.warn("⚠️ No products found for this category.");
        const productList = document.getElementById("productList");
        if (productList) productList.innerHTML = "<p>No products found.</p>";
      }
    })
    .catch((error) => console.error("❌ Error fetching products:", error));
}

function toggleAddForm(show) {
  document.getElementById("addProductSection").style.display = show
    ? "block"
    : "none";
}

function toggleEditForm(show) {
  const editSection = document.getElementById("editProductSection");
  if (!editSection) {
    console.error("❌ Error: Edit product form not found.");
    return;
  }

  if (show) {
    document.documentElement.style.setProperty(
      "--scrollbar-width",
      `${getScrollbarWidth()}px`
    );
    editSection.style.display = "block";
    document.body.classList.add("modal-open");
  } else {
    document.documentElement.style.setProperty("--scrollbar-width", "0px");
    editSection.style.display = "none";
    document.body.classList.remove("modal-open");
  }
}

document.addEventListener("DOMContentLoaded", function () {
  document
    .querySelectorAll(".modal, .overlay, .floating-form, .floating-overlay")
    .forEach((modal) => {
      modal.style.display = "none";
    });

  if (!document.body.classList.contains("modal-open")) {
    document.body.style.overflow = "auto";
  }

  fetchOrders();
  fetchProducts();
});

document.addEventListener("DOMContentLoaded", function () {
  document
    .querySelectorAll(".modal .close-btn, .modal .cancel-btn")
    .forEach((button) => {
      button.addEventListener("click", function () {
        document.body.classList.remove("modal-open");
      });
    });
});

document.addEventListener("DOMContentLoaded", function () {
  document
    .querySelectorAll(".modal .close-btn, .modal .cancel-btn")
    .forEach((button) => {
      button.addEventListener("click", function () {
        toggleModal(button.closest(".modal").id, false);
      });
    });

  document.querySelectorAll(".modal").forEach((modal) => {
    modal.addEventListener("click", function (event) {
      if (event.target === modal) {
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

function showProductPreview(product) {
  let imagePath = product.image;
  if (!imagePath.startsWith("http")) {
      imagePath = `http://192.168.137.14/backend/uploads/${imagePath}`;
  }
  document.querySelector("img").src = imagePath;


  const previewModal = document.getElementById("previewModal");
  const previewName = document.getElementById("previewName");
  const previewDescription = document.getElementById("previewDescription");
  const previewPrice = document.getElementById("previewPrice");
  const previewQuantity = document.getElementById("previewQuantity");
  const previewCategory = document.getElementById("previewCategory");
  const previewImage = document.getElementById("previewImage");

  if (
    previewModal &&
    previewName &&
    previewDescription &&
    previewPrice &&
    previewQuantity &&
    previewCategory &&
    previewImage
  ) {
    previewName.textContent = product.name || "No Name";
    previewDescription.textContent = product.description || "No Description";
    previewPrice.textContent = `Price: ₱${product.price || "N/A"}`;
    previewQuantity.textContent = `In Stock: ${product.quantity || "N/A"}`;
    previewCategory.textContent = `Category: ${product.cat || "N/A"}`;
    previewImage.src = imagePath;

    previewModal.style.display = "flex";
  } else {
    console.error(
      "One or more preview modal elements are missing from the DOM."
    );
  }
}

window.showProductPreview = showProductPreview;

function displayProducts(products) {
  const productList = document.getElementById("productList");
  if (!productList) return;
  productList.innerHTML = "";

  if (products.length === 0) {
    productList.innerHTML =
      "<p>No products available for the selected category.</p>";
    return;
  }

  products.forEach((product) => {
    if (!product.id) {
      console.warn(`⚠️ Missing product ID for`, product);
      return;
    }

    const productItem = document.createElement("div");
    productItem.className = "product-card";

    let imagePath = product.image;
    if (!imagePath.startsWith("http") && !imagePath.startsWith("/")) {
      imagePath = `http://192.168.137.14/backend/uploads/${imagePath}`;
    }

    productItem.innerHTML = `
            <div class="product-header">
                <span class="price-badge">Price: ₱${product.price}</span>
                <button class="menu-btn" onclick="toggleMenu(${product.id})">⋮</button>
                
                <div class="menu-dropdown" id="menu-${product.id}" style="display: none;">
                    <button onclick="editProduct(${product.id}, '${product.name}', '${product.price}', 
                        '${product.description}', '${product.quantity}', '${imagePath}', '${product.category}')">
                        Edit
                    </button>
                    <button onclick="deleteProduct(${product.id})">Delete</button>
                </div>
            </div>
            <div class="product-image">
                <img src="${imagePath}" alt="Product Image" onerror="this.onerror=null; this.src='default-product.jpg';">
            </div>
            <div class="product-details">
                <h3>${product.name}</h3>
                <p>In Stock: ${product.quantity}</p>
            </div>
        `;

    productItem.addEventListener("click", function (event) {
      if (
        event.target.closest(".menu-btn") ||
        event.target.closest(".menu-dropdown")
      ) {
        return;
      }
      showProductPreview(product);
    });

    productList.appendChild(productItem);
  });

  console.log("✅ Products displayed successfully.");
}

// Initialize custom modal dialogs for alert and confirm
(function () {
  // Create modal overlay element if it doesn't exist
  const modalOverlay = document.createElement("div");
  modalOverlay.id = "customModalOverlay";
  modalOverlay.innerHTML = `
    <div id="customModal">
      <p id="customModalMessage"></p>
      <div id="customModalButtons"></div>
    </div>
  `;
  document.body.appendChild(modalOverlay);

  // Override window.alert to use custom modal
  window.alert = function(message) {
    return new Promise(resolve => {
      const modalMessage = document.getElementById("customModalMessage");
      const modalButtons = document.getElementById("customModalButtons");
      modalMessage.textContent = message;
      modalButtons.innerHTML = `<button id="customOkButton">OK</button>`;
      modalOverlay.style.display = "flex";
      document.getElementById("customOkButton").onclick = function() {
        modalOverlay.style.display = "none";
        resolve();
      };
    });
  };

  // Override window.confirm to use custom modal, returns true if OK, false if Cancel.
  window.confirm = function(message) {
    return new Promise(resolve => {
      const modalMessage = document.getElementById("customModalMessage");
      const modalButtons = document.getElementById("customModalButtons");
      modalMessage.textContent = message;
      modalButtons.innerHTML = `
        <button id="customOkButton">OK</button>
        <button id="customCancelButton">Cancel</button>
      `;
      modalOverlay.style.display = "flex";
      document.getElementById("customOkButton").onclick = function() {
        modalOverlay.style.display = "none";
        resolve(true);
      };
      document.getElementById("customCancelButton").onclick = function() {
        modalOverlay.style.display = "none";
        resolve(false);
      };
    });
  };
})();