<?php
// Start session if not already started
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    // Redirect to login page if not logged in
    header("Location: admin_login.php");
    exit();
}

// Include database connection
require_once '../connect.php';

// Initialize variables
$admin_name = $email = $password = $position = '';
$name_error = $email_error = $password_error = $position_error = '';
$success_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    // Get form data
    $admin_name = htmlspecialchars($_POST['admin_name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $repeatPassword = $_POST['repeatPassword'] ?? '';
    $position = htmlspecialchars($_POST['admin_position'] ?? '');
    
    $hasError = false;
    
    // Validate name
    if (empty($admin_name)) {
        $name_error = 'Name is required.';
        $hasError = true;
    } else if (!preg_match("/^[a-zA-Z\s'-]+$/", $admin_name)) {
        $name_error = 'Name must only contain letters, spaces, apostrophes, or dashes.';
        $hasError = true;
    }

    // Validate email
    if (empty($email)) {
        $email_error = 'Email is required.';
        $hasError = true;
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = 'Invalid email format.';
        $hasError = true;
    } else {
        // Check if email already exists in database
        $stmt = $conn->prepare("SELECT * FROM admin WHERE admin_email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $email_error = 'Email already registered.';
            $hasError = true;
        }
    }

    // Validate password
    if (empty($password)) {
        $password_error = 'Password is required.';
        $hasError = true;
    } else if (strlen($password) < 8 || strlen($password) > 12) {
        $password_error = 'Password must be between 8 and 12 characters.';
        $hasError = true;
    } else if (!preg_match('/[A-Z]/', $password)) {
        $password_error = 'Password must contain at least 1 uppercase letter.';
        $hasError = true;
    } else if (!preg_match('/\d/', $password)) {
        $password_error = 'Password must contain at least 1 number.';
        $hasError = true;
    } else if (!preg_match('/[\W_]/', $password)) {
        $password_error = 'Password must contain at least 1 special character.';
        $hasError = true;
    } else if ($password !== $repeatPassword) {
        $password_error = 'Passwords do not match.';
        $hasError = true;
    }
    
    // Validate position
    if (empty($position)) {
        $position_error = "Position is required.";
        $hasError = true;
    }
    
    // If no errors, create admin account
    if (!$hasError) {
        try {
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert admin data into database
            $stmt = $conn->prepare("INSERT INTO admin (admin_name, admin_email, admin_password, admin_position) VALUES (:admin_name, :admin_email, :admin_password, :admin_position)");
            $stmt->bindParam(':admin_name', $admin_name, PDO::PARAM_STR);
            $stmt->bindParam(':admin_email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':admin_password', $hashed_password, PDO::PARAM_STR);
            $stmt->bindParam(':admin_position', $position, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                // Set success message
                $_SESSION['success_message'] = "Admin account created successfully";
                header("Location: admin_profile.php");
                exit();
            } else {
                throw new Exception("Failed to create admin in database");
            }
        } catch(Exception $e) {
            error_log("Error creating admin: " . $e->getMessage());
            $error_message = "An error occurred while creating admin account: " . $e->getMessage();
        }
    }
}
?>
