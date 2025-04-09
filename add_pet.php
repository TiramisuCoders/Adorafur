<?php

// At the top of add_pet.php, add:
error_log("POST data: " . print_r($_POST, true));

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("connect.php");

if (!isset($_SESSION['c_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['c_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // error_log("Received POST data: " . print_r($_POST, true));
    // error_log("Received FILES data: " . print_r($_FILES, true));

    $hasError = false;

    $pet_id = $_POST['pet_id'];
    
    $pet_name = $_POST['pet_name'];
    $pet_size = $_POST['pet_size'];
    $breed = $_POST['breed'];
    $years = isset($_POST['pet_age_years']) ? intval($_POST['pet_age_years']) : 
         (isset($_POST['age_years']) ? intval($_POST['age_years']) : 0);

    if ($years == 1) {
        $age = "1 year";
    } elseif ($years > 1) {
        $age = "$years years";
    } else {
        $age = "0 years"; 
    }

    $gender = $_POST['gender'];
    $description = $_POST['description'];
    $special_instructions = !empty($_POST['special_instructions']) ? $_POST['special_instructions'] : '';
    $vaccination_status = $_POST['vaccination_status'];
    $date_administered = $_POST['date_administered'];
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;

    // Initialize error variables
    $pet_photo_error = null;
    $vaccination_file_error = null;
    $date_administered_error = null;

    error_log("PET PHOTO FILE SIZE: " . $_FILES['pet_photo']['size']);  // <- Add this line

    // Validate pet photo file size
    if (isset($_FILES['pet_photo']) && $_FILES['pet_photo']['size'] > 0) {
        if ($_FILES['pet_photo']['size'] > 5242880) {
            $pet_photo_error = "File is too large. Maximum size is 5MB.";
            $hasError = true;
        }
    }
    // Validate vaccination file size
    if (isset($_FILES['vaccination_file']) && $_FILES['vaccination_file']['size'] > 0) {
        if ($_FILES['vaccination_file']['size'] > 5242880) {
            $vaccination_file_error = "malaki.";
            $hasError = true;
        }
    }
    
    // Validate date administered
    if (!empty($date_administered)) {
        $current_date = date('Y-m-d');
        
        if ($date_administered < $current_date) {
            $date_administered_error = "ha";
            $hasError = true;
        } 
        
        if ($date_administered > $current_date ){
            $date_administered_error = "Invalid date.";
            $hasError = true;
        }
    }
    

    // If there are errors, store them in session and redirect back
    if ($hasError) {
        $_SESSION['pet_register_error'] = true;
        $_SESSION['pet_photo_error'] = $pet_photo_error;
        $_SESSION['vaccination_file_error'] = $vaccination_file_error;
        $_SESSION['date_administered_error'] = $date_administered_error;
        
        // Store form data in session to repopulate the form
        $_SESSION['pet_form_data'] = [
            'pet_name' => $pet_name,
            'pet_size' => $pet_size,
            'breed' => $breed,
            'age_years' => $years,
            'gender' => $gender,
            'description' => $description,
            'vaccination_status' => $vaccination_status,
            'date_administered' => $date_administered,
            'expiry_date' => $expiry_date,
            'special_instructions' => $special_instructions
        ];
        
        header("Location: Profile.php");
        exit();
    }
    
    // Map pet size values to database values
    $petSizeMap = [
        'small_dog' => 'Small',
        'regular_dog' => 'Regular',
        'large_dog' => 'Large',
        'regular_cat' => 'Cat'
    ];
    
    // Convert pet size to database format
    $dbPetSize = isset($petSizeMap[$pet_size]) ? $petSizeMap[$pet_size] : $pet_size;
    
    // Log the conversion for debugging
    // error_log("Original pet size: " . $pet_size . ", Converted to: " . $dbPetSize);
    
    // Handle pet photo upload
    $image_path = "";
    if (isset($_FILES['pet_photo']) && $_FILES['pet_photo']['error'] == 0) {
        $upload_dir = "uploads/pets";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = $user_id . "_" . time() . "_" . basename($_FILES['pet_photo']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES['pet_photo']['tmp_name']);
        if ($check !== false) {
            if (move_uploaded_file($_FILES['pet_photo']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            }
        }
    }
    
    // Handle vaccination file upload
    $vaccination_file = "";
    if (isset($_FILES['vaccination_file']) && $_FILES['vaccination_file']['error'] == 0) {
        $upload_dir = "uploads/vaccinations/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = $user_id . "_" . time() . "_" . basename($_FILES['vaccination_file']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['vaccination_file']['tmp_name'], $target_file)) {
            $vaccination_file = $target_file;
        }
    }

    // Convert gender to proper case if needed
    $gender = ucfirst(strtolower($_POST['gender']));
    
    // Map vaccination status values
    $vaccinationMap = [
        'vaccinated' => 'Vaccinated',
        'not_vaccinated' => 'Not Vaccinated'
    ];
    
    // Convert vaccination status to database format
    $dbVaccinationStatus = isset($vaccinationMap[$vaccination_status]) ? $vaccinationMap[$vaccination_status] : $vaccination_status;
    
    // Insert new pet
    $insert_query = "INSERT INTO pet (customer_id, pet_name, pet_size, pet_breed, pet_age, pet_gender, pet_description, 
                    pet_special_instruction, pet_vaccination_status, pet_vaccination_date_administered, pet_vaccination_date_expiry, pet_picture, pet_vaccination_card) 
                    VALUES (:c_id, :pet_name, :pet_size, :breed, :age, :gender, :description, 
                    :special_instructions, :vaccination_status, :date_administered, :expiry_date, :image_path, :vaccination_file)";

    error_log("Attempting to insert new pet with data: " . print_r([
        'c_id' => $user_id,
        'pet_name' => $pet_name,
        'pet_size' => $dbPetSize, // Use the mapped size value
        'breed' => $breed,
        'age' => $age,
        'gender' => $gender,
        'description' => $description,
        'special_instructions' => $special_instructions,
        'vaccination_status' => $dbVaccinationStatus,
        'date_administered' => $date_administered,
        'expiry_date' => $expiry_date,
        'image_path' => $image_path,
        'vaccination_file' => $vaccination_file
    ], true));
    
    try {
        $stmt = $conn->prepare($insert_query);
        $stmt->bindParam(':c_id', $user_id);
        $stmt->bindParam(':pet_name', $pet_name);
        $stmt->bindParam(':pet_size', $dbPetSize); // Use the mapped size value
        $stmt->bindParam(':breed', $breed);
        $stmt->bindParam(':age', $age);
        $stmt->bindParam(':gender', $gender);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':special_instructions', $special_instructions);
        $stmt->bindParam(':vaccination_status', $dbVaccinationStatus);
        $stmt->bindParam(':date_administered', $date_administered);
        $stmt->bindParam(':expiry_date', $expiry_date);
        $stmt->bindParam(':image_path', $image_path);
        $stmt->bindParam(':vaccination_file', $vaccination_file);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // $_SESSION['success_message'] = "Pet added successfully";
        } else {
            error_log("Pet insertion failed without throwing an exception");
            $_SESSION['error_message'] = "Error adding pet: No rows affected";
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        error_log("Error code: " . $e->getCode());
        error_log("Error trace: " . $e->getTraceAsString());
        $_SESSION['error_message'] = "Error adding pet: " . $e->getMessage();
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