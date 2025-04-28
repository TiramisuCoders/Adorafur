<?php
// This file tests the Supabase login directly
// Create this file in the same directory as login.php

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to test Supabase login
function testSupabaseLogin($email, $password) {
    $supabase_url = "https://ygbwanzobuielhttdzsw.supabase.co";
    $supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlnYndhbnpvYnVpZWxodHRkenN3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM1MTY3NTMsImV4cCI6MjA1OTA5Mjc1M30.bIaP_7rfHyne5PQ_Wmt8qdMYFDzurdnEAUR7U2bxbDQ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $supabase_url . "/auth/v1/token?grant_type=password");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    
    $auth_data = [
        'email' => $email,
        'password' => $password
    ];
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($auth_data));
    
    $headers = [
        'Content-Type: application/json',
        'apikey: ' . $supabase_key
    ];
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    
    return [
        'success' => $http_code === 200,
        'http_code' => $http_code,
        'response' => $response
    ];
}

// Main page content
echo "<h1>Supabase Login Test</h1>";

// Test form
echo "<form method='post'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='test_email'>Email:</label><br>";
echo "<input type='email' id='test_email' name='test_email' required style='padding: 5px; width: 300px;'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='test_password'>Password:</label><br>";
echo "<input type='password' id='test_password' name='test_password' required style='padding: 5px; width: 300px;'>";
echo "</div>";
echo "<input type='submit' name='test_login' value='Test Supabase Login' style='padding: 10px; background-color: #007BFF; color: white; border: none; cursor: pointer;'>";
echo "</form>";

// Process test
if (isset($_POST['test_login'])) {
    $email = $_POST['test_email'] ?? '';
    $password = $_POST['test_password'] ?? '';
    
    echo "<h2>Test Results:</h2>";
    
    $result = testSupabaseLogin($email, $password);
    
    if ($result['success']) {
        echo "<p style='color: green;'>✓ Supabase login successful</p>";
        echo "<p>Response:</p>";
        echo "<pre>" . htmlspecialchars($result['response']) . "</pre>";
        
        // Parse the response to get the access token
        $response_data = json_decode($result['response'], true);
        if (isset($response_data['access_token'])) {
            echo "<p>Access token: " . substr($response_data['access_token'], 0, 20) . "...</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Supabase login failed: HTTP code " . $result['http_code'] . "</p>";
        echo "<p>Response:</p>";
        echo "<pre>" . htmlspecialchars($result['response']) . "</pre>";
        
        // Parse the error message
        $response_data = json_decode($result['response'], true);
        if (isset($response_data['error_description'])) {
            echo "<p>Error description: " . $response_data['error_description'] . "</p>";
        } else if (isset($response_data['message'])) {
            echo "<p>Error message: " . $response_data['message'] . "</p>";
        }
    }
}
?>
