<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Query database to check credentials and role
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    // Verify password (plain text comparison for school project)
    if ($admin && $password === $admin['password_hash']) {
        // Set session variables
        $_SESSION['admin_id'] = $admin['user_id'];
        $_SESSION['admin_username'] = $admin['first_name'];
        
        // Redirect to dashboard
        header('Location: admin.php');
        exit();
    } else {
        $error = "Invalid credentials or not an admin";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/login.css">
    <title>Library Management System - Login</title>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>📚 Library System</h1>
            <p>Sign in to access your account</p>
        </div>
        
        <form action="login.php" id="loginForm" method="post">
            <div class="form-group">
                <label for="username">Email</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="remember-forgot">
                <label class="remember-me">
                    <input type="checkbox" id="remember">
                    <span>Remember me</span>
                </label>
                <a href="#" class="forgot-password">Forgot Password?</a>
            </div>
            
            <button type="submit" class="login-button">Sign In</button>
        </form>
        
        <div class="signup-link">
            Don't have an account? <a href="#">Sign Up</a>
        </div>
    </div>
</body>
</html>