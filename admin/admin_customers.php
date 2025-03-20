<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="admin-css/admin_header1.css">
    <link rel="stylesheet" href="admin-css/admin_customer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
      
    <script src="admin.js"></script>
    <title>Admin Customers</title>
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

          <div class="customer-button active">
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
            <button id="notificationButton">
                <img class="notifications" src="admin-pics/notification-bell.png" alt="Notifications" />
            </button>
        </div>

    </nav>

    <!-- CUSTOMERS -->

    <div class="panel-container">
      <div class="head">
        <div class="head-text"> Customers</div>
        <div class="time-text" id="real-time-clock">Loading...</div>
      </div>

      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search">
        <span class="icon"><i class="fa-solid fa-magnifying-glass"></i></span>
      </div>
      
      <table class="customer-list">
        <thead class="customer-pet">
          <th class="name">Customer Name</th>
          <th class="pets">Pet/s Name</th>
          <th class="mem-status">Membership Status</th>
          <th class="date"></th>
        </thead>
        
        <tbody class="deets"  id="customerTableBody">
          
    <?php include("../connect.php");

    try {
      
      $sql = "SELECT 
                c.c_id, 
                CONCAT(c.c_first_name, ' ', c.c_last_name) AS owner_name,
                GROUP_CONCAT(p.pet_name SEPARATOR ', ') AS pet_names,
                c.c_membership_status as membership_status,
                p.*
                FROM customer c
                LEFT JOIN 
                  pet p ON c.c_id = p.customer_id
                GROUP BY 
                  c.c_id, c.c_first_name";
              
              // Prepare and execute the statement
      $stmt = $conn->prepare($sql);
      $stmt->execute();

      if ($stmt->rowCount() > 0) {

        while($row =$stmt->fetch(PDO::FETCH_ASSOC)){
          echo "<tr class='row1'>";
          echo "<td class='customer-name'> <a href='admin_customers_profile.php?id=" . htmlspecialchars($row["c_id"]) . "'>" . htmlspecialchars($row["owner_name"]) . "</a></td>";
          echo "<td class='pets-name'>". htmlspecialchars($row["pet_names"] ?? 'No pets') ."</td>";
          echo "<td class='mem-status'>". htmlspecialchars($row["membership_status"] ?? 'None') . "</td>";
          echo "<td class='dates'>";
          echo "<strong>Registered Date:</strong>". ($row["pet_vaccination_date_administered"] ? date('m/d/Y', strtotime($row["pet_vaccination_date_administered"])) : 'N/A') ."<br>";
          echo "<strong>Expiry Date:</strong>" . ($row["pet_vaccination_date_expiry"] ? date('m/d/Y', strtotime($row["pet_vaccination_date_expiry"])) : 'N/A');
          echo "</td>";
          echo "</tr>";
        }
      } else {
        echo "<tr><td colspan='4'>No customers found</td></tr>";
      }
    } catch (PDOException $e) {
      echo "<tr><td colspan='4'>Database error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
  }
    ?> 
            
        </tbody>
      </table>  

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

    <script> 
      document.getElementById('searchInput').addEventListener('keyup', function() {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#customerTableBody tr');
        
        rows.forEach(row => {
            const customerName = row.querySelector('.customer-name').textContent.toLowerCase();
            const petName = row.querySelector('.pets-name').textContent.toLowerCase();
            
            if (customerName.includes(searchValue) || petName.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

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