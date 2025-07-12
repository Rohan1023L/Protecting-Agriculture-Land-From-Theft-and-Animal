
const form = document.getElementById('loginForm');
const messageDiv = document.getElementById('message');

form.addEventListener('submit', async (e) => {
    e.preventDefault(); // Prevent normal form submit and page reload

    const formData = new FormData(form);

    try {
        const response = await fetch('../php/Login_Account.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        messageDiv.textContent = result.message;
        messageDiv.style.color = result.success ? 'green' : 'red';

        if (result.success) {
            setTimeout(() => {
                window.location.href = '../pages/dashboard.php'; // Adjust path as needed
            }, 3000);
        } else {
            // Clear error message after 5 seconds
            setTimeout(() => {
                messageDiv.textContent = '';
            }, 9000);
        }
    } catch (error) {
        messageDiv.textContent = '❌ Error connecting to server.';
        messageDiv.style.color = 'red';
        setTimeout(() => {
            messageDiv.textContent = '';
        }, 9000);
    }
});

