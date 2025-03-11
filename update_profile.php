<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("connect.php");

if (!isset($_SESSION['c_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['c_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $socials = $_POST['socials'];
    
    // Handle password change if provided
    $password_update = "";
    $params = [
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':contact' => $contact,
        ':email' => $email,
        ':address' => $address,
        ':socials' => $socials,
        ':c_id' => $user_id
    ];
    
    if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
        // Verify current password
        $current_password = $_POST['current_password'];
        $password_query = "SELECT c_password FROM customer WHERE c_id = :c_id";
        $password_stmt = $conn->prepare($password_query);
        $password_stmt->bindParam(':c_id', $user_id);
        $password_stmt->execute();
        $password_row = $password_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (password_verify($current_password, $password_row['c_password'])) {
            $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $password_update = ", customer_password = :new_password";
            $params[':new_password'] = $new_password;
        } else {
            $_SESSION['error_message'] = "Current password is incorrect";
            header("Location: profile.php");
            exit();
        }
    }
    
    // Handle profile picture upload
    $profile_picture_update = "";
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $upload_dir = "uploads/profiles/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = $user_id . "_" . time() . "_" . basename($_FILES['profile_picture']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES['profile_picture']['tmp_name']);
        if ($check !== false) {
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
                $profile_picture_update = ", profile_picture = :profile_picture";
                $params[':profile_picture'] = $target_file;
            }
        }
    }
    
    // Update user information
    $update_fields = [];
$params = [':c_id' => $user_id];

if (!empty($first_name)) {
    $update_fields[] = "c_first_name = :first_name";
    $params[':first_name'] = $first_name;
}
if (!empty($last_name)) {
    $update_fields[] = "c_last_name = :last_name";
    $params[':last_name'] = $last_name;
}
if (!empty($contact)) {
    $update_fields[] = "c_contact_number = :contact";
    $params[':contact'] = $contact;
}
if (!empty($email)) {
    $update_fields[] = "c_email = :email";
    $params[':email'] = $email;
}
if (!empty($address)) {
    $update_fields[] = "c_address = :address";
    $params[':address'] = $address;
}
if (!empty($socials)) {
    $update_fields[] = "c_mode_of_communication = :socials";
    $params[':socials'] = $socials;
}

// Handle password update
if (!empty($_POST['current_password']) && !empty($_POST['new_password'])) {
    $update_fields[] = "c_password = :new_password";
    $params[':new_password'] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
}

// Handle profile picture update
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    $upload_dir = "uploads/profiles/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_name = $user_id . "_" . time() . "_" . basename($_FILES['profile_picture']['name']);
    $target_file = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
        $update_fields[] = "profile_picture = :profile_picture";
        $params[':profile_picture'] = $target_file;
    }
}

    try {
        // If there are fields to update, build the query dynamically
if (!empty($update_fields)) {
    $update_query = "UPDATE customer SET " . implode(", ", $update_fields) . " WHERE c_id = :c_id";

    $stmt = $conn->prepare($update_query);
    $stmt->execute($params);
    $_SESSION['success_message'] = "Profile updated successfully";
} else {
    echo "No changes made!";
}
exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error updating profile: " . $e->getMessage();
    }
}

header("Location: profile.php");
exit();
?>