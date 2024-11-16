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
    
    // Remove the red border when the user starts typing again
    usernameInput.addEventListener('input', function() {
        usernameInput.style.border = '';
    });
    
    passwordInput.addEventListener('input', function() {
        passwordInput.style.border = '';
    });
});

