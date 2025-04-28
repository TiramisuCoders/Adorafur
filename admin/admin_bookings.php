<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
  // Redirect to login page if not logged in
  header("Location: ../index.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin-css/admin_header.css">
    <link rel="stylesheet" href="admin-css/admin_bookings.css">
    <link rel="icon" type="image/png" href="admin-pics/adorafur-logo.png">
    <title>Admin Bookings</title>
</head> 

<body>
    <!-- NAVIGATION BAR -->
    <nav class="nav-bar">
    <img class="adorafur-logo" src="admin-pics/adorafur-logo.png" alt="Adorafur Logo" />
      <div class="nav-container">

          <div class="home-button">
            <a href="admin_home.php" class="home-text">Home</a>
          </div>

          <div class="book-button active">
            <a href="admin_bookings.php" class="booking-text">Appointments</a>
          </div>

          <div class="customer-button">
            <a href="admin_customers.php" class="customers-text">Customers</a>
          </div>

          <div class="profile-button">
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


    <!-- BOOKINGS PANEL -->
    <div class="panel-container">
      <div class="head">
        <div class="head-text">Appointments</div>
        <div class="time-text" id="real-time-clock">Loading...</div>
      </div>

      <!-- Add a container div to hold both the calendar and sidebar -->
      <div class="content-container">
        <!-- CALENDAR -->
        <div class="bookings-weekly">
            <div class="controls">
                <button onclick="changeWeek(-1)">◀</button>
                <div class="week-range" id="week-range"></div>
                <button onclick="changeWeek(1)">▶</button>
            </div>
            <div class="calendar" id="calendar"></div>
        </div>

        <!-- SIDE BAR (DYNAMIC REMINDERS) -->
        <div class="side-bar">
            <div class="reminders-section">
                <div class="sidebar-title">REMINDERS</div>
                <!-- Reminders will be inserted dynamically -->
            </div>

            <div class="tasks-section">
                <div class="sidebar-title">TASKS</div>
                <!-- Tasks will be inserted dynamically -->
            </div>
        </div>
      </div>
    </div>

    <!-- MODAL FORM (Initially Hidden) -->
    <div class="sidebar-modal" id="activityModal">
        <div class="sidebar-modal-content">
            <h3 class="sidebar-mtitle">Add New  <span id="activity-type-title"></span></h3>
            <label for="activity_description" class="modal-textlabel">Description:</label>
            <input type="text" id="activity_description" required>

            <label for="activity_date" class="modal-textlabel">Date:</label>
            <input type="date" id="activity_date" required>

            <label for="activity_time" class="modal-textlabel">Time:</label>
            <input type="time" id="activity_time" required>

            <input type="hidden" id="activity_type" value="">
<br>
            <button id="submitActivityBtn">Add</button>
            <button class="close-btn" onclick="closeSidebarModal()">Cancel</button>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="delete-modal" id="deleteModal">
        <div class="delete-modal-content">
            <h3 class="delete-modal-title">Confirm Delete</h3>
            <p class="delete-modal-message">Are you sure you want to delete this item?</p>
            <div class="delete-modal-buttons">
                <button id="confirmDeleteBtn" class="confirm-delete-btn">Yes, Delete</button>
                <button id="cancelDeleteBtn" class="cancel-delete-btn">Cancel</button>
            </div>
        </div>
    </div>

    <script src="admin.js"></script>
</body>
</html>