// Fetch and inject the header
fetch('header.html')
    .then(response => response.text())
    .then(html => {
        document.getElementById('header').innerHTML = html;
        console.log("Header loaded successfully");

        // Now that the header is loaded, attach the event listener
        document.getElementById("user-logo").onclick = function() {
            var submenu = document.getElementById("subMenu");
            submenu.classList.toggle('open-menu');
        };
    })
    .catch(error => console.error('Error loading the header:', error));

// Fetch and inject the footer
fetch('footer.html')
    .then(response => response.text())
    .then(html => {
        document.getElementById('footer').innerHTML = html;
        console.log('Footer loaded successfully');
    })
    .catch(error => {
        console.error('Error loading the footer:', error);
    });

//Login Validation
// Example of client-side credential validation
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.querySelector('.login-box');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    
    loginForm.addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the form from submitting
        
        const username = usernameInput.value.trim();
        const password = passwordInput.value.trim();
        
        // Sample validation: Checking if the username and password match predefined values
        if (username === 'admin' && password === 'password123') {
            alert('Login successful!');
            // Proceed with the login logic, such as redirecting to another page
        } else {
            alert('Invalid username or password');
            // Add a red border to the input fields to indicate an error
            usernameInput.style.border = '2px solid red';
            passwordInput.style.border = '2px solid red';
        }
    });

    // Remove the red border when the user starts typing again
    usernameInput.addEventListener('input', function() {
        usernameInput.style.border = '';
    });
    
    passwordInput.addEventListener('input', function() {
        passwordInput.style.border = '';
    });
});

