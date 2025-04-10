<?php
// Supabase API configuration
define('SUPABASE_URL', 'https://ygbwanzobuielhttdzsw.supabase.co');
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlnYndhbnpvYnVpZWxodHRkenN3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM1MTY3NTMsImV4cCI6MjA1OTA5Mjc1M30.bIaP_7rfHyne5PQ_Wmt8qdMYFDzurdnEAUR7U2bxbDQ'); // Replace with your anon key

// Function to make Supabase API requests
function supabaseRequest($endpoint, $method = 'GET', $data = null, $headers = []) {
    $url = SUPABASE_URL . $endpoint;
    
    $defaultHeaders = [
        'Content-Type: application/json',
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY
    ];
    
    $headers = array_merge($defaultHeaders, $headers);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'data' => json_decode($response, true)
    ];
}

// Create a new user with Supabase Auth
function createSupabaseUser($email, $password, $userData = []) {
    $response = supabaseRequest('/auth/v1/admin/users', 'POST', [
        'email' => $email,
        'password' => $password,
        'email_confirm' => true,
        'user_metadata' => $userData
    ]);
    
    return $response;
}

// Get user by ID
function getSupabaseUser($userId) {
    return supabaseRequest('/auth/v1/admin/users/' . $userId, 'GET');
}

// Sign in user and get session
function signInSupabaseUser($email, $password) {
    $response = supabaseRequest('/auth/v1/token?grant_type=password', 'POST', [
        'email' => $email,
        'password' => $password
    ]);
    
    return $response;
}

// Insert admin data into your custom admin table
function insertAdminData($conn, $admin_name, $email, $position, $user_id) {
    try {
        $stmt = $conn->prepare("INSERT INTO admin (admin_name, admin_email, admin_position, supabase_uid) VALUES (:admin_name, :admin_email, :admin_position, :supabase_uid)");
        $stmt->bindParam(':admin_name', $admin_name, PDO::PARAM_STR);
        $stmt->bindParam(':admin_email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':admin_position', $position, PDO::PARAM_STR);
        $stmt->bindParam(':supabase_uid', $user_id, PDO::PARAM_STR);
        
        return $stmt->execute();
    } catch(Exception $e) {
        error_log("Error inserting admin data: " . $e->getMessage());
        return false;
    }
}
?>
