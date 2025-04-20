<?php
// Create this file in the same directory as login.php
// This will help us debug the registration process

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include("connect.php");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to test Supabase connection
function testSupabaseConnection() {
    $supabase_url = "https://ygbwanzobuielhttdzsw.supabase.co";
    $supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlnYndhbnpvYnVpZWxodHRkenN3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM1MTY3NTMsImV4cCI6MjA1OTA5Mjc1M30.bIaP_7rfHyne5PQ_Wmt8qdMYFDzurdnEAUR7U2bxbDQ";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $supabase_url . "/auth/v1/health");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: ' . $supabase_key]);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'success' => $http_code === 200,
        'http_code' => $http_code,
        'response' => $response
    ];
}

// Function to test registration with Supabase
function testRegistration($email, $password, $firstName, $lastName) {
    $supabase_url = "https://ygbwanzobuielhttdzsw.supabase.co";
    $supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlnYndhbnpvYnVpZWxodHRkenN3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM1MTY3NTMsImV4cCI6MjA1OTA5Mjc1M30.bIaP_7rfHyne5PQ_Wmt8qdMYFDzurdnEAUR7U2bxbDQ";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $supabase_url . "/auth/v1/signup");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    
    // Prepare the data for Supabase Auth
    $auth_data = [
        'email' => $email,
        'password' => $password,
        'data' => [
            'first_name' => $firstName,
            'last_name' => $lastName
        ],
        'email_confirm' => false // This will trigger email verification
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
        'success' => $http_code === 200 || $http_code === 201,
        'http_code' => $http_code,
        'response' => $response
    ];
}

// Function to test database insertion
function testDatabaseInsertion($conn, $firstName, $lastName, $email, $contactNumber, $password) {
    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO customer (c_first_name, c_last_name, c_email, c_contact_number, c_password) 
                                VALUES (:firstName, :lastName, :email, :contactNumber, :password)");
        $stmt->bindParam(':firstName', $firstName);
        $stmt->bindParam(':lastName', $lastName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contactNumber', $contactNumber);
        $stmt->bindParam(':password', $hashedPassword);
    
        $result = $stmt->execute();
        
        return [
            'success' => $result,
            'error' => $result ? null : $stmt->errorInfo()
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Main page content
echo "<h1>Registration Process Diagnostic</h1>";

// Test Supabase connection
echo "<h2>Supabase Connection Test</h2>";
$supabaseTest = testSupabaseConnection();
if ($supabaseTest['success']) {
    echo "<p style='color: green;'>✓ Supabase connection successful</p>";
} else {
    echo "<p style='color: red;'>✗ Supabase connection failed: HTTP code " . $supabaseTest['http_code'] . "</p>";
    echo "<pre>" . htmlspecialchars($supabaseTest['response']) . "</pre>";
}

// Test form for registration
echo "<h2>Test Registration</h2>";
echo "<form method='post'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='test_email'>Email:</label><br>";
echo "<input type='email' id='test_email' name='test_email' required style='padding: 5px; width: 300px;' value='test" . time() . "@example.com'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='test_password'>Password:</label><br>";
echo "<input type='password' id='test_password' name='test_password' required style='padding: 5px; width: 300px;' value='Test123!'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='test_firstname'>First Name:</label><br>";
echo "<input type='text' id='test_firstname' name='test_firstname' required style='padding: 5px; width: 300px;' value='Test'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='test_lastname'>Last Name:</label><br>";
echo "<input type='text' id='test_lastname' name='test_lastname' required style='padding: 5px; width: 300px;' value='User'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='test_contact'>Contact Number:</label><br>";
echo "<input type='text' id='test_contact' name='test_contact' required style='padding: 5px; width: 300px;' value='09123456789'>";
echo "</div>";
echo "<input type='submit' name='test_register' value='Test Registration' style='padding: 10px; background-color: #007BFF; color: white; border: none; cursor: pointer;'>";
echo "</form>";

// Process test registration
if (isset($_POST['test_register'])) {
    $email = $_POST['test_email'] ?? '';
    $password = $_POST['test_password'] ?? '';
    $firstName = $_POST['test_firstname'] ?? '';
    $lastName = $_POST['test_lastname'] ?? '';
    $contactNumber = $_POST['test_contact'] ?? '';
    
    echo "<h3>Registration Test Results:</h3>";
    
    // Step 1: Test Supabase registration
    echo "<h4>Step 1: Supabase Registration</h4>";
    $supabaseRegistration = testRegistration($email, $password, $firstName, $lastName);
    
    if ($supabaseRegistration['success']) {
        echo "<p style='color: green;'>✓ Supabase registration successful</p>";
    } else {
        echo "<p style='color: red;'>✗ Supabase registration failed: HTTP code " . $supabaseRegistration['http_code'] . "</p>";
        echo "<pre>" . htmlspecialchars($supabaseRegistration['response']) . "</pre>";
        
        // Check if it's because the user already exists
        $response = json_decode($supabaseRegistration['response'], true);
        if (isset($response['message']) && strpos($response['message'], 'already registered') !== false) {
            echo "<p style='color: orange;'>⚠ User already exists in Supabase. This is expected if you're testing with the same email.</p>";
        }
    }
    
    // Step 2: Test database insertion
    echo "<h4>Step 2: Database Insertion</h4>";
    $databaseInsertion = testDatabaseInsertion($conn, $firstName, $lastName, $email, $contactNumber, $password);
    
    if ($databaseInsertion['success']) {
        echo "<p style='color: green;'>✓ Database insertion successful</p>";
    } else {
        echo "<p style='color: red;'>✗ Database insertion failed: " . htmlspecialchars($databaseInsertion['error']) . "</p>";
        
        // Check if it's because the email already exists
        if (strpos($databaseInsertion['error'], 'duplicate key') !== false || 
            strpos($databaseInsertion['error'], 'UNIQUE constraint failed') !== false) {
            echo "<p style='color: orange;'>⚠ Email already exists in database. This is expected if you're testing with the same email.</p>";
        }
    }
    
    // Step 3: Verify the user exists in the database
    echo "<h4>Step 3: Verify User in Database</h4>";
    $stmt = $conn->prepare("SELECT c_id, c_email, c_password FROM customer WHERE c_email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($customer) {
        echo "<p style='color: green;'>✓ User found in database: ID " . $customer['c_id'] . "</p>";
        echo "<p>Password hash in database: " . substr($customer['c_password'], 0, 20) . "...</p>";
        echo "<p>Is valid bcrypt hash: " . (substr($customer['c_password'], 0, 4) === '$2y$' ? "Yes" : "No") . "</p>";
    } else {
        echo "<p style='color: red;'>✗ User not found in database</p>";
    }
    
    // Summary
    echo "<h3>Summary:</h3>";
    if ($supabaseRegistration['success'] || (isset($response['message']) && strpos($response['message'], 'already registered') !== false)) {
        if ($databaseInsertion['success'] || $customer) {
            echo "<p style='color: green;'>✓ Registration process is working correctly</p>";
        } else {
            echo "<p style='color: red;'>✗ Registration process has issues with database insertion</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Registration process has issues with Supabase registration</p>";
    }
}
?>
