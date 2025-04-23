<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Data cleaning
    $username = trim(htmlspecialchars(strip_tags($_POST['username'])));
    $email = trim(htmlspecialchars(strip_tags($_POST['email'])));
    $password = trim($_POST['password']); 
    $confirm_password = trim($_POST['confirm_password']);

    if ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->rowCount() > 0) {
            $error = "Username or email already exists";
        } else {
            // Hash password and insert user into the database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password])) {
                header("Location: login.php");
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YourHub - Register</title>
    <link rel="stylesheet" href="css/register.css">
</head>
<body>
    <!-- Left Gradient Section -->
    <div class="gradient-section">
        <div id="lottie-animation"></div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
        <script>
            lottie.loadAnimation({
                container: document.getElementById("lottie-animation"), // Div where animation will play
                renderer: "svg", // Can be "canvas" or "html" too
                loop: true, // Set false if you want it to play once
                autoplay: true,
                path: "login-registerAnimation.json" // Replace with your Lottie JSON file path or URL
            });
        </script>
    </div>

    <!-- Right Register Form Section -->
    <div class="right-section">
        <div class="auth-form">
            <div class="inner-container">
                <h2 id="register-heading">Register</h2>
                <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
                <form method="POST" action="register.php">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="formInput" name="email" required placeholder="e.g., sam@example.com">
                    </div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="formInput" name="username" required placeholder="Choose a username">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="formInput" name="password" required placeholder="Create a password">
                    </div>
                    <div class="form-group">
                        <label for="confirm-password">Confirm Password</label>
                        <input type="password" class="formInput" name="confirm-password" required placeholder="Re-enter password">
                    </div>
                    <button type="submit">Register</button>
                </form>
                <p>Already have an account? <a href="login.php">Login</a></p>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.querySelector("form");
        const passwordInput = document.querySelector("input[name='password']");
        const confirmPasswordInput = document.querySelector("input[name='confirm-password']");

        form.addEventListener("submit", function (event) {
            if (passwordInput.value !== confirmPasswordInput.value) {
                event.preventDefault(); 
                passwordInput.style.border = "1px solid red";
                confirmPasswordInput.style.border = "1px solid red";

                passwordInput.value = "";
                confirmPasswordInput.value = "";
                passwordInput.placeholder = "Passwords do not match!";
                confirmPasswordInput.placeholder = "Passwords do not match!";
                passwordInput.classList.add("error-placeholder");
                confirmPasswordInput.classList.add("error-placeholder");
            }
        });
    });
</script>

<style>
    .error-placeholder::placeholder {
        color: red;
    }
</style>

</body>
</html>
    