document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const errorDiv = document.getElementById('error');
    
    try {
        const response = await fetch('http://192.168.1.65/backend/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                username: document.getElementById('username').value,
                password: document.getElementById('password').value
            })
        });

        const data = await response.json();
        
        if (data.success) {
            // Store the user ID along with other data
            localStorage.setItem('shop_owner_token', data.token);
            localStorage.setItem('shop_owner_username', data.username);
            localStorage.setItem('shop_owner_id', data.user_id);
            
            window.location.href = 'index.html';
        } else {
            errorDiv.textContent = data.message || 'Login failed';
        }
    } catch (error) {
        console.error('Error:', error);
        errorDiv.textContent = 'An error occurred. Please try again.';
    }
});