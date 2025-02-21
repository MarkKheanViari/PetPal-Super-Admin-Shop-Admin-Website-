document.addEventListener("DOMContentLoaded", function () {
    console.log("📢 Dashboard.js Loaded!");

    // Ensure the chart renders only if the element exists
    const salesChartCanvas = document.getElementById("salesChart");
    if (salesChartCanvas) {
        renderSalesChart();
    }

    // Ensure elements exist before proceeding
    const vetContainer = document.getElementById("dashboardVetAppointments");
    const groomingContainer = document.getElementById("dashboardGroomingSchedule");

    if (vetContainer && groomingContainer) {
        fetchVetAppointments();
        fetchGroomingAppointments();
    }

    fetchOrders();
    fetchProducts();
});

function fetchVetAppointments() {
    fetch("http://192.168.137.239/backend/fetch_veterinary_appointments.php")
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP Error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                let vetContainer = document.getElementById("dashboardVetAppointments");
                vetContainer.innerHTML = "";

                data.appointments.slice(0, 3).forEach(appointment => {
                    let appointmentElement = createAppointmentElement(appointment);
                    vetContainer.appendChild(appointmentElement);
                });
            } else {
                console.warn("No veterinary appointments found.");
            }
        })
        .catch(error => console.error("❌ ERROR Fetching Veterinary Appointments:", error));
}


function fetchGroomingAppointments() {
    fetch("http://192.168.137.239/backend/fetch_grooming_appointments.php")
        .then(response => response.json())
        .then(data => {
            console.log("📢 Grooming Appointments Data:", data); // Debugging
            if (data.success) {
                let groomingContainer = document.getElementById("dashboardGroomingSchedule");
                groomingContainer.innerHTML = "";

                data.appointments.slice(0, 3).forEach(appointment => {
                    let appointmentElement = createAppointmentElement(appointment);
                    groomingContainer.appendChild(appointmentElement);
                });
            } else {
                console.warn("No grooming appointments found.");
            }
        })
        .catch(error => console.error("❌ ERROR Fetching Grooming Appointments:", error));
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", { month: "2-digit", day: "2-digit", year: "numeric" });
}




function createAppointmentElement(appointment) {
    let appointmentElement = document.createElement("div");
    appointmentElement.classList.add("appointment-card");
    appointmentElement.innerHTML = `
        <div class="appointment-info">
            <h3>${appointment.service}</h3>
            <p>Customer: ${appointment.customer_name}</p>
            <p>Pet: ${appointment.pet_name}</p>
        </div>
        <div class="appointment-time">
            <p>${formatDate(appointment.date)}</p>
        </div>
        <button class="more-options">⋮</button>
    `;
    return appointmentElement;
}


function renderSalesChart() {
    const ctx = document.getElementById("salesChart").getContext("2d");

    new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul"], // X-axis labels
            datasets: [{
                label: "Monthly Sales (₱)", // Update label to Peso
                data: [1000, 1200, 900, 1400, 1100, 1300, 1500], // Static sales data
                backgroundColor: [
                    "#FFB74D", "#FFA726", "#FB8C00", 
                    "#F57C00", "#EF6C00", "#E65100", "#D84315"
                ],
                borderColor: "black", // Consistent border color
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString(); // Format numbers with ₱
                        }
                    }
                }
            }
        }
    });
}

function fetchOrders() {
    fetch("http://192.168.137.239/backend/fetch_orders.php")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let dashboardOrdersContainer = document.getElementById("dashboardOrdersContainer");
                dashboardOrdersContainer.innerHTML = ""; // Clear previous content

                data.orders.slice(0, 3).forEach(order => {
                    let orderElement = createOrderElement(order);
                    dashboardOrdersContainer.appendChild(orderElement);
                });
            }
        })
        .catch(error => console.error("❌ ERROR Fetching Orders:", error));
}

function fetchProducts() {
    fetch("http://192.168.137.239/backend/fetch_product.php")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let dashboardProductsContainer = document.getElementById("dashboardProductsContainer");
                dashboardProductsContainer.innerHTML = ""; // Clear previous content

                data.products.slice(0, 3).forEach(product => {
                    let productElement = createProductElement(product);
                    dashboardProductsContainer.appendChild(productElement);
                });
            }
        })
        .catch(error => console.error("❌ ERROR Fetching Products:", error));
}

function createOrderElement(order) {
    let orderElement = document.createElement("div");
    orderElement.classList.add("order-card");
    orderElement.innerHTML = `
        <div class="order-icon">📦</div>
        <p class="order-number">Order #${order.order_id}</p>
        <p class="customer-name">${order.customer_name}</p>
        <p class="order-total">Total: ₱${order.total_price}</p>
    `;
    return orderElement;
}

function createProductElement(product) {
    let productElement = document.createElement("div");
    productElement.classList.add("product-card");
    productElement.innerHTML = `
        <div class="product-icon">🐕</div>
        <h3>${product.name}</h3>
        <p class="product-desc">${product.description}</p>
        <p class="stock">In Stock: ${product.quantity}</p>
    `;
    return productElement;
}
