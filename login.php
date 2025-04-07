<?php
    // $firstName = '';
    // $lastName = '';
    $firstname = "";
    $lastname = "";
    $email = "";
    $contactNumber = "";
    $firstname_error = null;
    $lastname_error = null;
    $email_error = null; 
    $contact_error = null;
    $password_error = null;

    // Login form variables
    $login_email_error = null;
    $login_password_error = null;

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
    
    // Validate first name
    if (!preg_match("/^[a-zA-Z-' ]+$/", $firstname)) {
        $firstname_error = 'First name must only contain letters, apostrophes, or dashes.';
        $hasError = true;
    }

    // Validate last name
    if (!preg_match("/^[a-zA-Z-' ]+$/", $lastname)) {
        $lastname_error = 'Last name must only contain letters, apostrophes, or dashes.';
        $hasError = true;
    }

    // Validate email
    $domain = substr(strrchr($email, "@"), 1);
    if (!checkdnsrr($domain, "MX")) {
        $email_error = 'Invalid email format.';
        $hasError = true;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = 'Invalid email format.';
        $hasError = true;
    }
    
    // Validate contact number
    if (!preg_match('/^09[0-9]{9}$/', $contactNumber)) {
        $contact_error = "Invalid Philippine phone number format.";
        $hasError = true;
    }

    // Validate password
    if (strlen($password) < 8 || strlen($password) > 12) {
        $password_error = 'Password must be between 8 and 12 characters.';
        $hasError = true;
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $password_error  = 'Password must contain at least 1 uppercase letter';
        $hasError = true;
    }

    if (!preg_match('/\d/', $password)) {
        $password_error  = 'Password must contain at least 1 number';
        $hasError = true;
    }

    if (!preg_match('/[\W_]/', $password)) {
        $password_error  = 'Password must contain at least 1 special character';
        $hasError = true;
    }

    if ($password !== $repeatPassword) {
        $password_error= 'Passwords do not match.';
        $hasError = true;
    } 

    // Check if email already exists in database
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
        // Create user in Supabase Auth via API
        $supabase_url = "https://ygbwanzobuielhttdzsw.supabase.co";
        $supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlnYndhbnpvYnVpZWxodHRkenN3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM1MTY3NTMsImV4cCI6MjA1OTA5Mjc1M30.bIaP_7rfHyne5PQ_Wmt8qdMYFDzurdnEAUR7U2bxbDQ"; // Replace with your actual anon key
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $supabase_url . "/auth/v1/signup");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        
        // Prepare the data for Supabase Auth
        $auth_data = [
            'email' => $email,
            'password' => $password,
            'data' => [
                'first_name' => $firstname,
                'last_name' => $lastname
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
        
        // Check if Supabase Auth creation was successful
        if ($http_code === 200 || $http_code === 201) {
            // Now insert into your database
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
                $_SESSION['registered_email'] = $email; // Store email for JS to use
                // Clear form data after successful registration
                $firstname = "";
                $lastname = "";
                $email = "";
                $contactNumber = "";
            } 
        } else {
            // Handle Supabase Auth error
            $response_data = json_decode($response, true);
            if (isset($response_data['message']) && strpos($response_data['message'], 'already registered') !== false) {
                $email_error = 'Email already registered in authentication system.';
            } else {
                $email_error = 'Error creating user in authentication system. Please try again.';
            }
            $hasError = true;
        }
    }
    
    if ($hasError) {
        $_SESSION['register_error'] = true; // Flag to show the register modal with errors
    }
    
    return !$hasError;
}

function handleLogin($conn) {
    global $login_email_error, $login_password_error;
    $hasError = false;

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // First, check if the user exists in Supabase Auth and is verified
    $supabase_url = "https://ygbwanzobuielhttdzsw.supabase.co";
    $supabase_key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InlnYndhbnpvYnVpZWxodHRkenN3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM1MTY3NTMsImV4cCI6MjA1OTA5Mjc1M30.bIaP_7rfHyne5PQ_Wmt8qdMYFDzurdnEAUR7U2bxbDQ"; // Replace with your actual anon key
    
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
    
    // For debugging
    error_log("Supabase Auth Login Response: " . $response);
    error_log("HTTP Code: " . $http_code);
    
    curl_close($ch);
    
    // If Supabase Auth login is successful, proceed with your database login
    if ($http_code === 200) {
        // Check if user is an admin
        $stmt = $conn->prepare("SELECT admin_id, admin_password FROM admin WHERE admin_email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // If not an admin, check customer login
        $stmt = $conn->prepare("SELECT c_id, c_password FROM customer WHERE c_email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        // If admin exists, check admin password
        if ($admin) {
            if ($password === $admin['admin_password']) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                header("Location: admin/admin_home.php");
                exit();
            } else {
                $login_password_error = 'Wrong password';
                $hasError = true;
            }
        } 
        // If customer exists, check customer password
        else if ($customer) {
            // Since we've already verified with Supabase, we can skip password verification
            // or keep it for double security
            if (password_verify($password, $customer['c_password'])) {
                $_SESSION['c_id'] = $customer['c_id'];
                $_SESSION['customer_id'] = $customer['c_id'];
                
                $_SESSION['login_time'] = date('Y-m-d H:i:s');
                $_SESSION['login_email'] = $email;
                
                header("Location: profile.php");
                exit();
            } else {
                $login_password_error = 'Password mismatch between systems. Please contact support.';
                $hasError = true;
            }
        } else {
            // User exists in Supabase but not in our database
            // This is an edge case - we should create the user in our database
            $login_email_error = 'User exists in authentication system but not in database. Please register again.';
            $hasError = true;
        }
    } else {
        // Handle Supabase Auth error
        $response_data = json_decode($response, true);
        
        if (isset($response_data['error_description'])) {
            if (strpos($response_data['error_description'], 'Email not confirmed') !== false) {
                $login_email_error = 'Please verify your email before logging in.';
            } else if (strpos($response_data['error_description'], 'Invalid login credentials') !== false) {
                $login_password_error = 'Invalid email or password';
            } else {
                $login_password_error = $response_data['error_description'];
            }
        } else if (isset($response_data['message'])) {
            $login_password_error = $response_data['message'];
        } else {
            // If we can't parse the error, show the raw response for debugging
            $login_password_error = 'Authentication error: ' . substr($response, 0, 100) . '...';
        }
        
        $hasError = true;
    }

    if ($hasError) {
        $_SESSION['login_error'] = true; // Flag to show the login modal with errors
    }
    
    return !$hasError;
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
    <link rel="stylesheet" href="log_in1.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        <?php if ($firstname_error): ?> .firstname-error { display: block; } <?php endif; ?>
        <?php if ($lastname_error): ?> .lastname-error { display: block; } <?php endif; ?>
        <?php if ($email_error): ?> .email-error { display: block; } <?php endif; ?>
        <?php if ($contact_error): ?> .contact-error { display: block; } <?php endif; ?>
        <?php if ($password_error): ?> .password-error { display: block; } <?php endif; ?>
        <?php if ($login_email_error): ?> .login-email-error { display: block; } <?php endif; ?>
        <?php if ($login_password_error): ?> .login-password-error { display: block; } <?php endif; ?>


        .error{
        color: #af4242;
        background-color: #fde8ec;
        padding: 10px;
        display: block; /* Always display errors when they exist */
        transform: translateY(-20px);
        margin-bottom: 10px;
        font-size: 14px;
        margin-top: 22px;
        }
        .password-input {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 38px;
            cursor: pointer;
            color: #6c757d;
        }
        
        .password-toggle:hover {
            color: #495057;
        }
        
        .password-requirements {
            font-size: 0.75rem;
            color: #6c757d;
            margin-top: 0.25rem;
        }
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
                        <div class="mb-3 d-flex flex-column justify-content-center">
                                <input type="email" class="form-control mx-auto w-50 text-center" name="email" id="loginEmail" required placeholder="Enter Email">
                                <?php if ($login_email_error): ?>
                                    <p class="error login-email-error mt-4 mb-0 mx-auto w-50"><?php echo $login_email_error; ?></p>
                                <?php endif; ?>
                                <div id="loginEmailError" class="error login-email-error mt-4 mb-0 mx-auto w-50" style="display: none;"></div>
                        </div>
                        <div class="mb-3 d-flex flex-column align-items-center">
                            <div class="position-relative w-50">
                                <input type="password" class="form-control text-center pr-5" id="loginPassword" name="password" required placeholder="Enter Password">
                                
                                <i class="fas fa-eye password-toggle position-absolute" id="loginPasswordToggle" style="right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                            </div>
                            
                            <?php if (!empty($login_password_error)): ?>
                                <p class="error login-password-error mt-4 w-50 text-center"><?php echo $login_password_error; ?></p>
                            <?php endif; ?>
                            <div id="loginPasswordError" class="error login-password-error mt-4 w-50 text-center" style="display: none;"></div>
                        </div>

                        <button type="submit" id="loginbut" class="btn btn-primary">Login</button>
                        <p class="mt-3 text-center"><a href="#" data-bs-toggle="modal" data-bs-target="#registerModal" id="not-yet-register">Not yet registered?</a></p>
                        <!-- Add resend verification link -->
                        <div class="mb-3 d-flex flex-column justify-content-center">
                            <p class="text-center mt-2">
                                <a href="#" id="resendVerificationLink">Didn't receive verification email?</a>
                            </p>
                        </div>
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
                                            <div id="registerEmailError" class="error email-error" style="display: none;"></div>
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
                                                <i class="fas fa-eye password-toggle" id="passwordToggle"></i>
                                                <div class="password-requirements">Password must be 8-12 characters, containing a 1 special character, 1 uppercase and 1 number.</div>
                                                <?php if ($password_error): ?>
                                                    <p class="error password-error"><?php echo $password_error; ?></p>
                                                <?php endif; ?>
                                                <div id="registerPasswordError" class="error password-error" style="display: none;"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-12">
                                            <div class="mb-3 password-input">
                                                <label for="repeatPassword">Repeat Password <span class="text-danger">*</span></label>
                                                <input type="password" class="form-control" id="repeatPassword" name="repeatPassword" placeholder="Repeat Password" required>
                                                <i class="fas fa-eye password-toggle" id="repeatPasswordToggle"></i>
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
                    <h2 class="modal-title">You are now registered with Adorafur!</h2>
                    <p>
                        We've sent a verification link to your email address. Please check your inbox and click the link to verify your account.
                        <strong>You must verify your email before you can log in.</strong>
                    </p>
                    <p>
                        If you don't see the email in your inbox, please check your spam folder. The verification link will expire in 24 hours.
                    </p>

                    <button type="button" class="btn btn-primary" id="returnToLogin">Return</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Resend Verification Modal -->
    <div class="modal fade" id="resendVerificationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Resend Verification Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Please enter your email address to receive a new verification link:</p>
                    <div class="mb-3">
                        <input type="email" class="form-control" id="resendEmail" placeholder="Enter your email">
                    </div>
                    <div id="resendMessage" class="alert d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="resendButton">Resend</button>
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


<!-- JavaScript to handle modals and password toggles -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Resend verification email
        const resendLink = document.getElementById('resendVerificationLink');
        const resendButton = document.getElementById('resendButton');
        const resendMessage = document.getElementById('resendMessage');
        
        if (resendLink) {
            resendLink.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Close login modal
                const loginModal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
                if (loginModal) {
                    loginModal.hide();
                }
                
                // Show resend verification modal
                setTimeout(() => {
                    const resendModal = new bootstrap.Modal(document.getElementById('resendVerificationModal'));
                    resendModal.show();
                }, 500);
            });
        }
        
        if (resendButton) {
            resendButton.addEventListener('click', function() {
                const email = document.getElementById('resendEmail').value.trim();
                
                if (!email) {
                    showResendMessage('Please enter your email address.', 'danger');
                    return;
                }
                
                // Disable button during request
                resendButton.disabled = true;
                resendButton.innerHTML = 'Sending...';
                
                // Send AJAX request to resend verification
                fetch('resend-verification.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'email=' + encodeURIComponent(email)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showResendMessage(data.message, 'success');
                        
                        // Clear input
                        document.getElementById('resendEmail').value = '';
                        
                        // Close modal after 3 seconds
                        setTimeout(() => {
                            const resendModal = bootstrap.Modal.getInstance(document.getElementById('resendVerificationModal'));
                            if (resendModal) {
                                resendModal.hide();
                            }
                        }, 3000);
                    } else {
                        showResendMessage(data.message, 'danger');
                    }
                })
                .catch(error => {
                    showResendMessage('An error occurred. Please try again.', 'danger');
                    console.error('Error:', error);
                })
                .finally(() => {
                    // Re-enable button
                    resendButton.disabled = false;
                    resendButton.innerHTML = 'Resend';
                });
            });
        }
        
        function showResendMessage(message, type) {
            resendMessage.textContent = message;
            resendMessage.className = `alert alert-${type}`;
            resendMessage.classList.remove('d-none');
        }
        
        // Password toggle functionality
        const passwordToggle = document.getElementById('passwordToggle');
        const repeatPasswordToggle = document.getElementById('repeatPasswordToggle');
        const loginPasswordToggle = document.getElementById('loginPasswordToggle');
        const passwordField = document.getElementById('password');
        const repeatPasswordField = document.getElementById('repeatPassword');
        const loginPasswordField = document.getElementById('loginPassword');
        
        // Function to toggle password visibility
        function togglePasswordVisibility(passwordField, toggleIcon) {
            if (passwordField && toggleIcon) {
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                } else {
                    passwordField.type = 'password';
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            }
        }
        
        // Toggle password fields
        if (passwordToggle && passwordField) {
            passwordToggle.addEventListener('click', function() {
                togglePasswordVisibility(passwordField, passwordToggle);
            });
        }
        
        if (repeatPasswordToggle && repeatPasswordField) {
            repeatPasswordToggle.addEventListener('click', function() {
                togglePasswordVisibility(repeatPasswordField, repeatPasswordToggle);
            });
        }
        
        if (loginPasswordToggle && loginPasswordField) {
            loginPasswordToggle.addEventListener('click', function() {
                togglePasswordVisibility(loginPasswordField, loginPasswordToggle);
            });
        }
        
        // Check if registration was successful
        <?php if (isset($_SESSION['registration_success'])): ?>
            var congratsModal = new bootstrap.Modal(document.getElementById('congratsModal'));
            congratsModal.show();
            <?php unset($_SESSION['registration_success']); // Clear session after showing modal ?>
        <?php endif; ?>

        // Show login modal if there's a login error
        <?php if (isset($_SESSION['login_error']) || isset($login_email_error) || isset($login_password_error)): ?>
            var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
            loginModal.show();
            <?php if (isset($_SESSION['login_error'])): ?>
                <?php unset($_SESSION['login_error']); ?>
            <?php endif; ?>
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
        const returnToLoginBtn = document.getElementById("returnToLogin");
        if (returnToLoginBtn) {
            returnToLoginBtn.addEventListener("click", function() {
                const congratsModalEl = document.getElementById('congratsModal');
                const congratsModal = bootstrap.Modal.getInstance(congratsModalEl);
                if (congratsModal) {
                    congratsModal.hide(); // Hide Congrats Modal
                }

                setTimeout(function() {
                    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
                    loginModal.show(); // Show Login Modal after a slight delay
                }, 500);
            });
        }
    });
</script>


</body>
</html>