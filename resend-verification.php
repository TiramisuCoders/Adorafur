<?php
// Start session
session_start();

// Initialize response
$response = [
    'success' => false,
    'message' => ''
];

// Check if email is provided
if (isset($_POST['email']) && !empty($_POST['email'])) {
    $email = $_POST['email'];
    
    // Supabase API details
    $supabase_url = "https://ygbwanzobuielhttdzsw.supabase.co";
    $supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."; // Replace with your actual anon key
    
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
    
    curl_close($ch);
    
    if ($http_code === 200) {
        $response['success'] = true;
        $response['message'] = "Verification email has been resent. Please check your inbox.";
    } else {
        $error_data = json_decode($response_data, true);
        $response['message'] = isset($error_data['error_description']) 
            ? $error_data['error_description'] 
            : "Error sending verification email. Please try again.";
    }
} else {
    $response['message'] = "Email is required.";
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>