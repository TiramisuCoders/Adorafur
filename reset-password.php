<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include("connect.php");

// Initialize variables
$token = $_GET['token'] ?? '';
$error = '';
$success = false;

// For debugging
error_log("Token received: " . $token);

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'resetPassword') {
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $token = $_POST['token'] ?? '';
    
    // Validate password
    if (strlen($password) < 8 || strlen($password) > 12) {
        $error = 'Password must be between 8 and 12 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must contain at least 1 uppercase letter.';
    } elseif (!preg_match('/\d/', $password)) {
        $error = 'Password must contain at least 1 number.';
    } elseif (!preg_match('/[\W_]/', $password)) {
        $error = 'Password must contain at least 1 special character.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        // Update password in Supabase
        $supabase_url = "https://ygbwanzobuielhttdzsw.supabase.co";
        $supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlnYndhbnpvYnVpZWxodHRkenN3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM1MTY3NTMsImV4cCI6MjA1OTA5Mjc1M30.bIaP_7rfHyne5PQ_Wmt8qdMYFDzurdnEAUR7U2bxbDQ";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $supabase_url . "/auth/v1/user/recovery");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        
        $data = [
            'token' => $token,
            'password' => $password
        ];
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $headers = [
            'Content-Type: application/json',
            'apikey: ' . $supabase_key
        ];
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // For debugging
        error_log("Supabase Password Update Response: " . $response);
        error_log("HTTP Code: " . $http_code);
        
        curl_close($ch);
        
        if ($http_code === 200) {
            // If Supabase update is successful, update the password in your database
            $response_data = json_decode($response, true);
            if (isset($response_data['email'])) {
                $email = $response_data['email'];
                
                // Update password in your database
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE customer SET c_password = ? WHERE c_email = ?");
                if ($stmt->execute([$hashedPassword, $email])) {
                    $success = true;
                } else {
                    $error = 'Failed to update password in database. Please contact support.';
                }
            } else {
                $error = 'Failed to retrieve user information. Please try again.';
            }
        } else {
            $response_data = json_decode($response, true);
            $error = $response_data['error_description'] ?? ($response_data['message'] ?? 'Failed to reset password. The link may have expired.');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .reset-container {
            max-width: 500px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .password-input {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        .password-requirements {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
        .error {
            color: #af4242;
            background-color: #fde8ec;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="reset-container">
            <?php if ($success): ?>
                <div class="text-center mb-4">
                    <h2>Password Reset Successful</h2>
                    <p class="alert alert-success">Your password has been successfully reset.</p>
                    <p>You can now <a href="login.php" class="btn btn-primary">Login</a> with your new password.</p>
                </div>
            <?php else: ?>
                <h2 class="text-center mb-4">Reset Your Password</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (empty($token)): ?>
                    <div class="alert alert-danger">
                        Invalid or missing reset token. Please request a new password reset link.
                    </div>
                    <div class="text-center mt-3">
                        <a href="login.php" class="btn btn-primary">Back to Login</a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="resetPassword">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <div class="mb-3 password-input">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <i class="fas fa-eye password-toggle" id="passwordToggle"></i>
                            <div class="password-requirements">
                                Password must be 8-12 characters, containing 1 special character, 1 uppercase letter and 1 number.
                            </div>
                        </div>
                        
                        <div class="mb-4 password-input">
                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                            <i class="fas fa-eye password-toggle" id="confirmPasswordToggle"></i>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Reset Password</button>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle functionality
            const passwordToggle = document.getElementById('passwordToggle');
            const confirmPasswordToggle = document.getElementById('confirmPasswordToggle');
            const passwordField = document.getElementById('password');
            const confirmPasswordField = document.getElementById('confirmPassword');
            
            function togglePasswordVisibility(field, toggle) {
                if (field && toggle) {
                    if (field.type === 'password') {
                        field.type = 'text';
                        toggle.classList.remove('fa-eye');
                        toggle.classList.add('fa-eye-slash');
                    } else {
                        field.type = 'password';
                        toggle.classList.remove('fa-eye-slash');
                        toggle.classList.add('fa-eye');
                    }
                }
            }
            
            if (passwordToggle && passwordField) {
                passwordToggle.addEventListener('click', function() {
                    togglePasswordVisibility(passwordField, passwordToggle);
                });
            }
            
            if (confirmPasswordToggle && confirmPasswordField) {
                confirmPasswordToggle.addEventListener('click', function() {
                    togglePasswordVisibility(confirmPasswordField, confirmPasswordToggle);
                });
            }
        });
    </script>
</body>
</html>
