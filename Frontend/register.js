document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const errorDiv = document.getElementById('error');

    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    // Email validation: Ensure it is a Gmail address
    const gmailRegex = /^[a-zA-Z0-9._%+-]+@gmail\.com$/;
    if (!gmailRegex.test(email)) {
        errorDiv.textContent = 'Only Gmail addresses (@gmail.com) are allowed.';
        return;
    }

    // Password match validation
    if (password !== confirmPassword) {
        errorDiv.textContent = 'Passwords do not match';
        return;
    }

    try {
        const response = await fetch('http://localhost/backend/register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                username: username,
                email: email,
                password: password
            })
        });

        const data = await response.json();
        
        if (data.success) {
            alert('Registration successful! Please log in.');
            window.location.href = 'login.html';
        } else {
            errorDiv.textContent = data.message || 'Registration failed';
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