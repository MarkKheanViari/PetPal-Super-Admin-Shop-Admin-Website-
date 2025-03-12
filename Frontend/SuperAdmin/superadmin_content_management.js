// 📌 Fetch Product Listings and Store in Global Variable
let allProducts = [];
let currentFilter = "all"; // Track active filter

// 📌 Fetch Products from Database with Proper JSON Handling
async function fetchProducts() {
  try {
    const response = await fetch(
      "http://192.168.1.3/backend/Frontend/SuperAdmin/fetch_products.php"
    );
    const data = await response.json();

    const productTable = document.getElementById("productTable");
    productTable.innerHTML = "";

    // ✅ Ensure category and quantity are displayed
    data.products.forEach((product) => {
      productTable.innerHTML += `
                <tr>
                    <td>#P${product.id}</td>
                    <td>${product.name}</td>
                    <td>$${product.price}</td>
                    <td>${product.quantity}</td>
                    <td>${product.category || "Unknown"}</td>
                    <td><img src="${product.image_url}" width="50"></td>
                    <td>
                        <button class="approve-btn">✅</button>
                        <button class="delete-btn">❌</button>
                        <button class="more-btn">More</button>
                    </td>
                </tr>
            `;
    });
  } catch (error) {
    console.error("❌ Error fetching products:", error);
  }
}

// 📌 Function to Display Products in Table
function displayProducts(filterType) {
  const productTable = document.getElementById("productTable");

  if (!productTable) {
    console.error("❌ ERROR: 'productTable' element is missing in the HTML.");
    return;
  }

  productTable.innerHTML = ""; // Clear table before adding new data

  let filteredProducts = allProducts;

  if (filterType === "pending") {
    filteredProducts = allProducts.filter(
      (product) => product.status === "Pending"
    );
  } else if (filterType === "approved") {
    filteredProducts = allProducts.filter(
      (product) => product.status === "Approved"
    );
  } else if (filterType === "rejected") {
    filteredProducts = allProducts.filter(
      (product) => product.status === "Rejected"
    );
  }

  // ✅ Render products in table
  if (filteredProducts.length > 0) {
    filteredProducts.forEach((product) => {
      const row = `
                <tr>
                    <td>#P${product.id}</td>
                    <td>${product.name}</td>
                    <td>${product.owner || "Unknown"}</td>
                    <td>$${parseFloat(product.price).toFixed(2)}</td>
                    <td><span class="status-${(
                      product.status || "unknown"
                    ).toLowerCase()}">${product.status || "Unknown"}</span></td>
                    <td>
                        <button class="approve-btn">✔</button> 
                        <button class="reject-btn">✘</button> 
                        <button class="more-btn">More</button>
                    </td>
                </tr>
            `;
      productTable.innerHTML += row;
    });
  } else {
    productTable.innerHTML = "<tr><td colspan='6'>No Products Found</td></tr>";
  }
}

// 📌 Attach Filter Buttons Event Listeners
document.addEventListener("DOMContentLoaded", function () {
  fetchProducts(); // Fetch products on page load

  document
    .getElementById("allProductsBtn")
    .addEventListener("click", function () {
      setActiveFilter(this);
      displayProducts("all");
    });

  document
    .getElementById("pendingProductsBtn")
    .addEventListener("click", function () {
      setActiveFilter(this);
      displayProducts("pending");
    });

  document
    .getElementById("approvedProductsBtn")
    .addEventListener("click", function () {
      setActiveFilter(this);
      displayProducts("approved");
    });

  document
    .getElementById("rejectedProductsBtn")
    .addEventListener("click", function () {
      setActiveFilter(this);
      displayProducts("rejected");
    });
});

// 📌 Function to Highlight Active Filter Button
function setActiveFilter(activeBtn) {
  document
    .querySelectorAll(".filter-btn")
    .forEach((btn) => btn.classList.remove("active"));
  activeBtn.classList.add("active");
}
