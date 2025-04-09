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
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin-css/admin_header01.css">
    <link rel="stylesheet" href="admin-css/admin_profile1.css">
    <link rel="icon" type="image/png" href="admin-pics/adorafur-logo.png">
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
        <?php else: ?>
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
        <?php endif; ?>
        
        
      </div>
    </div>
    
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
            document.querySelector('.date-and-day').textContent = now.toLocaleDateString('en-US', options);
        }
        
        // Initial update
        updateDateTime();
        
        // Update every second
        setInterval(updateDateTime, 1000);
    </script>
</body>
</html>