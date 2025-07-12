const form = document.getElementById('loginForm');
const messageDiv = document.getElementById('message');

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(form);

    try {
        const response = await fetch('../php/Login_Account.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        const result = await response.json();

        messageDiv.textContent = result.message;
        messageDiv.style.color = result.success ? 'green' : 'red';

        if (result.success) {
            setTimeout(() => {
                window.location.href = '../pages/Dashboard.php'; // Make sure case matches actual filename
            }, 3000);
        } else {
            setTimeout(() => {
                messageDiv.textContent = '';
            }, 9000);
        }
    } catch (error) {
        messageDiv.textContent = '? Error connecting to server.';
        messageDiv.style.color = 'red';
        setTimeout(() => {
            messageDiv.textContent = '';
        }, 9000);
        console.error(error);
    }
});