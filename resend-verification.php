<?php
// Start session
session_start();

// Include database connection
include("connect.php");

// Initialize response
$response = [
    'success' => false,
    'message' => ''
];

// Check if email is provided
if (isset($_POST['email']) && !empty($_POST['email'])) {
    $email = $_POST['email'];
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT c_id FROM customer WHERE c_email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() === 0) {
        $response['message'] = 'No account found with this email address.';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Supabase API details
    $supabase_url = "https://ygbwanzobuielhttdzsw.supabase.co";
    $supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlnYndhbnpvYnVpZWxodHRkenN3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM1MTY3NTMsImV4cCI6MjA1OTA5Mjc1M30.bIaP_7rfHyne5PQ_Wmt8qdMYFDzurdnEAUR7U2bxbDQ";
    
    // Send request to Supabase to resend confirmation email
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $supabase_url . "/auth/v1/resend");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'email' => $email,
        'type' => 'signup'
    ]));
    
    $headers = [
        'Content-Type: application/json',
        'apikey: ' . $supabase_key
    ];
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response_data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // For debugging
    error_log("Supabase Resend Verification Response: " . $response_data);
    error_log("HTTP Code: " . $http_code);
    
    curl_close($ch);
    
    if ($http_code === 200) {
        $response['success'] = true;
        $response['message'] = "Verification email has been resent. Please check your inbox.";
    } else {
        $error_data = json_decode($response_data, true);
        $response['message'] = isset($error_data['error_description']) 
            ? $error_data['error_description'] 
            : (isset($error_data['message']) 
                ? $error_data['message'] 
                : "Error sending verification email. Please try again.");
    }
} else {
    $response['message'] = "Email is required.";
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
