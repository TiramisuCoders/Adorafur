<?php
// Start session
session_start();

// Include database connection
require_once '../connect.php';

// Initialize variables
$email = $password = '';
$error_message = '';

// Check if user is already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="admin-css/admin_login.css">
    <link rel="icon" type="image/png" href="admin-pics/adorafur-logo.png">
    <script src="https://unpkg.com/@supabase/supabase-js@2"></script>
</head>
<body>
    <div class="login-container">
        <div class="login-form">
            <img src="admin-pics/adorafur-logo.png" alt="Adorafur Logo" class="logo">
            <h2>Admin Login</h2>
            
            <div class="error-message" id="login-error" style="<?php echo !empty($error_message) ? 'display:block' : 'display:none'; ?>">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            
            <form id="loginForm">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="button" id="loginBtn" class="login-btn">Login</button>
            </form>
        </div>
    </div>

    <script src="admin_login.js"></script>
</body>
</html>
