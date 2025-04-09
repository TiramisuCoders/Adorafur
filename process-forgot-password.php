<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include("connect.php");

// Set header to return JSON
header('Content-Type: application/json');

// Initialize response
$response = [
    'success' => false,
    'error' => ''
];

// Check if it's a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] === 'forgotPassword') {
        $email = $_POST['email'] ?? '';
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['error'] = 'Please enter a valid email address.';
        } else {
            // Check if the email exists in the database
            $stmt = $conn->prepare("SELECT c_id FROM customer WHERE c_email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() === 0) {
                $response['error'] = 'No account found with this email address.';
            } else {
                // Email exists, send password reset request to Supabase
                $supabase_url = "https://ygbwanzobuielhttdzsw.supabase.co";
                $supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlnYndhbnpvYnVpZWxodHRkenN3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM1MTY3NTMsImV4cCI6MjA1OTA5Mjc1M30.bIaP_7rfHyne5PQ_Wmt8qdMYFDzurdnEAUR7U2bxbDQ";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $supabase_url . "/auth/v1/recover");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                
                // Add the redirect URL to your site's index page
                // Supabase will add the token as a hash fragment
                $data = [
                    'email' => $email,
                    'redirect_to' => 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/reset-password.php'
                ];
                
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                
                $headers = [
                    'Content-Type: application/json',
                    'apikey: ' . $supabase_key
                ];
                
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                
                $response_data = curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                
                // For debugging
                error_log("Supabase Password Reset Response: " . $response_data);
                error_log("HTTP Code: " . $http_code);
                
                curl_close($ch);
                
                if ($http_code === 200) {
                    $response['success'] = true;
                } else {
                    $error_data = json_decode($response_data, true);
                    $response['error'] = isset($error_data['error_description']) 
                        ? $error_data['error_description'] 
                        : (isset($error_data['message']) 
                            ? $error_data['message'] 
                            : 'An error occurred while processing your request.');
                }
            }
        }
    } else {
        $response['error'] = 'Invalid action.';
    }
} else {
    $response['error'] = 'Invalid request method.';
}

// Return JSON response
echo json_encode($response);
?>
