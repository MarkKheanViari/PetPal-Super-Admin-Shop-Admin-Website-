// 📌 Global Variables
let allProducts = [];
let currentFilter = "all"; // Track active filter

// 📌 Initialize on Page Load
document.addEventListener("DOMContentLoaded", function () {
  fetchProducts();
  setupEventListeners();
});

// 📌 Fetch Products from Database with Proper JSON Handling
async function fetchProducts(filter = "all", searchQuery = "") {
  const productTable = document.getElementById("productTable");
  productTable.innerHTML =
    "<tr><td colspan='7'><div class='spinner'></div></td></tr>";
  try {
    const response = await fetch(
      "http://10.40.70.46/backend/Frontend/SuperAdmin/fetch_products.php"
    );
    const data = await response.json();
    allProducts = data.products || [];
    displayProducts(filter, searchQuery);
  } catch (error) {
    console.error("❌ Error fetching products:", error);
    productTable.innerHTML =
      "<tr><td colspan='7'>Error loading products</td></tr>";
  }
}

// 📌 Function to Display Products in Table
function displayProducts(filterType = "all", searchQuery = "") {
  currentFilter = filterType;
  const productTable = document.getElementById("productTable");
  if (!productTable) {
    console.error("❌ ERROR: 'productTable' element is missing in the HTML.");
    return;
  }

  productTable.innerHTML = ""; // Clear table before adding new data

  let filteredProducts = allProducts;

  // ✅ Apply Filter by Status
  if (filterType === "pending") {
    filteredProducts = filteredProducts.filter(
      (product) => product.status === "Pending"
    );
  } else if (filterType === "approved") {
    filteredProducts = filteredProducts.filter(
      (product) => product.status === "Approved"
    );
  } else if (filterType === "rejected") {
    filteredProducts = filteredProducts.filter(
      (product) => product.status === "Rejected"
    );
  }

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
                    <td><span class="status-${(
                      product.status || "unknown"
                    ).toLowerCase()}">${product.status || "Unknown"}</span></td>
                    <td>
                        <button class="approve-btn" data-id="${
                          product.id
                        }">✔</button>
                        <button class="reject-btn" data-id="${
                          product.id
                        }">✘</button>
                        <button class="more-btn" data-id="${
                          product.id
                        }">More</button>
                    </td>
                </tr>
            `;
      productTable.innerHTML += row;
    });
  } else {
    productTable.innerHTML = "<tr><td colspan='7'>No Products Found</td></tr>";
  }
}

// 📌 Setup Event Listeners
function setupEventListeners() {
  // ✅ Filter Buttons
  document.querySelectorAll(".filter-btn").forEach((button) => {
    button.addEventListener("click", function () {
      setActiveFilter(this);
      const filterType = this.getAttribute("data-filter");
      displayProducts(
        filterType,
        document.getElementById("searchBar").value.trim().toLowerCase()
      );
    });
  });

  // ✅ Search Functionality
  document.getElementById("searchBar").addEventListener("keyup", function () {
    displayProducts(currentFilter, this.value.trim().toLowerCase());
  });

  // ✅ Action Buttons (Event Delegation)
  document
    .getElementById("productTable")
    .addEventListener("click", async function (e) {
      const id = e.target.dataset.id;
      if (!id) return;

      if (e.target.classList.contains("approve-btn")) {
        await updateProductStatus(id, "Approved");
      } else if (e.target.classList.contains("reject-btn")) {
        await updateProductStatus(id, "Rejected");
      } else if (e.target.classList.contains("more-btn")) {
        console.log(`More info for product ID: ${id}`);
        // Placeholder for "More" functionality
        alert(`More details for product ID: ${id}`);
      }
    });
}

// 📌 Function to Update Product Status
async function updateProductStatus(productId, status) {
  try {
    const response = await fetch(
      "http://10.40.70.46/backend/Frontend/SuperAdmin/update_product_status.php",
      {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: productId, status }),
      }
    );

    const result = await response.json();

    if (result.success) {
      Toastify({
        text: `✅ Product ${status} Successfully!`,
        duration: 3000,
        style: { background: "var(--primary-orange)" },
      }).showToast();
      fetchProducts(
        currentFilter,
        document.getElementById("searchBar").value.trim().toLowerCase()
      );
    } else {
      throw new Error(
        result.message || `Failed to ${status.toLowerCase()} product`
      );
    }
  } catch (error) {
    console.error(`❌ Error updating product status:`, error);
    Toastify({
      text: "❌ Error: " + error.message,
      duration: 3000,
      style: { background: "#ef4444" },
    }).showToast();
  }
}

// 📌 Function to Highlight Active Filter Button
function setActiveFilter(activeBtn) {
  document
    .querySelectorAll(".filter-btn")
    .forEach((btn) => btn.classList.remove("active"));
  activeBtn.classList.add("active");
}
