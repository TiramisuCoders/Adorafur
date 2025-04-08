<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include("connect.php");

// Set header to return JSON
header('Content-Type: application/json');

// Check if it's a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] === 'forgotPassword') {
        $email = $_POST['email'] ?? '';
        $error = null;
        $success = false;
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Check if the email exists in the database
            $stmt = $conn->prepare("SELECT c_id FROM customer WHERE c_email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() === 0) {
                $error = 'No account found with this email address.';
            } else {
                // Email exists, send password reset request to Supabase
                $supabase_url = "https://ygbwanzobuielhttdzsw.supabase.co";
                $supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlnYndhbnpvYnVpZWxodHRkenN3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM1MTY3NTMsImV4cCI6MjA1OTA5Mjc1M30.bIaP_7rfHyne5PQ_Wmt8qdMYFDzurdnEAUR7U2bxbDQ";
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $supabase_url . "/auth/v1/recover");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                
                $data = [
                    'email' => $email
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
                error_log("Supabase Password Reset Response: " . $response);
                error_log("HTTP Code: " . $http_code);
                
                curl_close($ch);
                
                if ($http_code === 200) {
                    $success = true;
                } else {
                    $response_data = json_decode($response, true);
                    $error = $response_data['message'] ?? 'An error occurred while processing your request.';
                }
            }
        }
        
        echo json_encode([
            'success' => $success,
            'error' => $error
        ]);
        exit;
    }
}

// If we get here, it's an invalid request
echo json_encode([
    'success' => false,
    'error' => 'Invalid request'
]);
?>
