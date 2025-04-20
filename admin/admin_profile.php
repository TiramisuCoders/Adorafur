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

// Fetch admin data using PDO
try {
    $stmt = $conn->prepare("SELECT admin_name, admin_email, admin_position FROM admin WHERE admin_id = :admin_id");
    $stmt->bindParam(':admin_id', $_SESSION['admin_id'], PDO::PARAM_INT);
    $stmt->execute();
    
    // Check if admin exists
    if ($stmt->rowCount() > 0) {
        $admin = $stmt->fetch();
    } else {
        // Handle case where admin ID exists in session but not in database
        session_destroy();
        header("Location: admin_login.php?error=invalid_session");
        exit();
    }
} catch(PDOException $e) {
    error_log("Error fetching admin data: " . $e->getMessage());
    $error_message = "An error occurred while retrieving your profile information.";
}

// Include the create admin processing file
require_once 'create_admin_modal.php';
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin-css/admin_header.css">
    <link rel="stylesheet" href="admin-css/admin_profile1.css">
    <link rel="stylesheet" href="admin-css/admin_modal.css">
    <link rel="icon" type="image/png" href="admin-pics/adorafur-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Load Supabase JS library -->
    <script src="https://unpkg.com/@supabase/supabase-js@2"></script>
    <script src="admin.js"></script>

    <title>Admin Profile</title>
</head> 

<body>
    <!-- NAVIGATION BAR -->
    <nav class="nav-bar">
    <img class="adorafur-logo" src="admin-pics/adorafur-logo.png" alt="Adorafur Logo" />
      <div class="nav-container">

          <div class="home-button">
            <a href="admin_home.php" class="home-text">Home</a>
          </div>

          <div class="book-button">
            <a href="admin_bookings.php" class="booking-text">Bookings</a>
          </div>

          <div class="customer-button">
            <a href="admin_customers.php" class="customers-text">Customers</a>
          </div>

          <div class="profile-button active">
            <a href="admin_profile.php" class="profile-text">Profile</a>
          </div>

          <div class="logout-button">
            <a href="../logout.php" class="logout-text">Logout</i></a>
          </div>

      </div>

      <!-- HEADER -->
      <div class="header-img-container">
        </div>

    </nav>

    <!-- PROFILE -->
    <div class="panel-container">
      <div class="head">
        <div class="head-text">Profile</div>
        <div class="time-text" id="real-time-clock">Loading...</div>
      </div>
      

      <div class="profile-content">
        <?php if (isset($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php elseif (isset($_SESSION['success_message'])): ?>
            <div class="success-message"><?php echo htmlspecialchars($_SESSION['success_message']); ?></div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        
        <?php if (!isset($error_message)): ?>
            <div class="profile-field">
              <div class="field-label">Name:</div>
              <div class="field-value"><?php echo htmlspecialchars($admin['admin_name']); ?></div>
            </div>
            
            <div class="profile-field">
              <div class="field-label">Email Address:</div>
              <div class="field-value"><?php echo htmlspecialchars($admin['admin_email']); ?></div>
            </div>
            
            <div class="profile-field">
              <div class="field-label">Role:</div>
              <div class="field-value"><?php echo htmlspecialchars($admin['admin_position']); ?></div>
            </div>
            
            <!-- Add Create Admin Button -->
            <button class="create-admin-btn" onclick="openModal()">Create New Admin</button>
        <?php endif; ?>
      </div>
    </div>
    
    <!-- Create Admin Modal -->
    <div id="createAdminModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Create New Admin Account</h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            
            <form id="createAdminForm">
                <div class="form-group">
                    <label for="admin_name">Name</label>
                    <input type="text" id="admin_name" name="admin_name" placeholder="Enter full name" value="<?php echo htmlspecialchars($admin_name ?? ''); ?>">
                    <div class="error-message" id="name_error"><?php echo $name_error ?? ''; ?></div>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter email address" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                    <div class="error-message" id="email_error"><?php echo $email_error ?? ''; ?></div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" placeholder="Enter password">
                        <button type="button" id="passwordToggle" class="password-toggle">
                            <i id="passwordEyeIcon" class="fa fa-eye"></i>
                        </button>
                    </div>
                    <div class="error-message" id="password_error"><?php echo $password_error ?? ''; ?></div>
                    <div class="password-requirements">Password must be 8-12 characters, containing a 1 special character, 1 uppercase and 1 number.</div>
                </div>
                
                <div class="form-group">
                    <label for="repeatPassword">Confirm Password</label>
                    <div class="password-field">
                        <input type="password" id="repeatPassword" name="repeatPassword" placeholder="Confirm password">
                        <button type="button" id="repeatPasswordToggle" class="password-toggle">
                            <i id="repeatPasswordEyeIcon" class="fa fa-eye"></i>
                        </button>
                    </div>
                    <div class="error-message" id="repeat_password_error"></div>
                </div>
                
                <div class="form-group">
                    <label for="admin_position">Position</label>
                    <input type="text" id="admin_position" name="admin_position" placeholder="Enter position (e.g., Manager, Supervisor)" value="<?php echo htmlspecialchars($position ?? ''); ?>">
                    <div class="error-message" id="position_error"><?php echo $position_error ?? ''; ?></div>
                </div>
                
                <div class="btn-container">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="button" id="createAdminBtn" class="btn btn-primary">Create Admin</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="admin_modal.js"></script>
    <script>
        // Update the clock and date
        function updateDateTime() {
            const now = new Date();
            
            // Update time
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            document.getElementById('real-time-clock').textContent = `${hours}:${minutes}:${seconds}`;
            
            // Update date
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            const dateElement = document.querySelector('.date-and-day');
            if (dateElement) {
                dateElement.textContent = now.toLocaleDateString('en-US', options);
            }
        }
        
        // Initial update
        updateDateTime();
        
        // Update every second
        setInterval(updateDateTime, 1000);
    </script>
</body>
</html>
