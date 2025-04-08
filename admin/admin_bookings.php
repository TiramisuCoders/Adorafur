<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Fix CSS path and add version parameter to prevent caching -->
    <link rel="stylesheet" href="admin-css/admin_header01.css?v=1.0.1">
    <link rel="stylesheet" href="admin-css/admin_bookings.css?v=1.0.1">
    <link rel="icon" type="image/png" href="admin-pics/adorafur-logo.png">
    <title>Admin Bookings</title>
    <!-- Add debug script to check for errors -->
    <script>
        window.onerror = function(message, source, lineno, colno, error) {
            console.error("JavaScript Error:", message, "at", source, "line:", lineno);
            alert("JavaScript Error: " + message + " at line " + lineno);
            return true;
        };
    </script>
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
            <a href="admin_bookings.php" class="booking-text">Bookings</a>
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
        <div class="head-text"> Bookings</div>
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

    <!-- Make sure the script path is correct and add a version parameter to prevent caching -->
    <script src="admin.js?v=1.0.1"></script>
</body>
</html>
