document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const errorDiv = document.getElementById('error');
    
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        errorDiv.textContent = 'Passwords do not match';
        return;
    }
    
    try {
        const response = await fetch('http://192.168.1.65/backend/register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                username: document.getElementById('username').value,
                email: document.getElementById('email').value,
                password: password
            })
        });

        const data = await response.json();
        
        if (data.success) {
            alert('Registration successful! Please login.');
            window.location.href = 'login.html';
        } else {
            errorDiv.textContent = data.message || 'Registration failed';
        }
    } catch (error) {
        console.error('Error:', error);
        errorDiv.textContent = 'An error occurred. Please try again.';
    }
});