<?php
session_start(); // Start session

// Redirect if not logged in
if (!isset($_SESSION['customer_id'])) {
    echo "<script>alert('Please log in first!'); window.location.href='lag.php';</script>";
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fur_a_paw_intments";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $customer_id = $_SESSION['customer_id']; // Get logged-in user ID

    // Validate required fields
    if (empty($_POST['name']) || empty($_POST['size']) || empty($_POST['breed']) || empty($_POST['age']) || empty($_POST['gender']) || empty($_POST['vaccination_status'])) {
        echo "<script>alert('Please fill all required fields!'); history.back();</script>";
        exit();
    }

    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $size = $_POST['size'];
    $breed = htmlspecialchars($_POST['breed'], ENT_QUOTES, 'UTF-8');
    $age = (int)$_POST['age'];
    $gender = $_POST['gender'];
    $description = isset($_POST['description']) ? htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8') : null;
    $vaccination_status = $_POST['vaccination_status'];
    $date_administered = !empty($_POST['date_administered']) ? $_POST['date_administered'] : null;
    $expiry_date = !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $instructions = isset($_POST['instructions']) ? htmlspecialchars($_POST['instructions'], ENT_QUOTES, 'UTF-8') : null;

    // Create upload directory if it doesn't exist
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Handle Profile Photo Upload
    $profile_photo = null;
    if (!empty($_FILES['profile_photo']['name'])) {
        $profile_photo = $target_dir . time() . "_" . basename($_FILES['profile_photo']['name']);
        move_uploaded_file($_FILES['profile_photo']['tmp_name'], $profile_photo);
    }

    // Handle Vaccination File Upload
    $vaccination_file = null;
    if (!empty($_FILES['vaccination_file']['name'])) {
        $vaccination_file = $target_dir . time() . "_" . basename($_FILES['vaccination_file']['name']);
        move_uploaded_file($_FILES['vaccination_file']['tmp_name'], $vaccination_file);
    }

    // Insert pet details into database
    $stmt = $conn->prepare("INSERT INTO pet (customer_id, pet_name, pet_size, pet_breed, pet_age, pet_gender, pet_description, pet_profile_photo, pet_vaccination_status, pet_vaccination_card, pet_vaccination_date, pet_vaccination_expiry, pet_special_instruction)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssissssssss", $customer_id, $name, $size, $breed, $age, $gender, $description, $profile_photo, $vaccination_status, $vaccination_file, $date_administered, $expiry_date, $instructions);

    if ($stmt->execute()) {
        // Store the latest pet ID in the session for payment
        $_SESSION['latest_pet_id'] = $conn->insert_id;

        echo "<script>
                alert('Pet details saved successfully!');
                window.location.href='pet.php';
              </script>";
        exit();
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Details Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#petModal">Add Pet</button>
        
        
        <a href="seal.php" class="btn btn-success">Proceed to Payment</a>
        
    </div>

    <div class="modal fade" id="petModal" tabindex="-1" aria-labelledby="petModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pet Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="pet.php" method="POST" enctype="multipart/form-data">
                        <label class="form-label">Name:</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter pet's name" required>

                        <label class="form-label mt-2">Pet Size:</label>
                        <div class="d-flex">
                            <div class="form-check me-3">
                                <input type="radio" name="size" value="Small Dog" class="form-check-input" required> Small Dog
                            </div>
                            <div class="form-check me-3">
                                <input type="radio" name="size" value="Regular Dog" class="form-check-input" required> Regular Dog
                            </div>
                            <div class="form-check me-3">
                                <input type="radio" name="size" value="Large Dog" class="form-check-input" required> Large Dog
                            </div>
                            <div class="form-check">
                                <input type="radio" name="size" value="Regular Cat" class="form-check-input" required> Regular Cat
                            </div>
                        </div>

                        <label class="form-label mt-2">Breed:</label>
                        <input type="text" name="breed" class="form-control" placeholder="Enter pet's breed" required>

                        <label class="form-label mt-2">Age:</label>
                        <input type="number" name="age" class="form-control" placeholder="Enter pet's age in years" required>

                        <label class="form-label mt-2">Gender:</label>
                        <div class="d-flex">
                            <div class="form-check me-3">
                                <input type="radio" name="gender" value="Male" class="form-check-input" required> Male
                            </div>
                            <div class="form-check">
                                <input type="radio" name="gender" value="Female" class="form-check-input" required> Female
                            </div>
                        </div>

                        <label class="form-label mt-2">Description:</label>
                        <textarea name="description" class="form-control" placeholder="Provide any special details about your pet"></textarea>

                        <label class="form-label mt-2">Profile Photo:</label>
                        <input type="file" name="profile_photo" class="form-control" accept="image/*">

                        <label class="form-label mt-2">Vaccination Proof:</label>
                        <input type="file" name="vaccination_file" class="form-control" accept="image/*,application/pdf">

                        <label class="form-label mt-2">Vaccination Status:</label>
                        <div class="d-flex">
                            <div class="form-check me-3">
                                <input type="radio" name="vaccination_status" value="Vaccinated" class="form-check-input" required> Vaccinated
                            </div>
                            <div class="form-check">
                                <input type="radio" name="vaccination_status" value="Not Vaccinated" class="form-check-input" required> Not Vaccinated
                            </div>
                        </div>

                        <label class="form-label mt-2">Date Administered:</label>
                        <input type="date" name="date_administered" class="form-control">

                        <label class="form-label mt-2">Expiry Date:</label>
                        <input type="date" name="expiry_date" class="form-control">

                        <label class="form-label mt-2">Special Instructions:</label>
                        <textarea name="instructions" class="form-control" placeholder="Enter any special care instructions"></textarea>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-dark">Save and Go Back</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
