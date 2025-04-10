<?php
// Start session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Include database connection
require_once '../connect.php';

// Enable error reporting for debugging but capture errors instead of displaying them
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set content type to JSON
header('Content-Type: application/json');

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_admin') {
        try {
            // Get form data
            $admin_name = htmlspecialchars($_POST['admin_name'] ?? '');
            $email = htmlspecialchars($_POST['admin_email'] ?? '');
            $position = htmlspecialchars($_POST['admin_position'] ?? '');
            $supabase_uid = $_POST['supabase_uid'] ?? '';
            $password = $_POST['password'] ?? ''; // Get the plain text password
            
            // Log the data for debugging
            error_log("Creating admin with: name=$admin_name, email=$email, position=$position, uid=$supabase_uid");
            
            // Validate data
            if (empty($admin_name) || empty($email) || empty($position) || empty($supabase_uid) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'All fields are required']);
                exit();
            }
            
            // Check if email already exists
            $checkEmailStmt = $conn->prepare("SELECT admin_id FROM admin WHERE admin_email = :email");
            $checkEmailStmt->bindParam(':email', $email);
            $checkEmailStmt->execute();
            
            if ($checkEmailStmt->rowCount() > 0) {
                // Email exists, update the supabase_uid and password
                $admin = $checkEmailStmt->fetch(PDO::FETCH_ASSOC);
                $updateStmt = $conn->prepare("UPDATE admin SET supabase_uid = :supabase_uid, admin_password = :admin_password WHERE admin_id = :admin_id");
                $updateStmt->bindParam(':supabase_uid', $supabase_uid);
                $updateStmt->bindParam(':admin_password', $password); // Store the plain text password
                $updateStmt->bindParam(':admin_id', $admin['admin_id']);
                
                if ($updateStmt->execute()) {
                    error_log("Updated existing admin with Supabase UID and password");
                    echo json_encode(['success' => true, 'message' => 'Admin updated with Supabase authentication']);
                } else {
                    error_log("Failed to update admin: " . print_r($updateStmt->errorInfo(), true));
                    echo json_encode(['success' => false, 'message' => 'Failed to update admin']);
                }
            } else {
                // Insert new admin with plain text password
                $stmt = $conn->prepare("INSERT INTO admin (admin_name, admin_email, admin_position, supabase_uid, admin_password) VALUES (:admin_name, :admin_email, :admin_position, :supabase_uid, :admin_password)");
                $stmt->bindParam(':admin_name', $admin_name, PDO::PARAM_STR);
                $stmt->bindParam(':admin_email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':admin_position', $position, PDO::PARAM_STR);
                $stmt->bindParam(':supabase_uid', $supabase_uid, PDO::PARAM_STR);
                $stmt->bindParam(':admin_password', $password, PDO::PARAM_STR); // Store the plain text password
                
                if ($stmt->execute()) {
                    error_log("Admin created successfully");
                    echo json_encode(['success' => true, 'message' => 'Admin created successfully']);
                } else {
                    error_log("Failed to create admin in database: " . print_r($stmt->errorInfo(), true));
                    echo json_encode(['success' => false, 'message' => 'Failed to create admin in database']);
                }
            }
        } catch(PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        } catch(Exception $e) {
            error_log("General error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
