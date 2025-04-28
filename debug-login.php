<?php
// Create this file in the same directory as login.php
// This will help us debug the login issue

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include("connect.php");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to check if a string is a valid bcrypt hash
function is_bcrypt_hash($hash) {
    return (strlen($hash) == 60 && substr($hash, 0, 4) === '$2y$');
}

echo "<h1>Login System Diagnostic</h1>";

// Check PostgreSQL connection
echo "<h2>Database Connection</h2>";
try {
    $conn->query("SELECT 1");
    echo "<p style='color: green;'>✓ PostgreSQL connection successful</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ PostgreSQL connection error: " . $e->getMessage() . "</p>";
}

// Check Supabase connection
echo "<h2>Supabase Connection</h2>";
$supabase_url = "https://ygbwanzobuielhttdzsw.supabase.co";
$supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlnYndhbnpvYnVpZWxodHRkenN3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM1MTY3NTMsImV4cCI6MjA1OTA5Mjc1M30.bIaP_7rfHyne5PQ_Wmt8qdMYFDzurdnEAUR7U2bxbDQ";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $supabase_url . "/auth/v1/health");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['apikey: ' . $supabase_key]);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    echo "<p style='color: green;'>✓ Supabase connection successful</p>";
} else {
    echo "<p style='color: red;'>✗ Supabase connection error: HTTP code " . $http_code . "</p>";
}

// Check customer table structure
echo "<h2>Database Structure</h2>";
try {
    $stmt = $conn->query("SELECT column_name, data_type, character_maximum_length 
                         FROM information_schema.columns 
                         WHERE table_name = 'customer'");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Column</th><th>Type</th><th>Max Length</th></tr>";
    
    $passwordColumn = null;
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['column_name'] . "</td>";
        echo "<td>" . $column['data_type'] . "</td>";
        echo "<td>" . $column['character_maximum_length'] . "</td>";
        echo "</tr>";
        
        if ($column['column_name'] === 'c_password') {
            $passwordColumn = $column;
        }
    }
    echo "</table>";
    
    // Check if password column has sufficient length
    if ($passwordColumn) {
        if ($passwordColumn['data_type'] !== 'character varying' || $passwordColumn['character_maximum_length'] < 60) {
            echo "<p style='color: red;'>✗ Password column may not be large enough for bcrypt hashes (needs varchar(60) minimum)</p>";
        } else {
            echo "<p style='color: green;'>✓ Password column type and length are sufficient</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Could not find c_password column</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error checking table structure: " . $e->getMessage() . "</p>";
}

// Check password hashes in database
echo "<h2>Password Hash Check</h2>";
try {
    $stmt = $conn->query("SELECT c_id, c_email, c_password FROM customer LIMIT 10");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Email</th><th>Password Hash</th><th>Valid bcrypt?</th></tr>";
    
    $validHashes = 0;
    $invalidHashes = 0;
    
    foreach ($customers as $customer) {
        $isValidHash = is_bcrypt_hash($customer['c_password']);
        echo "<tr>";
        echo "<td>" . $customer['c_id'] . "</td>";
        echo "<td>" . $customer['c_email'] . "</td>";
        echo "<td>" . substr($customer['c_password'], 0, 20) . "...</td>";
        echo "<td style='color: " . ($isValidHash ? "green" : "red") . ";'>" . ($isValidHash ? "Yes" : "No") . "</td>";
        echo "</tr>";
        
        if ($isValidHash) {
            $validHashes++;
        } else {
            $invalidHashes++;
        }
    }
    echo "</table>";
    
    echo "<p>Found $validHashes valid bcrypt hashes and $invalidHashes invalid hashes.</p>";
    
    if ($invalidHashes > 0) {
        echo "<p style='color: red;'>✗ Some passwords are not properly hashed with bcrypt</p>";
    } else if ($validHashes > 0) {
        echo "<p style='color: green;'>✓ All checked passwords are properly hashed with bcrypt</p>";
    } else {
        echo "<p style='color: orange;'>⚠ No customer records found to check</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error checking password hashes: " . $e->getMessage() . "</p>";
}

// Provide a fix button
echo "<h2>Fix Password Hashes</h2>";
echo "<p>If you see invalid password hashes above, click the button below to fix them:</p>";
echo "<form method='post'>";
echo "<input type='submit' name='fix_hashes' value='Fix Password Hashes' style='padding: 10px; background-color: #4CAF50; color: white; border: none; cursor: pointer;'>";
echo "</form>";

// Process fix if requested
if (isset($_POST['fix_hashes'])) {
    echo "<h3>Fixing Password Hashes...</h3>";
    
    try {
        $stmt = $conn->query("SELECT c_id, c_email, c_password FROM customer");
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $fixed = 0;
        $alreadyValid = 0;
        $errors = 0;
        
        foreach ($customers as $customer) {
            if (!is_bcrypt_hash($customer['c_password'])) {
                // Generate a proper bcrypt hash
                $tempPassword = "Temporary1!"; // Temporary password that meets requirements
                $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);
                
                // Update the customer's password
                $updateStmt = $conn->prepare("UPDATE customer SET c_password = ? WHERE c_id = ?");
                if ($updateStmt->execute([$hashedPassword, $customer['c_id']])) {
                    echo "<p>Fixed password hash for customer ID: " . $customer['c_id'] . " (" . $customer['c_email'] . ")</p>";
                    $fixed++;
                } else {
                    echo "<p style='color: red;'>Failed to fix password for customer ID: " . $customer['c_id'] . "</p>";
                    $errors++;
                }
            } else {
                $alreadyValid++;
            }
        }
        
        echo "<h3>Summary:</h3>";
        echo "<p>Fixed $fixed password hashes</p>";
        echo "<p>Found $alreadyValid already valid hashes</p>";
        echo "<p>Encountered $errors errors</p>";
        
        if ($fixed > 0) {
            echo "<div style='background-color: #f8d7da; color: #721c24; padding: 10px; border: 1px solid #f5c6cb; margin-top: 20px;'>";
            echo "<h3>Important:</h3>";
            echo "<p>Passwords have been reset to temporary values. Users will need to use the 'Forgot Password' feature to set new passwords.</p>";
            echo "</div>";
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Error fixing password hashes: " . $e->getMessage() . "</p>";
    }
}

// Test login function
echo "<h2>Test Login</h2>";
echo "<p>Use this form to test the login process with debug information:</p>";
echo "<form method='post'>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='test_email'>Email:</label><br>";
echo "<input type='email' id='test_email' name='test_email' required style='padding: 5px; width: 300px;'>";
echo "</div>";
echo "<div style='margin-bottom: 10px;'>";
echo "<label for='test_password'>Password:</label><br>";
echo "<input type='password' id='test_password' name='test_password' required style='padding: 5px; width: 300px;'>";
echo "</div>";
echo "<input type='submit' name='test_login' value='Test Login' style='padding: 10px; background-color: #007BFF; color: white; border: none; cursor: pointer;'>";
echo "</form>";

// Process test login
if (isset($_POST['test_login'])) {
    $email = $_POST['test_email'] ?? '';
    $password = $_POST['test_password'] ?? '';
    
    echo "<h3>Login Test Results:</h3>";
    
    // Check if user exists in database
    $stmt = $conn->prepare("SELECT c_id, c_email, c_password FROM customer WHERE c_email = ?");
    $stmt->execute([$email]);
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($customer) {
        echo "<p>✓ User found in database: ID " . $customer['c_id'] . "</p>";
        echo "<p>Password hash in database: " . substr($customer['c_password'], 0, 20) . "...</p>";
        echo "<p>Is valid bcrypt hash: " . (is_bcrypt_hash($customer['c_password']) ? "Yes" : "No") . "</p>";
        
        // Test Supabase authentication
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
        
        echo "<p>Supabase authentication HTTP code: " . $http_code . "</p>";
        
        if ($http_code === 200) {
            echo "<p style='color: green;'>✓ Supabase authentication successful</p>";
            
            // Update the password hash in the database
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE customer SET c_password = ? WHERE c_id = ?");
            if ($updateStmt->execute([$hashedPassword, $customer['c_id']])) {
                echo "<p style='color: green;'>✓ Updated password hash in database</p>";
            } else {
                echo "<p style='color: red;'>✗ Failed to update password hash in database</p>";
            }
            
            echo "<p style='color: green; font-weight: bold;'>Login would be successful!</p>";
        } else {
            echo "<p style='color: red;'>✗ Supabase authentication failed</p>";
            $response_data = json_decode($response, true);
            if (isset($response_data['error_description'])) {
                echo "<p>Error: " . $response_data['error_description'] . "</p>";
            } else if (isset($response_data['message'])) {
                echo "<p>Error: " . $response_data['message'] . "</p>";
            }
        }
    } else {
        echo "<p style='color: red;'>✗ User not found in database</p>";
        
        // Check if user exists in Supabase
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
        
        if ($http_code === 200) {
            echo "<p style='color: orange;'>⚠ User exists in Supabase but not in local database</p>";
        } else {
            echo "<p>User not found in Supabase either</p>";
        }
    }
}
?>
