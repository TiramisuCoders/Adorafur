<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    error_log("Received POST data: " . print_r($_POST, true));
    error_log("Received FILES data: " . print_r($_FILES, true));

    $pet_id = $_POST['pet_id'];
    $name = $_POST['name'];
    $breed = $_POST['breed'];
    $pet_size = $_POST['pet_size'];
    $gender = $_POST['gender'];
    $description = $_POST['description'];
    $special_instructions = $_POST['special_instructions'];
    $vaccination_status = $_POST['vaccination_status'];
    
    // Handle date fields properly - use null if empty
    $date_administered = !empty($_POST['date_administered']) ? $_POST['date_administered'] : null;
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    
    $age_years = isset($_POST['pet_age_years']) ? intval($_POST['pet_age_years']) : 0;
    $age_months = isset($_POST['pet_age_months']) ? intval($_POST['pet_age_months']) : 0;
    
    // Format the age string
    $age = '';
    if ($age_years > 0) {
        $age .= $age_years . ' ' . ($age_years == 1 ? 'year' : 'years');
    }
    if ($age_months > 0) {
        if ($age_years > 0) {
            $age .= ' ';
        }
        $age .= $age_months . ' mos';
    }
    if (empty($age)) {
        $age = '0 years';
    }
    
    
    // Verify pet belongs to user
    $verify_query = "SELECT pet_id FROM pet WHERE pet_id = :pet_id AND customer_id = :c_id";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bindParam(':pet_id', $pet_id);
    $verify_stmt->bindParam(':c_id', $user_id);
    $verify_stmt->execute();
    
    if ($verify_stmt->rowCount() == 0) {
        $_SESSION['error_message'] = "You don't have permission to edit this pet";
        header("Location: profile.php");
        exit();
    }
    
    // Handle pet image upload
    $image_update = "";
    $params = [
        ':name' => $name,
        ':breed' => $breed,
        ':pet_size' => $pet_size,
        ':age' => $age,
        ':gender' => $gender,
        ':description' => $description,
        ':special_instructions' => $special_instructions,
        ':vaccination_status' => $vaccination_status,
        ':date_administered' => $date_administered,
        ':expiry_date' => $expiry_date,
        ':pet_id' => $pet_id,
        ':c_id' => $user_id
    ];
    
    if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] == 0) {
        $upload_dir = "uploads/pets/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = $user_id . "_" . time() . "_" . basename($_FILES['pet_image']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES['pet_image']['tmp_name']);
        if ($check !== false) {
            if (move_uploaded_file($_FILES['pet_image']['tmp_name'], $target_file)) {
                $image_update = ", pet_picture = :image_path";
                $params[':image_path'] = $target_file;
            }
        }
    }
    
    // Update pet information
    $update_query = "UPDATE pet SET 
                   pet_name = :name,
                   pet_breed = :breed,
                   pet_size = :pet_size,
                   pet_age = :age,
                   pet_gender = :gender,
                   pet_description = :description,
                   pet_special_instruction = :special_instructions,
                   pet_vaccination_status = :vaccination_status,
                   pet_vaccination_date_administered = :date_administered,
                   pet_vaccination_date_expiry = :expiry_date
                   $image_update
                   WHERE pet_id = :pet_id AND customer_id = :c_id";
    
    try {
        $stmt = $conn->prepare($update_query);
        $stmt->execute($params);
        
        if ($stmt->rowCount() > 0) {
            // $_SESSION['success_message'] = "Pet information updated successfully";
        } else {
            error_log("Pet update failed without throwing an exception");
            $_SESSION['error_message'] = "No changes were made to the pet information";
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        error_log("Error code: " . $e->getCode());
        error_log("Error trace: " . $e->getTraceAsString());
        $_SESSION['error_message'] = "Error updating pet information: " . $e->getMessage();
    }
}

if (isset($_SESSION['error_message'])) {
    error_log("Error message set: " . $_SESSION['error_message']);
} elseif (isset($_SESSION['success_message'])) {
    error_log("Success message set: " . $_SESSION['success_message']);
} else {
    error_log("No message set after processing");
}

header("Location: profile.php");
exit();
?>