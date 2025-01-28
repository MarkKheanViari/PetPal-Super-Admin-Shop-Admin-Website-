// Authentication check
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

// Logout function
function logout() {
    localStorage.removeItem('shop_owner_token');
    localStorage.removeItem('shop_owner_username');
    localStorage.removeItem('shop_owner_id');
    window.location.href = 'login.html';
}

// Show error message
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    document.body.insertBefore(errorDiv, document.body.firstChild);
    setTimeout(() => errorDiv.remove(), 5000);
}

// Fetch and display products
async function fetchProducts() {
    try {
        const userId = localStorage.getItem('shop_owner_id');
        const response = await fetch(`http://192.168.1.65/backend/fetch_product.php?shop_owner_id=${userId}`);
        const products = await response.json();
        
        const productList = document.getElementById('productList');
        productList.innerHTML = '';

        products.forEach(product => {
            const div = document.createElement('div');
            div.className = 'list-item';
            div.textContent = `${product.name} - $${product.price}`;
            productList.appendChild(div);
        });
    } catch (error) {
        console.error('Error fetching products:', error);
        showError('Failed to fetch products: ' + error.message);
    }
}

// Fetch and display services
async function fetchServices() {
    try {
        const userId = localStorage.getItem('shop_owner_id');
        const response = await fetch(`http://192.168.1.65/backend/fetch_services.php?shop_owner_id=${userId}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const services = await response.json();

        const serviceList = document.getElementById('serviceList');
        serviceList.innerHTML = '';

        services.forEach(service => {
            const div = document.createElement('div');
            div.className = 'list-item';
            
            const serviceInfo = document.createElement('div');
            serviceInfo.className = 'service-info';
            serviceInfo.innerHTML = `
                <strong>${service.service_name}</strong>
                <p>${service.description}</p>
                <span class="status-${service.status?.toLowerCase() || 'none'}">
                    ${service.status ? `Status: ${service.status}` : ''}
                </span>
            `;

            const actionButtons = document.createElement('div');
            actionButtons.className = 'service-actions';

            if (service.status === 'pending') {
                actionButtons.innerHTML = `
                    <button onclick="updateServiceStatus(${service.id}, 'confirmed')" class="submit-btn confirm-btn">Confirm</button>
                    <button onclick="updateServiceStatus(${service.id}, 'declined')" class="submit-btn decline-btn">Decline</button>
                `;
            }

            div.appendChild(serviceInfo);
            div.appendChild(actionButtons);
            serviceList.appendChild(div);
        });
    } catch (error) {
        console.error('Error fetching services:', error);
        showError('Failed to fetch services: ' + error.message);
    }
}

// Update the updateServiceStatus function in index.html
async function updateServiceStatus(id, status) {
const button = event.target;
button.disabled = true;

try {
console.log('Updating service:', id, 'to status:', status);

const response = await fetch('http://192.168.1.65/backend/update_service_status.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    },
    body: JSON.stringify({
        id: id,
        status: status,
        shop_owner_id: localStorage.getItem('shop_owner_id') // Add this line
    })
});

if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
}

const data = await response.json();
console.log('Update response:', data);

if (data.success) {
    alert('Status updated successfully!');
    fetchServices(); // Refresh the service list
} else {
    throw new Error(data.message || 'Failed to update status');
}
} catch (error) {
console.error('Error updating status:', error);
showError('Failed to update status: ' + error.message);
} finally {
button.disabled = false;
}
}

// Add product form handler
document.getElementById('productForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('shop_owner_id', localStorage.getItem('shop_owner_id'));

    try {
        const response = await fetch('http://192.168.1.65/backend/add_product.php', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();
        if (data.success) {
            e.target.reset();
            fetchProducts();
        } else {
            throw new Error(data.error || 'Failed to add product');
        }
    } catch (error) {
        console.error('Error adding product:', error);
        showError('Failed to add product: ' + error.message);
    }
});

// Initialize if authenticated
if (checkAuth()) {
    fetchProducts();
    fetchServices();
    setInterval(fetchServices, 30000); // Refresh services every 30 seconds
}