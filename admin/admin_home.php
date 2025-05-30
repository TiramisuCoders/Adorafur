<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../index.php");
    exit();
}
include("../connect.php");

$sql = "SELECT
            b.booking_id AS b_id,
            p.pet_name AS p_pet,
            p.pet_breed AS p_breed,
            p.pet_size AS p_size,
            s.service_name AS s_service,
            CONCAT(c.c_first_name, ' ', c.c_last_name) AS owner_name,
            c.c_id AS owner_id,
            c.c_contact_number AS owner_num,
            pay.pay_status AS pay_status,
            b.booking_balance AS b_balance,
            pay.pay_method AS pay_mop,
            pay.pay_reference_number AS pay_reference_number,
            pay.proof_of_payment AS pay_proof_of_payment,
            DATE(b.booking_check_in) AS b_in,
            DATE(b.booking_check_out) AS b_out
        FROM bookings b
        JOIN pet p ON b.pet_id = p.pet_id
        JOIN customer c ON p.customer_id = c.c_id
        JOIN service s ON b.service_id = s.service_id
        JOIN (
            SELECT DISTINCT ON (booking_id)
                *
            FROM payment
            ORDER BY booking_id, pay_date DESC
        ) pay ON b.booking_id = pay.booking_id
        WHERE b.booking_status IN ('Pending', 'Confirmed')
        ORDER BY
            CASE
                WHEN b.booking_check_in >= CURRENT_DATE THEN 1
                ELSE 2
            END,
            b.booking_check_in ASC;";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->query("SELECT  admin_id, admin_name FROM admin");

    $staffNames = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="admin-pics/adorafur-logo.png">
    <link rel="stylesheet" href="admin-css/admin_header.css">
    <link rel="stylesheet" href="admin-css/admin_home.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin.js"></script>
    <title>Admin Homepage</title>

    <style>
        .search-box {
            position: relative;
            width: 300px;
        }

        .search-box input {
            width: 100%;
            padding: 8px 15px;
            padding-right: 40px;
            border-radius: 20px;
            border: 1px solid #ccc;
            outline: none;
            transition: all 0.3s;
        }

        .search-box input:focus {
            border-color: #5a3e36;
            box-shadow: 0 0 5px rgba(90, 62, 54, 0.3);
        }

        .search-box .icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #5a3e36;
        }

        .head {
            padding: 20px 0 0 0;
        }

        .head-text {
            margin: 0 0 15px 0;
            font-size: 2rem;
            color: #421D11;
            font-family: 'BalooTammudu2-Bold', sans-serif;
        }

        .search-box-container {
            margin-bottom: 20px;
        }

        /* Hide rows that don't match search */
        tr.hidden-row {
            display: none;
        }
        
        /* Modal styling from admin_home.php */
        .modal-header {
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
        }
        
        
        
        
        
        .header-id {
            font-size: 1.5rem;
            font-weight: bold;
            color: black !important;
        }
        
        .header-controls {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .staff-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .staff-label {
            margin-bottom: 0;
            color: inherit;
            font-weight: 500;
        }
        
        .staff-select {
            padding: 5px 10px;
            border-radius: 4px;
            border: 1px solid #ced4da;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
        }
        
        .button {
            padding: 6px 15px;
            border-radius: 4px;
            border: none;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .button:hover {
            background-color: #45a049;
        }
        
        #cancelButton {
            background-color: #f44336;
            color: white;
        }
        
        #cancelButton:hover {
            background-color: #d32f2f;
        }
        
        .form-label {
            font-weight: 500;
            color: #421D11;
        }
        
        #view-photo {
            background-color: #5a3e36;
            color: white;
            padding: 6px 15px;
            border-radius: 4px;
            text-decoration: none;
        }
        
        #view-photo:hover {
            background-color: #4a3026;
        }
    </style>

    <style>
        /* Additional styles for scrollable panel */
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        .panel-container {
            max-height: calc(100vh - 80px);
            overflow-y: scroll;
            /* Changed from auto to scroll */
            scrollbar-width: none;
            /* Firefox */
            -ms-overflow-style: none;
            /* IE and Edge */
            padding: 0 15px 20px;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            box-sizing: border-box;
        }

        /* Hide scrollbar for Chrome, Safari and Opera */
        .panel-container::-webkit-scrollbar {
            display: none;
        }

        /* Media queries for responsiveness */
        @media (max-width: 1200px) {
            .panel-container {
                width: 95%;
            }
        }

        @media (max-width: 768px) {
            .panel-container {
                width: 100%;
                padding: 0 10px 20px;
            }
        }
    </style>

    <!-- Font Awesome for search icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


</head>

<body style="background-color: #eee; height: 100vh;">
    <!-- NAVIGATION BAR -->
    <nav class="nav-bar">
        <a href="../home.php"><img class="adorafur-logo" src="admin-pics/adorafur-logo.png" alt="Adorafur Logo" /></a>
        <div class="nav-container">
            <div class="home-button active">
                <a href="admin_home.php" class="home-text">Home</a>
            </div>
            <div class="book-button">
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

    <!-- HOME PAGE -->
    <div class="panel-container">
        <div class="head">
            <h6 class="head-text">Dashboard</h6>
            <div class="search-box-container">
                <div class="search-box">
                    <input type="text" id="searchInput" placeholder="Search">
                    <span class="icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                </div>
            </div>
        </div>
       
    


        <div class="dashboard-overview" style="margin: 20px;">

            <!-- Stats Cards -->
            <div class="stats-container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 25px;">
                <!-- Total Bookings -->
                <?php
                // Get total bookings count
                $total_bookings_query = "SELECT COUNT(*) as total FROM bookings WHERE booking_status IN ('Pending', 'Confirmed')";
                $total_stmt = $conn->prepare($total_bookings_query);
                $total_stmt->execute();
                $total_bookings = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];

                // Get pending bookings count
                $pending_bookings_query = "SELECT COUNT(*) as pending FROM bookings WHERE booking_status = 'Pending'";
                $pending_stmt = $conn->prepare($pending_bookings_query);
                $pending_stmt->execute();
                $pending_bookings = $pending_stmt->fetch(PDO::FETCH_ASSOC)['pending'];

                // Get confirmed bookings count
                $confirmed_bookings_query = "SELECT COUNT(*) as confirmed FROM bookings WHERE booking_status = 'Confirmed'";
                $confirmed_stmt = $conn->prepare($confirmed_bookings_query);
                $confirmed_stmt->execute();
                $confirmed_bookings = $confirmed_stmt->fetch(PDO::FETCH_ASSOC)['confirmed'];
                ?>

                <div class="stat-card" style="background-color: white; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.15); border-left: 4px solid #1a4b8c;">
                    <div style="display: flex; align-items: center;">
                        <div style="background-color: rgba(26, 75, 140, 0.1); border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                            <i class="fa-solid fa-calendar" style="color: #1a4b8c; font-size: 24px;"></i>
                        </div>
                        <div>
                            <p style="color: #666; font-size: 14px; margin: 0;">Total Bookings</p>
                            <p style="font-size: 24px; font-weight: bold; color: #421D11; margin: 0;"><?php echo $total_bookings; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Pending Bookings -->
                <div class="stat-card" style="background-color: white; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.15); border-left: 4px solid #ffd700;">
                    <div style="display: flex; align-items: center;">
                        <div style="background-color: rgba(255, 215, 0, 0.1); border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                            <i class="fa-solid fa-clock" style="color: #ffd7কিন্তon0; font-size: 24px;"></i>
                        </div>
                        <div>
                            <p style="color: #666; font-size: 14px; margin: 0;">Pending Bookings</p>
                            <p style="font-size: 24px; font-weight: bold; color: #421D11; margin: 0;"><?php echo $pending_bookings; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Confirmed Bookings -->
                <div class="stat-card" style="background-color: white; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.15); border-left: 4px solid #4CAF50;">
                    <div style="display: flex; align-items: center;">
                        <div style="background-color: rgba(76, 175, 80, 0.1); border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                            <i class="fa-solid fa-check-circle" style="color: #4CAF50; font-size: 24px;"></i>
                        </div>
                        <div>
                            <p style="color: #666; font-size: 14px; margin: 0;">Confirmed Bookings</p>
                            <p style="font-size: 24px; font-weight: bold; color: #421D11; margin: 0;"><?php echo $confirmed_bookings; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Breakdown and Today's Activity -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 25px;">
                <!-- Bookings by Service -->
                <div style="background-color: white; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.15);">
                    <h3 style="color: #421D11; font-family: 'BalooTammudu2-SemiBold', sans-serif; font-size: 18px; margin-bottom: 15px;">Bookings by Service</h3>

                    <?php
                    // Get pet hotel bookings count
                    $hotel_query = "SELECT COUNT(*) as hotel FROM bookings b 
                           JOIN service s ON b.service_id = s.service_id 
                           WHERE s.service_name = 'Pet Hotel' AND b.booking_status IN ('Pending', 'Confirmed')";
                    $hotel_stmt = $conn->prepare($hotel_query);
                    $hotel_stmt->execute();
                    $hotel_bookings = $hotel_stmt->fetch(PDO::FETCH_ASSOC)['hotel'];

                    // Get pet daycare bookings count
                    $daycare_query = "SELECT COUNT(*) as daycare FROM bookings b 
                             JOIN service s ON b.service_id = s.service_id 
                             WHERE s.service_name = 'Pet Daycare' AND b.booking_status IN ('Pending', 'Confirmed')";
                    $daycare_stmt = $conn->prepare($daycare_query);
                    $daycare_stmt->execute();
                    $daycare_bookings = $daycare_stmt->fetch(PDO::FETCH_ASSOC)['daycare'];
                    ?>

                    <div style="display: flex; justify-content: space-around; align-items: center;">
                        <div style="text-align: center;">
                            <div style="background-color: rgba(26, 75, 140, 0.1); border-radius: 50%; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">
                                <i class="fa-solid fa-hotel" style="color: #1a4b8c; font-size: 28px;"></i>
                            </div>
                            <h4 style="font-size: 20px; font-weight: bold; color: #421D11; margin: 0;"><?php echo $hotel_bookings; ?></h4>
                            <p style="color: #666; font-size: 14px; margin: 5px 0 0;">Pet Hotel</p>
                        </div>

                        <div style="text-align: center;">
                            <div style="background-color: rgba(255, 215, 0, 0.1); border-radius: 50%; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px;">
                                <i class="fa-solid fa-paw" style="color: #ffd700; font-size: 28px;"></i>
                            </div>
                            <h4 style="font-size: 20px; font-weight: bold; color: #421D11; margin: 0;"><?php echo $daycare_bookings; ?></h4>
                            <p style="color: #666; font-size: 14px; margin: 5px 0 0;">Pet Daycare</p>
                        </div>
                    </div>
                </div>

                <!-- Today's Activity -->
                <div style="background-color: white; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.15);">
                    <h3 style="color: #421D11; font-family: 'BalooTammudu2-SemiBold', sans-serif; font-size: 18px; margin-bottom: 15px;">Today's Activity</h3>

                    <?php
                    // Get today's date
                    $today = date('Y-m-d');

                    // Get check-ins for today
                    $checkin_query = "SELECT COUNT(*) as checkins FROM bookings 
                             WHERE DATE(booking_check_in) = :today AND booking_status IN ('Pending', 'Confirmed')";
                    $checkin_stmt = $conn->prepare($checkin_query);
                    $checkin_stmt->bindParam(':today', $today);
                    $checkin_stmt->execute();
                    $checkins = $checkin_stmt->fetch(PDO::FETCH_ASSOC)['checkins'];

                    // Get check-outs for today
                    $checkout_query = "SELECT COUNT(*) as checkouts FROM bookings 
                              WHERE DATE(booking_check_out) = :today AND booking_status IN ('Pending', 'Confirmed')";
                    $checkout_stmt = $conn->prepare($checkout_query);
                    $checkout_stmt->bindParam(':today', $today);
                    $checkout_stmt->execute();
                    $checkouts = $checkout_stmt->fetch(PDO::FETCH_ASSOC)['checkouts'];
                    ?>

                    <div style="display: flex; justify-content: space-between; gap: 15px;">
                        <div style="background-color: rgba(26, 75, 140, 0.1); border-radius: 8px; padding: 15px; flex: 1;">
                            <div style="display: flex; align-items: center;">
                                <div style="background-color: rgba(26, 75, 140, 0.2); border-radius: 8px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                                    <i class="fa-solid fa-sign-in-alt" style="color: #1a4b8c; font-size: 20px;"></i>
                                </div>
                                <div>
                                    <p style="color: #666; font-size: 14px; margin: 0;">Check-ins Today</p>
                                    <p style="font-size: 20px; font-weight: bold; color: #421D11; margin: 0;"><?php echo $checkins; ?></p>
                                </div>
                            </div>
                        </div>

                        <div style="background-color: rgba(76, 175, 80, 0.1); border-radius: 8px; padding: 15px; flex: 1;">
                            <div style="display: flex; align-items: center;">
                                <div style="background-color: rgba(76, 175, 80, 0.2); border-radius: 8px; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                                    <i class="fa-solid fa-sign-out-alt" style="color: #4CAF50; font-size: 20px;"></i>
                                </div>
                                <div>
                                    <p style="color: #666; font-size: 14px; margin: 0;">Check-outs Today</p>
                                    <p style="font-size: 20px; font-weight: bold; color: #421D11; margin: 0;"><?php echo $checkouts; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="reservations-container">
            <table class="reservations">
                <?php
                if ($stmt->rowCount() > 0) {
                    echo '
                <thead class="attributes">
                    <th class="id">ID</th>
                    <th class="pet">Pet</th>
                    <th class="service">Service</th>
                    <th class="name">Name</th>
                    <th class="payment">Payment</th>
                    <th class="date">Date</th>
                </thead>
                <tbody class="deets">
                ';

                    foreach ($reservations as $fetch_reservations) {
                ?>
                        <tr class="row1">

                            <td class="deets-id <?php echo strtolower($fetch_reservations['s_service']) === 'pet hotel' ? 'row-hotel' : 'row-daycare'; ?>">
                                <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#bookingModal"
                                    data-booking-id="<?php echo htmlspecialchars($fetch_reservations['b_id']); ?>"
                                    data-owner-name="<?php echo htmlspecialchars($fetch_reservations['owner_name']); ?>"
                                    data-owner-id="<?php echo htmlspecialchars($fetch_reservations['owner_id']); ?>"
                                    data-owner-num="<?php echo htmlspecialchars($fetch_reservations['owner_num']); ?>"
                                    data-pet-name="<?php echo htmlspecialchars($fetch_reservations['p_pet']); ?>"
                                    data-pet-breed="<?php echo htmlspecialchars($fetch_reservations['p_breed']); ?>"
                                    data-pet-size="<?php echo htmlspecialchars($fetch_reservations['p_size']); ?>"
                                    data-service="<?php echo htmlspecialchars($fetch_reservations['s_service']); ?>"
                                    data-check-in="<?php echo htmlspecialchars($fetch_reservations['b_in']); ?>"
                                    data-check-out="<?php echo htmlspecialchars($fetch_reservations['b_out']); ?>"
                                    data-payment-status="<?php echo htmlspecialchars($fetch_reservations['pay_status']); ?>"
                                    data-mop="<?php echo htmlspecialchars($fetch_reservations['pay_mop']); ?>"
                                    data-reference-number="<?php echo htmlspecialchars($fetch_reservations['pay_reference_number']); ?>"
                                    data-book-balance="<?php echo htmlspecialchars($fetch_reservations['b_balance']) ?>"
                                    data-proof-of-payment="<?php echo htmlspecialchars($fetch_reservations['pay_proof_of_payment']); ?>">
                                    <strong> <?php echo htmlspecialchars($fetch_reservations['b_id']); ?> </strong> 
                                </button>
                            </td>

                            <td class="deets-pet">
                                <span class="pet-name">
                                    <?php echo htmlspecialchars($fetch_reservations['p_pet']); ?>
                                </span><br>
                                <span class="pet-breed">
                                    <?php echo htmlspecialchars($fetch_reservations['p_breed'] . ", " . $fetch_reservations['p_size']); ?>
                                </span>
                            </td>
                            <td class="deets-service"><?php echo htmlspecialchars($fetch_reservations['s_service']); ?></td>
                            <td class="deets-name">
                                <span class="owner"><?php echo htmlspecialchars($fetch_reservations['owner_name']); ?></span><br>
                                <span class='owner-num'><?php echo htmlspecialchars($fetch_reservations['owner_num']); ?></span>
                            </td>
                            <td class="deets-payment">
                                <span class="payment-dot <?php echo strtolower($fetch_reservations['pay_status']) === 'down payment' ? 'payment-down' : 'payment-full'; ?>">
                                </span>
                                <?php echo htmlspecialchars($fetch_reservations['pay_status']); ?>
                            </td>
                            <td class="deets-date">
                                <span class="name-12-span">Check-in:</span>
                                <span class="name-12-span2"><?php echo htmlspecialchars($fetch_reservations['b_in']); ?></span>
                                <br>
                                <span class="number-12-span">Check-out:</span>
                                <span class="number-12-span2"><?php echo htmlspecialchars($fetch_reservations['b_out']); ?></span>
                            </td>
                        </tr>
                <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap Modal - Using the design from admin_home.php -->
    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-center">
            <div class="modal-content" id="book-modal">
                <div class="modal-header" id="modalHeader">
                    <div class="header-id" id="modalBookingId"></div>
                    <div class="header-controls">
                        <div class="staff-section">
                            <label class="staff-label">Staff:</label>
                            <select class="staff-select" id="staffSelect">
                                <?php
                                if ($staffNames) {
                                    foreach ($staffNames as $staff) {
                                        echo "<option value='" . htmlspecialchars($staff['admin_id']) . "'>" . htmlspecialchars($staff['admin_name']) . "</option>";
                                    }
                                } else {
                                    echo "<option>No staff available</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="button-group">
                            <button class="button" id="saveButton">Save</button>
                            <button type="button" class="btn" id="cancelButton" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <form id="updateBookingForm">
                        <input type="hidden" name="booking_id" id="bookingId">

                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Owner Name:</label>
                                    <input type="text" class="form-control" name="ownerName" id="ownerName" readonly>
                                    <input type="hidden" name="ownerId" id="ownerId">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contact:</label>
                                    <input type="text" class="form-control" name="contact" id="contact" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pet Name:</label>
                                    <input type="text" class="form-control" name="petName" id="petName" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pet Type:</label>
                                    <input type="text" class="form-control" name="petType" id="petType" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pet Breed:</label>
                                    <input type="text" class="form-control" name="petBreed" id="petBreed" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Service:</label>
                                    <input type="text" class="form-control" name="service" id="service" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Check-in:</label>
                                    <input type="date" class="form-control" name="checkIn" id="checkIn">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Check-out:</label>
                                    <input type="date" class="form-control" name="checkOut" id="checkOut">
                                </div>
                            </div>


                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Balance:</label>
                                    <input type="text" class="form-control" name="bookBalance" id="bookBalance" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Mode of Payment:</label>
                                    <input type="text" class="form-control" name="paymentMode" id="paymentMode" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reference No:</label>
                                    <input type="text" class="form-control" name="referenceNo" id="referenceNo" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Proof of Payment</label><br>
                                    <div id="proofOfPaymentContainer">
                                        <!-- Will be populated by JavaScript -->
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="form-group">
                                        <label class="form-label text-brown mb-2">Booking Status:</label>
                                        <select class="form-control" name="bookingStatusUpdate" id="bookingStatusUpdate">
                                            <option value="Pending">Pending</option>
                                            <option value="Confirmed">Confirmed</option>
                                            <option value="Completed">Completed</option>
                                            <option value="Cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Payment Status:</label>
                                    <select class="form-control" name="paymentStatus" id="paymentStatus">
                                        <option value="Down Payment">Down Payment</option>
                                        <option value="Fully Paid">Fully Paid</option>
                                    </select>
                                </div>
                                <!-- Add Payment Section -->
                                <div class="card mt-4">
                                    <button type="button" class="btn btn-primary w-100" onclick="openPaymentModal(document.getElementById('modalBookingId').textContent, document.getElementById('bookBalance').value)">
                                        Add Payment
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Payment Modal -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPaymentModalLabel">Add Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPaymentForm">
                        <input type="hidden" id="paymentBookingId" name="booking_id">
                        <input type="hidden" id="currentBalance" name="current_balance">
                        <input type="hidden" id="paymentOwnerId" name="customer_id">

                        <div class="mb-3">
                            <label for="amountPaid" class="form-label fw-bold text-brown">Amount Paid:</label>
                            <input type="number" class="form-control" id="amountPaid" name="amount_paid" required>
                        </div>

                        <div class="mb-3">
                            <label for="paymentModeAdd" class="form-label fw-bold text-brown">Mode of Payment:</label>
                            <select class="form-control" id="paymentModeAdd" name="payment_mode" required>
                                <option value="cash">Cash</option>
                                <option value="gcash">GCash</option>
                                <option value="maya">Maya</option>
                                <option value="others">Others</option>
                            </select>
                        </div>

                        <div class="mb-3 d-none" id="otherPaymentMode">
                            <label for="otherPaymentModeInput" class="form-label fw-bold text-brown">Specify Payment Mode:</label>
                            <input type="text" class="form-control" id="otherPaymentModeInput" name="other_payment_mode">
                        </div>

                        <div class="mb-3">
                            <label for="refNo" class="form-label fw-bold text-brown">Reference No.:</label>
                            <input type="text" class="form-control" id="refNo" name="reference_no">
                        </div>

                        <div class="mb-3">
                            <label for="paymentStatusAdd" class="form-label fw-bold text-brown">Payment Status:</label>
                            <select class="form-control" id="paymentStatusAdd" name="payment_status" required>
                                <option value="Down Payment">Down Payment</option>
                                <option value="Fully Paid">Fully Paid</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="savePaymentBtn">Save Payment</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update date display
            function updateDateDisplay() {
                const now = new Date();

                // Get day of week, month, day, and year separately
                const dayOfWeek = now.toLocaleDateString('en-US', {
                    weekday: 'long'
                });
                const month = now.toLocaleDateString('en-US', {
                    month: 'long'
                });
                const day = now.getDate();
                const year = now.getFullYear();

                // Combine them without comma
                const formattedDate = `${dayOfWeek} ${month} ${day} ${year}`;

                // Update the element if it exists
                const dateElement = document.getElementById('current-date');
                if (dateElement) {
                    dateElement.textContent = formattedDate;
                }
            }

            // Call immediately and set up to update daily
            updateDateDisplay();
            setInterval(updateDateDisplay, 86400000); // Update once per day (in milliseconds)

            // Search functionality
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.reservations tbody tr');

                    rows.forEach(row => {
                        // Get all the text content from the row's cells
                        const id = row.querySelector('.deets-id button').textContent.trim().toLowerCase();
                        const pet = row.querySelector('.deets-pet').textContent.trim().toLowerCase();
                        const service = row.querySelector('.deets-service').textContent.trim().toLowerCase();
                        const name = row.querySelector('.deets-name').textContent.trim().toLowerCase();
                        const payment = row.querySelector('.deets-payment').textContent.trim().toLowerCase();
                        const date = row.querySelector('.deets-date').textContent.trim().toLowerCase();

                        // Check if any of the fields contain the search term
                        if (id.includes(searchTerm) ||
                            pet.includes(searchTerm) ||
                            service.includes(searchTerm) ||
                            name.includes(searchTerm) ||
                            payment.includes(searchTerm) ||
                            date.includes(searchTerm)) {
                            row.classList.remove('hidden-row');
                        } else {
                            row.classList.add('hidden-row');
                        }
                    });
                });
            }

            // Modal functionality
            var bookingModal = document.getElementById('bookingModal');
            if (bookingModal) {
                bookingModal.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    var bookingId = button.getAttribute('data-booking-id');
                    var modalBookingId = document.getElementById('modalBookingId');
                    modalBookingId.textContent = bookingId;

                    var service = button.getAttribute('data-service');
                    var modalHeader = document.getElementById('modalHeader');
                    modalHeader.className = 'modal-header ' + (service.toLowerCase() === 'pet hotel' ? 'modal-hotel' : 'modal-daycare');

                    document.getElementById('ownerName').value = button.getAttribute('data-owner-name');
                    document.getElementById('ownerId').value = button.getAttribute('data-owner-id');
                    document.getElementById('contact').value = button.getAttribute('data-owner-num');
                    document.getElementById('petName').value = button.getAttribute('data-pet-name');
                    document.getElementById('petBreed').value = button.getAttribute('data-pet-breed');
                    document.getElementById('petType').value = button.getAttribute('data-pet-size');
                    document.getElementById('service').value = button.getAttribute('data-service');
                    document.getElementById('checkIn').value = button.getAttribute('data-check-in');
                    document.getElementById('checkOut').value = button.getAttribute('data-check-out');
                    document.getElementById('paymentStatus').value = button.getAttribute('data-payment-status');
                    document.getElementById('paymentMode').value = button.getAttribute('data-mop');
                    document.getElementById('referenceNo').value = button.getAttribute('data-reference-number');
                    document.getElementById('bookBalance').value = button.getAttribute('data-book-balance');
                    
                    // Handle proof of payment
                    const proofOfPayment = button.getAttribute('data-proof-of-payment');
                    const proofContainer = document.getElementById('proofOfPaymentContainer');
                    
                    if (proofOfPayment && proofOfPayment !== 'null' && proofOfPayment !== '') {
                        const proofPath = '/Adorafur/' + proofOfPayment;
                        proofContainer.innerHTML = `<a href="${proofPath}" target="_blank" class="btn" id="view-photo">View Proof</a>`;
                    } else {
                        proofContainer.innerHTML = '<span>No proof of payment</span>';
                    }

                    // Fetch booking status from server
                    fetch('get_booking_data.php?booking_id=' + bookingId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.booking_status) {
                                var bookingStatusSelect = document.getElementById('bookingStatusUpdate');
                                
                                for (var i = 0; i < bookingStatusSelect.options.length; i++) {
                                    if (bookingStatusSelect.options[i].value === data.booking_status) {
                                        bookingStatusSelect.selectedIndex = i;
                                        break;
                                    }
                                }
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            }

            // Save button functionality
            const saveButton = document.getElementById('saveButton');
            if (saveButton) {
                saveButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    var formData = new FormData();
                    
                    formData.append('booking_id', document.getElementById('modalBookingId').textContent);
                    formData.append('checkIn', document.getElementById('checkIn').value);
                    formData.append('checkOut', document.getElementById('checkOut').value);
                    formData.append('booking_status', document.getElementById('bookingStatusUpdate').value);
                    formData.append('paymentStatus', document.getElementById('paymentStatus').value);
                    formData.append('staff', document.getElementById('staffSelect').value);

                    fetch('update_booking.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                alert('Booking updated successfully!');
                                location.reload();
                            } else {
                                alert('Error updating booking: ' + (result.message || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error updating the booking!');
                        });
                });
            }

            // Add event listeners to check-in and check-out date inputs to recalculate total amount and balance
            const checkInInput = document.getElementById('checkIn');
            const checkOutInput = document.getElementById('checkOut');
            
            if (checkInInput) {
                checkInInput.addEventListener('change', recalculateBookingAmount);
            }
            
            if (checkOutInput) {
                checkOutInput.addEventListener('change', recalculateBookingAmount);
            }

            // Add Payment Modal functionality
            const paymentModeSelect = document.getElementById("paymentModeAdd");
            if (paymentModeSelect) {
                paymentModeSelect.addEventListener("change", function() {
                    const otherPaymentMode = document.getElementById("otherPaymentMode");
                    if (this.value === "others") {
                        otherPaymentMode.classList.remove("d-none");
                    } else {
                        otherPaymentMode.classList.add("d-none");
                    }
                });
            }

            const savePaymentBtn = document.getElementById("savePaymentBtn");
            if (savePaymentBtn) {
                savePaymentBtn.addEventListener("click", function() {
                    const form = document.getElementById("addPaymentForm");

                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return;
                    }

                    // Disable button to prevent double submission
                    savePaymentBtn.disabled = true;
                    savePaymentBtn.textContent = "Processing...";

                    const formData = new FormData(form);

                    const amountPaid = Number.parseFloat(formData.get("amount_paid"));
                    const currentBalance = Number.parseFloat(formData.get("current_balance"));

                    if (amountPaid <= 0) {
                        alert("Amount paid must be greater than zero.");
                        savePaymentBtn.disabled = false;
                        savePaymentBtn.textContent = "Save Payment";
                        return;
                    }

                    if (amountPaid > currentBalance) {
                        alert("Amount paid cannot be greater than the current balance.");
                        savePaymentBtn.disabled = false;
                        savePaymentBtn.textContent = "Save Payment";
                        return;
                    }

                    fetch("add_payment.php", {
                            method: "POST",
                            body: formData,
                        })
                        .then((response) => response.text())
                        .then((text) => {
                            // Try to parse as JSON, but handle HTML responses
                            let data;
                            try {
                                data = JSON.parse(text);
                            } catch (e) {
                                console.error("Server returned non-JSON response:", text);
                                throw new Error("Server returned an invalid response. Check server logs.");
                            }

                            if (data.success) {
                                // Close the modal
                                const modalElement = document.getElementById("addPaymentModal");
                                const modal = bootstrap.Modal.getInstance(modalElement);
                                if (modal) {
                                    modal.hide();
                                }

                                // Update the UI with new balance
                                if (data.booking_balance !== undefined) {
                                    document.getElementById("bookBalance").value = data.booking_balance;

                                    // Update payment status if balance is zero
                                    if (Number.parseFloat(data.booking_balance) === 0) {
                                        document.getElementById("paymentStatus").value = "Fully Paid";
                                    } else if (Number.parseFloat(data.booking_balance) > 0) {
                                        document.getElementById("paymentStatus").value = "Down Payment";
                                    }

                                    // Show success message
                                    alert("Payment added successfully! New balance: " + data.booking_balance);
                                } else {
                                    alert("Payment added successfully!");
                                }
                            } else {
                                alert("Error: " + (data.message || "Unknown error occurred"));
                            }
                        })
                        .catch((error) => {
                            console.error("Error:", error);
                            alert("An error occurred while processing the payment: " + error.message);
                        })
                        .finally(() => {
                            // Re-enable button
                            savePaymentBtn.disabled = false;
                            savePaymentBtn.textContent = "Save Payment";
                        });
                });
            }
        });

        // Function to open payment modal
        function openPaymentModal(bookingId, currentBalance) {
            document.getElementById("paymentBookingId").value = bookingId;
            document.getElementById("currentBalance").value = currentBalance;
            document.getElementById("paymentOwnerId").value = document.getElementById("ownerId").value;

            // Reset form
            document.getElementById("addPaymentForm").reset();
            document.getElementById("amountPaid").value = "";
            document.getElementById("refNo").value = "";
            document.getElementById("otherPaymentMode").classList.add("d-none");

            // Open modal
            const paymentModal = new bootstrap.Modal(document.getElementById("addPaymentModal"));
            paymentModal.show();
        }

        // Function to recalculate booking amount
        function recalculateBookingAmount() {
            const checkIn = document.getElementById('checkIn').value;
            const checkOut = document.getElementById('checkOut').value;
            const bookingId = document.getElementById('modalBookingId').textContent;
            const petSize = document.getElementById('petType').value;
            const service = document.getElementById('service').value;
            
            // Validate dates
            if (!checkIn || !checkOut) {
                return;
            }
            
            // Ensure check-out is after check-in
            if (new Date(checkOut) <= new Date(checkIn)) {
                alert('Check-out date must be after check-in date');
                return;
            }
            
            // Call API to recalculate amount
            fetch('recalculate_booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    booking_id: bookingId,
                    check_in: checkIn,
                    check_out: checkOut,
                    pet_size: petSize,
                    service: service
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the UI with new values
                    document.getElementById('bookBalance').value = data.booking_balance;
                    
                    // Update payment status if balance is zero
                    if (parseFloat(data.booking_balance) === 0) {
                        document.getElementById('paymentStatus').value = 'Fully Paid';
                    } else if (parseFloat(data.booking_balance) > 0) {
                        document.getElementById('paymentStatus').value = 'Down Payment';
                    }
                    
                    // Show notification
                    alert('Booking amount recalculated. New balance: ' + data.booking_balance);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while recalculating the booking amount.');
            });
        }
    </script>
</body>

</html>
