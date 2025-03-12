<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin-css/admin_header2.css">
    <link rel="stylesheet" href="admin-css/admin_bookings.css">
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
                <a href="admin_bookings1.php" class="booking-text">Bookings</a>
            </div>

            <div class="by-service-frame">
                <div class="by-service">By Service</div>
            </div>

            <div class="pet-hotel-cont">
                <input type="checkbox" id="hotel-check" class="pet-hotel-checkbox">
                <label for="hotel-check" class="pet-hotel-text">Pet Hotel</label>
            </div>

            <div class="pet-daycare-cont">
                <input type="checkbox" id="daycare-check" class="daycare-checkbox">
                <label for="daycare-check" class="pet-daycare-text">Pet Daycare</label>
            </div>

            <div class="customer-button">
                <a href="admin_customers.php" class="customers-text">Customers</a>
            </div>

            <div class="profile-button">
                <a href="admin_profile.php" class="profile-text">Profile</a>
            </div>
        </div>

        <!-- HEADER -->
        <div class="header-img-container">
            <button id="notificationButton">
                <img class="notifications" src="admin-pics/notification-bell.png" alt="Notifications" />
            </button>
        </div>
    </nav>

    <!-- BOOKINGS PANEL -->
    <div class="panel-container">
        <div class="head">
            <div class="head-text">Bookings</div>
            <div class="time-text" id="real-time-clock">Loading...</div>
        </div>
        
        <div class="content">
            <!-- CALENDAR -->
            <div class="bookings-weekly">
                <div class="controls">
                    <button onclick="changeWeek(-1)">◀</button>
                    <span id="week-range"></span>
                    <button onclick="changeWeek(1)">▶</button>
                </div>
                <div class="calendar" id="calendar"></div>
            </div>

            <!-- SIDE BAR (DYNAMIC REMINDERS) -->
            <div class="side-bar">
                <div class="reminders-section">
                    <div class="sidebar-title">REMINDERS</div>

                    <div class="sidebar-textbox reminder-item">
                        <div class="sidebar-subtitle">Vet Visit</div>
                        <div class="sidebar-desc">October 5, 2024</div>
                        <div class="sidebar-line"></div>
                    </div>

                    <div class="sidebar-textbox reminder-item">
                        <div class="sidebar-subtitle">Water Interruption</div>
                        <div class="sidebar-desc">October 9, 2024</div>
                        <div class="sidebar-line"></div>
                    </div>

                    <div class="sidebar-textbox reminder-item">
                        <div class="sidebar-subtitle">Electric Bills Deadline</div>
                        <div class="sidebar-desc">October 15, 2024</div>
                        <div class="sidebar-line"></div>
                    </div>

                    <div class="sidebar-textbox reminder-item">
                        <div class="sidebar-subtitle">Dog Food Restock</div>
                        <div class="sidebar-desc">October 15, 2024</div>
                        <div class="sidebar-line"></div>
                    </div>

                    <div class="sidebar-textbox reminder-item">
                        <div class="sidebar-subtitle">Halfday Operations</div>
                        <div class="sidebar-desc">October 15, 2024</div>
                        <div class="sidebar-line"></div>
                    </div>

                    <div class="sidebar-textbox">
                        <div class="add-sidebar">Add Reminder</div>
                        <div class="sidebar-line"></div>
                    </div>

                    <div class="view-rem" id="viewRemindersBtn">View Reminders</div>
                </div>

                <div class="tasks-section">
                    <div class="sidebar-title">TASKS</div>

                    <div class="sidebar-textbox">
                        <div class="sidebar-subtitle">Follow Up Payment</div>
                        <div class="sidebar-bdesc">Transaction ID: SHSD78F6</div>
                        <div class="sidebar-desc">Han Bascao, GCash</div>
                        <div class="sidebar-line"></div>
                    </div>

                    <div class="sidebar-textbox">
                        <div class="sidebar-subtitle">Send Confirmation Msg</div>
                        <div class="sidebar-bdesc">Phone Number: 0993 452 1387</div>
                        <div class="sidebar-desc">Joanne Margareth</div>
                        <div class="sidebar-line"></div>
                    </div>

                    <div class="sidebar-textbox">
                        <div class="sidebar-subtitle">Follow Up Payment</div>
                        <div class="sidebar-bdesc">Transaction ID: H3JK4H5J</div>
                        <div class="sidebar-desc">Jude Flores, Paymaya</div>
                        <div class="sidebar-line"></div>
                    </div>

                    <div class="sidebar-textbox">
                        <div class="sidebar-subtitle">Send Confirmation Msg</div>
                        <div class="sidebar-bdesc">Phone Number: 0932 763 1111</div>
                        <div class="sidebar-desc">Vince Delos Santos</div>
                        <div class="sidebar-line"></div>
                    </div>

                    <div class="sidebar-textbox">
                        <div class="add-sidebar">Add Task</div>
                        <div class="sidebar-line"></div>
                    </div>
                    <div class="view-task" id="viewTasksBtn">View Tasks</div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL FORM (Initially Hidden) -->
    <div class="sidebar-modal" id="activityModal">
        <div class="sidebar-modal-content">
            <h3 class="sidebar-mtitle">Add New <span id="activity-type-title"></span></h3>
            <label for="activity_description" class="modal-textlabel">Description:</label>
            <input type="text" id="activity_description" required>

            <label for="activity_date" class="modal-textlabel">Date:</label>
            <input type="date" id="activity_date" required>

            <label for="activity_time" class="modal-textlabel">Time:</label>
            <input type="time" id="activity_time" required>

            <input type="hidden" id="activity_type" value="">
            <button id="submitActivityBtn">Add</button>
            <button class="close-btn" onclick="closeSidebarModal()">Cancel</button>
        </div>
    </div>

    <!-- Notification Modal -->
    <div id="notificationModal" class="modal-home">
        <div class="modal-content-home">
            <div class="modal-header">
                <img src="admin-pics/adorafur-logo.png" alt="Adorafur Logo" class="modal-logo">
                <div class="notifications-header">
                    Notifications
                </div>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="notif-today">TODAY</div>
                <div class="notification-card">
                    <div class="notif-service">Pet Hotel</div>
                    <div class="notif-sec">
                        <div class="notif-sub">Transaction No:</div>
                        <div class="notif-info">7S89F7A</div>
                    </div>

                    <div class="notif-sec">
                        <div class="notif-sub">Customer: </div>
                        <div class="notif-info">Han Bascao</div>
                    </div>
                    <div class="notification-footer">
                        <div class="date-sec">
                            <div class="notif-sub">Date: </div>
                            <div class="notif-info">Today</div>
                        </div>
                        <div class="notif-confirmed">CONFIRMED</div>
                    </div>
                </div>

                <div class="notification-card">
                    <div class="notif-circle"></div>
                    <div class="notif-service">Daycare</div>

                    <div class="notif-sec">
                        <div class="notif-sub">Transaction No:</div>
                        <div class="notif-info">ASF9S8F9</div>
                    </div>

                    <div class="notif-sec">
                        <div class="notif-sub">Customer: </div>
                        <div class="notif-info">Jude Flores</div>
                    </div>
                    <div class="notification-footer">
                        <div class="date-sec">
                            <div class="notif-sub">Date: </div>
                            <div class="notif-info">October 5, 2025</div>
                        </div>
                        <button id="confirm-btn" class="confirm-btn">Confirm</button>
                    </div>
                </div>
                <div class="notif-date">Oct 2</div>
                <div class="notification-card">
                    <div class="notif-text">Fully Booked on October 6, 2024</div>
                </div>
            </div>
        </div>
    </div>

    <script src="admin.js"></script>
    <script>
        // Notification Modal Script
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById("notificationModal");
            const btn = document.getElementById("notificationButton");
            const span = document.getElementsByClassName("close")[0];

            btn.onclick = function() {
                modal.style.display = "block";
            }

            span.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        });
    </script>
    
</body>
</html>