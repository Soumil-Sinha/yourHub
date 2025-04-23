<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //Data cleaning
    $username = trim(htmlspecialchars(strip_tags($_POST['username'])));
    $password = trim($_POST['password']); // Passwords shouldn't be altered with htmlspecialchars()

    // Check if username exists in the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // Redirect to index.php after login
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YourHub - Login</title>
    <link rel="stylesheet" href="css/login.css">
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
z
    <!-- Right Login Form Section -->
    <div class="right-section">
   
        <div class="auth-form">
            <div class="inner-container">
            <h2 id="login-heading">Login</h2>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" class="formInput" name="username" required  placeholder="e.g., sam smith">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="formInput" name="password" required  placeholder="enter password">
                </div>
                <button type="submit">Login</button>
            </form>
            <p>Don't have an account? <a href="register.php">Register</a></p>
            </div>
        </div>
</div>
</body>
</html>

