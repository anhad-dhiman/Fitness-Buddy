<?php
require 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Fitness Buddy</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4" style="width: 350px;">
        <h4 class="text-center">Register</h4>
        <form id="registerForm">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
        <div class="mt-3 text-center">
            <a href="login.php">Already have an account? Login</a>
        </div>
        <div id="registerMessage" class="mt-3 text-center"></div>
    </div>

    <script>
        document.getElementById("registerForm").addEventListener("submit", function(event) {
            event.preventDefault();

            const username = document.getElementById("username").value;
            const email = document.getElementById("email").value;
            const password = document.getElementById("password").value;

            // Create a FormData object for sending data as JSON
            const data = {
                username: username,
                email: email,
                password: password
            };

            fetch("api_register.php", {
                method: "POST",
                headers: { 
                    "Content-Type": "application/json" // Send data as JSON
                },
                body: JSON.stringify(data) // Convert the JavaScript object to JSON
            })
            .then(response => response.json()) // Parse the JSON response
            .then(data => {
                const messageBox = document.getElementById("registerMessage");
                if (data.status === "success") {
                    messageBox.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                    setTimeout(() => window.location.href = "login.php", 2000); // Redirect after success
                } else {
                    messageBox.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(error => console.error("Error:", error));
        });
    </script>

</body>
</html>
