<?php
// This script will update display names for all existing admin users in Supabase
// Run this once to fix existing users without display names

// Start session
session_start();

// Include database connection
require_once '../connect.php';

// Set content type to JSON
header('Content-Type: application/json');

// Supabase credentials
$supabase_url = "https://ygbwanzobuielhttdzsw.supabase.co";
$supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlnYndhbnpvYnVpZWxodHRkenN3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM1MTY3NTMsImV4cCI6MjA1OTA5Mjc1M30.bIaP_7rfHyne5PQ_Wmt8qdMYFDzurdnEAUR7U2bxbDQ";

// Get all admins with Supabase UIDs
try {
    $stmt = $conn->prepare("SELECT admin_id, admin_name, admin_email, admin_position, admin_password, supabase_uid FROM admin WHERE supabase_uid IS NOT NULL");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $results = [];
    
    foreach ($admins as $admin) {
        // First, get a token by signing in
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $supabase_url . "/auth/v1/token?grant_type=password");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        $auth_data = [
            'email' => $admin['admin_email'],
            'password' => $admin['admin_password'] // Note: This only works if the password is still in plain text
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
        
        $result = [
            'admin_id' => $admin['admin_id'],
            'admin_name' => $admin['admin_name'],
            'admin_email' => $admin['admin_email'],
            'supabase_uid' => $admin['supabase_uid'],
            'auth_status' => $http_code
        ];
        
        if ($http_code === 200) {
            $response_data = json_decode($response, true);
            if (isset($response_data['access_token'])) {
                // Now update the user metadata
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $supabase_url . "/auth/v1/user");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                
                $update_data = [
                    'data' => [
                        'name' => $admin['admin_name'],
                        'position' => $admin['admin_position'],
                        'full_name' => $admin['admin_name'] // Add full_name for display name
                    ]
                ];
                
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($update_data));
                
                $auth_headers = [
                    'Content-Type: application/json',
                    'apikey: ' . $supabase_key,
                    'Authorization: Bearer ' . $response_data['access_token']
                ];
                
                curl_setopt($ch, CURLOPT_HTTPHEADER, $auth_headers);
                
                $update_response = curl_exec($ch);
                $update_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                
                curl_close($ch);
                
                $result['update_status'] = $update_http_code;
                $result['update_response'] = json_decode($update_response, true);
            }
        } else {
            // If direct login fails, try admin login with service role
            // This would require a service role key which is not provided in this example
            $result['auth_error'] = json_decode($response, true);
        }
        
        $results[] = $result;
    }
    
    echo json_encode(['success' => true, 'results' => $results]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

?>
