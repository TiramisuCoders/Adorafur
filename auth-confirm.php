<?php
// Start session
session_start();

// Include database connection
include("connect.php");

// Initialize variables
$message = "";
$status = "error";

// Check if token_hash and type are provided in the URL
if (isset($_GET['token_hash']) && isset($_GET['type'])) {
    $token_hash = $_GET['token_hash'];
    $type = $_GET['type'];
    
    // Verify the token with Supabase API
    $supabase_url = "https://ygbwanzobuielhttdzsw.supabase.co";
    $supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."; // Replace with your actual anon key
    
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $supabase_url . "/auth/v1/verify");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'token_hash' => $token_hash,
        'type' => $type
    ]));
    
    $headers = [
        'Content-Type: application/json',
        'apikey: ' . $supabase_key,
        'Authorization: Bearer ' . $supabase_key
    ];
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    if ($http_code === 200) {
        $status = "success";
        $message = "Your email has been successfully verified! You can now log in to your account.";
    } else {
        $response_data = json_decode($response, true);
        $message = isset($response_data['error_description']) 
            ? $response_data['error_description'] 
            : "Error verifying email. The link may be invalid or expired.";
    }
} else {
    $message = "Invalid verification link. Missing required parameters.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - Adorafur Happy Stay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .verification-container {
            max-width: 600px;
            margin: 100px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .success-message {
            color: #28a745;
        }
        .error-message {
            color: #dc3545;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <img src="Header-Pics/logo.png" alt="Adorafur Logo" class="logo">
        <h2>Email Verification</h2>
        
        <div class="mt-4 <?php echo $status === 'success' ? 'success-message' : 'error-message'; ?>">
            <p><?php echo $message; ?></p>
        </div>
        
        <div class="mt-4">
            <a href="index.php" class="btn btn-primary">Return to Homepage</a>
            <?php if ($status === 'success'): ?>
                <button type="button" class="btn btn-success ms-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                    Login Now
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($status === 'success'): ?>
        <!-- Include login modal for convenience -->
        <?php include 'login.php'; ?>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($status === 'success'): ?>
        <script>
            // Show login modal automatically if verification was successful
            document.addEventListener('DOMContentLoaded', function() {
                var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                setTimeout(function() {
                    loginModal.show();
                }, 1500);
            });
        </script>
    <?php endif; ?>
</body>
</html>