// 📌 Global Variables
let allProducts = [];

// 📌 Initialize on Page Load
document.addEventListener("DOMContentLoaded", function () {
  fetchProducts();
  setupEventListeners();
});

// 📌 Fetch Products from Database with Proper JSON Handling
async function fetchProducts(searchQuery = "") {
  const productTable = document.getElementById("productTable");
  productTable.innerHTML =
    "<tr><td colspan='7'><div class='spinner'></div></td></tr>";
  try {
    const response = await fetch(
      "http://192.168.1.65/backend/Frontend/SuperAdmin/fetch_products.php"
    );
    const data = await response.json();
    console.log("Fetched products:", data); // Debug: Log the fetched data
    allProducts = data.products || [];
    displayProducts(searchQuery);
  } catch (error) {
    console.error("❌ Error fetching products:", error);
    productTable.innerHTML =
      "<tr><td colspan='7'>Error loading products</td></tr>";
  }
}

// 📌 Function to Display Products in Table
function displayProducts(searchQuery = "") {
  const productTable = document.getElementById("productTable");
  if (!productTable) {
    console.error("❌ ERROR: 'productTable' element is missing in the HTML.");
    return;
  }

  productTable.innerHTML = ""; // Clear table before adding new data

  let filteredProducts = allProducts;

  // ✅ Apply Search Filtering
  if (searchQuery) {
    filteredProducts = filteredProducts.filter(
      (product) =>
        (product.name || "").toLowerCase().includes(searchQuery) ||
        (product.price || "").toString().includes(searchQuery) ||
        (product.quantity || "").toString().includes(searchQuery) ||
        (product.category || "").toLowerCase().includes(searchQuery)
    );
  }

  // ✅ Render products in table
  if (filteredProducts.length > 0) {
    filteredProducts.forEach((product) => {
      const row = `
                <tr>
                    <td>#P${product.id}</td>
                    <td>${product.name || "Unknown"}</td>
                    <td>$${parseFloat(product.price || 0).toFixed(2)}</td>
                    <td>${product.quantity || "0"}</td>
                    <td>${product.category || "Unknown"}</td>
                    <td>
                        <span class="report-count ${
                          product.report_count > 0 ? "reported" : ""
                        }">
                            ${product.report_count} Report${
        product.report_count !== 1 ? "s" : ""
      }
                        </span>
                    </td>
                    <td>
                        <button class="more-btn" data-id="${
                          product.id
                        }">View More</button>
                    </td>
                </tr>
            `;
      productTable.innerHTML += row;
    });
  } else {
    productTable.innerHTML = "<tr><td colspan='7'>No Products Found</td></tr>";
  }
}

// 📌 Helper Function to Close Modal and Restore Scrolling
function closeModal() {
  const modal = document.getElementById("productModal");
  modal.style.display = "none";
  document.body.style.overflow = ""; // Restore scrolling
}

// 📌 Setup Event Listeners
function setupEventListeners() {
  // ✅ Search Functionality
  document.getElementById("searchBar").addEventListener("keyup", function () {
    fetchProducts(this.value.trim().toLowerCase());
  });

  // ✅ Action Buttons (Event Delegation)
  document
    .getElementById("productTable")
    .addEventListener("click", function (e) {
      const id = e.target.dataset.id;
      if (!id) return;

      if (e.target.classList.contains("more-btn")) {
        showProductDetails(id);
      }
    });

  // ✅ Modal Close Button
  const modal = document.getElementById("productModal");
  const closeBtn = document.getElementsByClassName("close")[0];
  closeBtn.onclick = closeModal;

  // ✅ Close Modal When Clicking Outside
  window.onclick = function (event) {
    if (event.target === modal) {
      closeModal();
    }
  };
}

// 📌 Function to Delete a Product
async function deleteProduct(productId) {
  try {
    const response = await fetch(
      "http://192.168.1.65/backend/Frontend/SuperAdmin/delete_product.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `product_id=${productId}`,
      }
    );

    // Check if the response is OK
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }

    // Fetch the raw response text for debugging
    const text = await response.text();
    console.log("Raw response from delete_product.php:", text);

    // Try to parse the response as JSON
    let data;
    try {
      data = JSON.parse(text);
    } catch (error) {
      throw new Error(`Failed to parse JSON: ${text}`);
    }

    if (data.success) {
      Toastify({
        text: "✅ Product deleted successfully!",
        duration: 3000,
        style: { background: "#10b981" },
      }).showToast();
      // Refresh the product list
      fetchProducts();
      // Close the modal and restore scrolling
      closeModal();
    } else {
      throw new Error(data.error || "Failed to delete product");
    }
  } catch (error) {
    console.error("❌ Error deleting product:", error);
    Toastify({
      text: `❌ Error deleting product: ${error.message}`,
      duration: 3000,
      style: { background: "#ef4444" },
    }).showToast();
  }
}

// 📌 Function to Fetch Report Details
async function fetchReportDetails(productId) {
  try {
    const response = await fetch(
      `http://192.168.1.65/backend/Frontend/SuperAdmin/fetch_product_reports.php?product_id=${productId}`
    );
    const data = await response.json();
    console.log(`Report details for product ${productId}:`, data); // Debug: Log the report data
    return data.reports || [];
  } catch (error) {
    console.error("❌ Error fetching report details:", error);
    return [];
  }
}

// 📌 Function to Show Product Details in Modal
async function showProductDetails(productId) {
  const product = allProducts.find((p) => p.id == productId);
  if (!product) {
    Toastify({
      text: "❌ Product not found!",
      duration: 3000,
      style: { background: "#ef4444" },
    }).showToast();
    return;
  }

  console.log("Product details:", product);

  // Fetch report details
  const reports = await fetchReportDetails(productId);

  // Determine which image to display: prioritize image_url, fall back to image
  let imageDisplay = "";
  if (product.image_url) {
    imageDisplay = `<img src="${product.image_url}" alt="${product.name}" class="product-image" onerror="this.onerror=null; this.parentNode.innerHTML='<p>Image failed to load</p>'" />`;
  } else if (product.image) {
    const imagePath = `http://192.168.1.65/backend/uploads/${product.image}`;
    console.log("Attempting to load image from:", imagePath);
    imageDisplay = `<img src="${imagePath}" alt="${product.name}" class="product-image" onerror="this.onerror=null; this.parentNode.innerHTML='<p>Image failed to load</p>'" />`;
  } else {
    imageDisplay = `<p>No image available</p>`;
  }

  // Build the reports section
  let reportsDisplay = "<h3>Reports</h3>";
  if (reports.length > 0) {
    reportsDisplay += "<ul>";
    reports.forEach((report) => {
      reportsDisplay += `
              <li>
                  <strong>Reported by:</strong> ${report.reporter}<br>
                  <strong>Reason:</strong> ${report.reason}<br>
                  <strong>Date:</strong> ${report.created_at}
              </li>
          `;
    });
    reportsDisplay += "</ul>";
  } else {
    reportsDisplay += "<p>No reports for this product.</p>";
  }

  // Build the modal content with error handling
  try {
    const modalContent = document.getElementById("modalContent");
    modalContent.innerHTML = `
          ${imageDisplay}
          <p><strong>ID:</strong> #P${product.id}</p>
          <p><strong>Name:</strong> ${product.name || "Unknown"}</p>
          <p><strong>Price:</strong> $${parseFloat(product.price || 0).toFixed(
            2
          )}</p>
          <p><strong>Shop Owner:</strong> ${product.shop || "Unknown"}</p>
          <p><strong>Description:</strong> ${
            product.description || "No description available"
          }</p>
          <p><strong>Quantity:</strong> ${product.quantity || "0"}</p>
          <p><strong>Category:</strong> ${product.category || "Unknown"}</p>
          <p><strong>Image URL:</strong> ${
            product.image_url || "No image URL available"
          }</p>
          <p><strong>Image File:</strong> ${
            product.image || "No image file available"
          }</p>
          ${reportsDisplay}
          <button class="delete-btn" data-id="${
            product.id
          }">Delete Product</button>
      `;

    const modal = document.getElementById("productModal");
    modal.style.display = "block";
    // Disable scrolling on the main page while modal is open
    document.body.style.overflow = "hidden";

    // Add event listener for the delete button
    document
      .querySelector(".delete-btn")
      .addEventListener("click", function () {
        if (confirm("Are you sure you want to delete this product?")) {
          deleteProduct(productId);
        }
      });
  } catch (error) {
    console.error("❌ Error rendering modal content:", error);
    Toastify({
      text: "❌ Error displaying product details!",
      duration: 3000,
      style: { background: "#ef4444" },
    }).showToast();
  }
}
