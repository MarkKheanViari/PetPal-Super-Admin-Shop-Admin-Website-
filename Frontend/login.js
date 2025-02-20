
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const errorDiv = document.getElementById('error');
        
        try {
            const response = await fetch('http://192.168.34.203/backend/login.php', {
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
                
                window.location.href = 'dashboard.html';
            } else {
                errorDiv.textContent = data.message || 'Login failed';
            }
        } catch (error) {
            console.error('Error:', error);
            errorDiv.textContent = 'An error occurred. Please try again.';
        }
    });
    function togglePassword(fieldId, textId) {
        const field = document.getElementById(fieldId);
        const toggleText = document.getElementById(textId);
        if (field.type === "password") {
            field.type = "text";
            toggleText.textContent = "Hide";
        } else {
            field.type = "password";
            toggleText.textContent = "Show";
        }
    }