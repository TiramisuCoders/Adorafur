<?php
    // $firstName = '';
    // $lastName = '';
    $firstname = "";
    $lastname = "";
    $email = "";
    $contactNumber = "";
    $firstname_error =null;
    $lastname_error = null;
    $email_error = null; 
    $contact_error = null;
    $password_error = null;

// Start session at the very beginning
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
include("connect.php");

// Display login errors if any
if (isset($_SESSION['login_error'])) {
    echo "<div class='alert alert-danger'>" . $_SESSION['login_error'] . "</div>";
    unset($_SESSION['login_error']);
}

// Process form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'register':
                handleRegister($conn);
                break;
            case 'login':
                handleLogin($conn);
                break;
            case 'forgotPassword':
                handleForgotPassword($conn);
                break;
        }
    }
}

function handleRegister($conn) {
    global $firstname, $lastname, $email, $contactNumber;
    global $firstname_error, $lastname_error, $email_error, $contact_error, $password_error, $firstname;
    $hasError = false;

    $firstname = htmlspecialchars($_POST['firstName'] ?? '');
    $lastname = htmlspecialchars($_POST['lastName'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $contactNumber = htmlspecialchars($_POST['contactNumber'] ?? '');
    $password = $_POST['password'] ?? '';
    $repeatPassword = $_POST['repeatPassword'] ?? '';
    
    if (!preg_match("/^[a-zA-Z-' ]+$/", $firstname)) {
        $firstname_error = 'First name must only contain letters, apostrophes, or dashes.';
        $hasError = true;
    }

    if (!preg_match("/^[a-zA-Z-' ]+$/", $lastname)) {
        $lastname_error = 'Last name must only contain letters, apostrophes, or dashes.';
        $hasError = true;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = 'Invalid email format.';
        $hasError = true;
    }
    
    if (!preg_match('/^09[0-9]{9}$/', $contactNumber)) {
        $contact_error = "Invalid Philippine phone number format.";
        $hasError = true;
    }

    if ($password !== $repeatPassword) {
        $password_error= 'Passwords do not match.';
        $hasError = true;
    } 

    if (!$hasError && $email) {
        $stmt = $conn->prepare("SELECT * FROM customer WHERE c_email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $email_error= 'Email already registered.';
            $hasError = true;
        } 
    }

    if (!$hasError) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO customer (c_first_name, c_last_name, c_email, c_contact_number, c_password) 
                                VALUES (:firstName, :lastName, :email, :contactNumber, :password)");
        $stmt->bindParam(':firstName', $firstname);
        $stmt->bindParam(':lastName', $lastname);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contactNumber', $contactNumber);
        $stmt->bindParam(':password', $hashedPassword);
    
        if ($stmt->execute()) {
            $_SESSION['registration_success'] = true; // Store success in session
            // / Clear form data after successful registration
            $firstname = "";
            $lastname = "";
            $email = "";
            $contactNumber = "";
        } 
    }
    $_SESSION['register_error'] = true; // Flag to show the register modal with errors
    return false;
}

function handleLogin($conn) {
    try {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Check if user is an admin
        $stmt = $conn->prepare("SELECT admin_id, admin_password FROM admin WHERE admin_email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && $password === $admin['admin_password']) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            header("Location: admin/admin_home.php");
            exit();
        }

        // If not an admin, check customer login
        $stmt = $conn->prepare("SELECT c_id, c_password FROM customer WHERE c_email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($customer && password_verify($password, $customer['c_password'])) {
            // Set both session variables for compatibility
            $_SESSION['c_id'] = $customer['c_id'];
            $_SESSION['customer_id'] = $customer['c_id'];
            
            // For debugging (optional - you can remove this later)
            $_SESSION['login_time'] = date('Y-m-d H:i:s');
            $_SESSION['login_email'] = $email;
            
            // Redirect to profile page
            header("Location: profile.php");
            exit();
        }

        // If we get here, login failed - set error but don't redirect
        $_SESSION['login_error'] = "Invalid email or password.";
        return false;
    } catch (PDOException $e) {
        $_SESSION['login_error'] = "Database error: " . $e->getMessage();
        return false;
    }
}

function handleForgotPassword($conn) {
    // Your forgot password logic here
    // This is just a placeholder
    echo "Password reset functionality not implemented yet.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOG IN PAGE</title>
    <link rel="stylesheet" href="log_in.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        <?php if ($firstname_error): ?> .firstname-error { display: block; } <?php endif; ?>
        <?php if ($lastname_error): ?> .lastname-error { display: block; } <?php endif; ?>
        <?php if ($email_error): ?> .email-error { display: block; } <?php endif; ?>
        <?php if ($contact_error): ?> .contact-error { display: block; } <?php endif; ?>
        <?php if ($password_error): ?> .password-error { display: block; } <?php endif; ?>
    </style>
</head>
<body>

    <!-- Login Modal -->
    <div class="modal" id="loginModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="login-body">
                    <h5 class="modal-title">Paw's up, fur-furfriend</h5>
                    <h2 class="modal-title">Welcome back to your pet's favorite spot!</h2>

                    <form id="loginForm" action="" method="POST">
                        <input type="hidden" name="action" value="login">
                        <div class="mb-3 d-flex justify-content-center">
                            <input type="email" class="form-control w-50" name="email" required placeholder="Enter Email">
                        </div>
                        <div class="mb-3 d-flex justify-content-center">
                            <input type="password" class="form-control w-50" name="password" required placeholder="Enter Password">
                        </div>
                        <?php if (isset($_SESSION['login_error'])): ?>
                            <div class="alert alert-danger text-center w-50 mx-auto">
                                <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
                            </div>
                        <?php endif; ?>
                        <button type="submit" id="loginbut" class="btn btn-primary">Login</button>
                        <p class="mt-3 text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" id="not-yet-register">Not yet registered?</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-dialog modal-xl">
                <div class="modal-content border-0 p-0">
                    <div class="row g-0">
                        <!-- Form Side -->
                        <div class="col-md-6 form-side">
                            <div class="p-4 p-md-4">
                                <h2 class="fw-bold mb-2">Register</h2>
                                <p class="text-muted mb-2">Fill in this form to create an account</p>
                                <hr>
                                <form action="" id="registerForm" method="POST">
                                    <input type="hidden" name="action" value="register">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="mb-1">
                                                <label for="firstName">First Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="firstName" name="firstName" value="<?php echo $firstname?>" required>
                                                <?php if ($firstname_error): ?>
                                                    <p class="error firstname-error"><?php echo $firstname_error; ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label for="lastName">Last Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="lastName" name="lastName" value="<?php echo $lastname; ?>" placeholder="Enter Last Name" required>      
                                            <?php if ($lastname_error): ?>
                                                <p class="error lastname-error"><?php echo $lastname_error; ?></p>
                                            <?php endif; ?>

                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="email">Email <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" placeholder="Enter Email" required>
                                            <?php if ($email_error): ?>
                                                <p class="error email-error"><?php echo $email_error; ?></p>
                                            <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="contactNumber">Contact Number <span class="text-danger">*</span></label>  
                                                <input type="tel" class="form-control" id="contactNumber" name="contactNumber" value="<?php echo $contactNumber; ?>" placeholder="Contact Number" required>
                                            <?php if ($contact_error): ?>
                                                <p class="error contact-error"><?php echo $contact_error; ?></p>
                                            <?php endif; ?>

                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="mb-3 password-input">
                                                <label for="password">Password <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>  
                                                <span class="validation-icon"></span>
                                                <?php if ($password_error): ?>
                                                <p class="error password-error"><?php echo $password_error; ?></p>
                                            <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="mb-3 password-input">
                                                <label for="repeatPassword">Repeat Password <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control" id="repeatPassword" name="repeatPassword" placeholder="Repeat Password" required>
                                                <span class="validation-icon"></span>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary create-button w-100" id="create-but">Create</button>
                                        </div>
                                    </div>
                                    
                                    <p class="text-center mt-4 mb-0">
                                        Already have an account? 
                                        <a href="#" class="sign-in-link" id="sign-in" data-bs-target="#loginModal" data-bs-toggle="modal">Sign in</a>
                                    </p>  

                                </form>
                            </div>
                        </div>
                        
                        <!-- Image Side -->
                        <div class="col-md-6 d-none d-md-block image-side p-0 position-relative">
                            <button type="button" class="btn-close position-absolute" data-bs-dismiss="modal" aria-label="Close"></button>
                            <img src="Register-dog.png" alt="Happy dog" class="dog-image">
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Bootstrap Congrats Modal -->
<div class="modal fade" id="congratsModal" tabindex="-1" aria-labelledby="congratsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body" id="congrats-body">
                <h5 class="modal-title">Congratulations!</h5>
                <h2 class="modal-title">You are now a certified member of Adorafur!</h2>
                <p>
                To complete your registration, please fill out your account 
                details in the Profile tab once you log in. Additionally, a confirmation 
                message will be sent to your email shortly. Thank you!
                </p>

                <button type="button" class="btn btn-primary" id="returnToLogin">Return</button>

            </div>
        </div>
    </div>
</div>

<!-- JavaScript to Auto-Show the Modal If Registration Was Successful -->
<?php if (isset($_SESSION['registration_success'])): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var congratsModal = new bootstrap.Modal(document.getElementById('congratsModal'));
            congratsModal.show();
        });
    </script>
    <?php unset($_SESSION['registration_success']); // Remove session variable after showing modal ?>
<?php endif; ?>


<!-- JavaScript to handle modals -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Check if registration was successful
        <?php if (isset($_SESSION['registration_success'])): ?>
            var congratsModal = new bootstrap.Modal(document.getElementById('congratsModal'));
            congratsModal.show();
            <?php unset($_SESSION['registration_success']); // Clear session after showing modal ?>
        <?php endif; ?>

        // Show login modal if there's a login error
        <?php if (isset($_SESSION['login_error'])): ?>
            var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            loginModal.show();
        <?php endif; ?>

        // Show register modal if there are registration errors
        <?php if (isset($_SESSION['register_error']) || $firstname_error || $lastname_error || $email_error || $contact_error || $password_error): ?>
            var registerModal = new bootstrap.Modal(document.getElementById('registerModal'));
            registerModal.show();
            <?php if (isset($_SESSION['register_error'])): ?>
                <?php unset($_SESSION['register_error']); ?>
            <?php endif; ?>
        <?php endif; ?>

        // When the return button is clicked, close congratsModal and open loginModal
        document.getElementById("returnToLogin").addEventListener("click", function() {
            var congratsModalEl = document.getElementById('congratsModal');
            var congratsModal = bootstrap.Modal.getInstance(congratsModalEl);
            congratsModal.hide(); // Hide Congrats Modal

            var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            setTimeout(function() {
                loginModal.show(); // Show Login Modal after a slight delay
            }, 500);
        });
    });
</script>


</body>
</html>

