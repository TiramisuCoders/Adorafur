<?php
session_start();    

require_once ('connect.php');

$petSelected = false;
$dateSelected = false;
$timeSelected = false;
$isLoggedIn = isset($_SESSION['c_id']);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['pet_type'])) {
        $_SESSION['pet_type'] = $_POST['pet_type'];
        $petSelected = true;
        echo json_encode(['success' => true, 'message' => 'Pet type saved']);
        exit;
    }
    
    if (isset($_POST['selected_date'])) {
        $_SESSION['selected_date'] = $_POST['selected_date'];
        $dateSelected = true;
        
        // Get available slots for the selected date
        $date = $_POST['selected_date'];
        $serviceType = 'Pet Daycare';
        
        // Get total bookings for the selected date
        $stmt = $conn->prepare("
            SELECT COUNT(*) as booking_count 
            FROM bookings b
            JOIN service s ON b.service_id = s.service_id
            WHERE DATE(b.booking_check_in) = :date
            AND s.service_name = :service_type
            AND b.booking_status IN ('Confirmed', 'Pending')
        ");
        $stmt->execute(['date' => $date, 'service_type' => $serviceType]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $bookingCount = $result['booking_count'];
        
        // Standard slots per day (you can adjust this value)
        $standardSlots = 10;
        $availableSlots = $standardSlots - $bookingCount;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Date saved',
            'availableSlots' => max(0, $availableSlots)
        ]);
        exit;
    }
    
    if (isset($_POST['check_in_time'])) {
        $_SESSION['check_in_time'] = $_POST['check_in_time'];
        
        // Calculate default check out time (3 hours after check in)
        $checkInTime = $_POST['check_in_time'];
        $checkInHour = intval(substr($checkInTime, 0, 2));
        $checkInPeriod = substr($checkInTime, -2);
        
        // Convert to 24-hour format for calculation
        if ($checkInPeriod == 'PM' && $checkInHour < 12) {
            $checkInHour += 12;
        } else if ($checkInPeriod == 'AM' && $checkInHour == 12) {
            $checkInHour = 0;
        }
        
        // Add 3 hours
        $checkOutHour = $checkInHour + 3;
        
        // Convert back to 12-hour format
        $checkOutPeriod = 'AM';
        if ($checkOutHour >= 12) {
            $checkOutPeriod = 'PM';
            if ($checkOutHour > 12) {
                $checkOutHour -= 12;
            }
        }
        
        $checkOutTime = sprintf("%d:00 %s", $checkOutHour, $checkOutPeriod);
        $_SESSION['check_out_time'] = $checkOutTime;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Check-in time saved',
            'defaultCheckOut' => $checkOutTime
        ]);
        exit;
    }
    
    if (isset($_POST['check_out_time'])) {
        $_SESSION['check_out_time'] = $_POST['check_out_time'];
        $timeSelected = true;
        echo json_encode(['success' => true, 'message' => 'Check-out time saved']);
        exit;
    }
    
    // These requests require authentication
    if (isset($_POST['get_pets']) || isset($_POST['selected_pet'])) {
        if (!$isLoggedIn) {
            echo json_encode(['success' => false, 'requireLogin' => true, 'message' => 'Login required']);
            exit;
        }
        
        if (isset($_POST['get_pets'])) {
            // Get pets for the logged-in user
            $userId = $_SESSION['c_id'];
            $stmt = $conn->prepare("SELECT pet_id, pet_name, pet_breed, pet_age, pet_gender, pet_size FROM pet WHERE customer_id = :customer_id");
            $stmt->execute(['customer_id' => $userId]);
            $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'pets' => $pets]);
            exit;
        }
        
        if (isset($_POST['selected_pet'])) {
            // Get pet details
            $petId = $_POST['selected_pet'];
            $stmt = $conn->prepare("
                SELECT p.pet_id, p.pet_name, p.pet_breed, p.pet_age, p.pet_gender, p.pet_size, s.service_rate 
                FROM pet p
                JOIN service s ON p.pet_size = s.service_variant OR (p.pet_size = 'Cat' AND s.service_variant = 'Cats')
                WHERE p.pet_id = :pet_id AND s.service_name = 'Pet Daycare'
            ");
            $stmt->execute(['pet_id' => $petId]);
            $petDetails = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'petDetails' => $petDetails]);
            exit;
        }
    }
}


// Set variables based on session
if (isset($_SESSION['pet_type'])) $petSelected = true;
if (isset($_SESSION['selected_date'])) $dateSelected = true;
if (isset($_SESSION['check_in_time']) && isset($_SESSION['check_out_time'])) $timeSelected = true;

// Get user details
$userDetails = null;
if($isLoggedIn){
    $userId = $_SESSION['c_id'];
    $stmt = $conn->prepare("SELECT c_first_name, c_last_name, c_email FROM customer WHERE c_id = :user_id");
    $stmt->execute(['user_id' => $userId]);
    $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);
}

function generateCalendar($month, $year) {
    $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
    $numberDays = date('t', $firstDayOfMonth);
    $dateComponents = getdate($firstDayOfMonth);
    $monthName = $dateComponents['month'];
    $dayOfWeek = $dateComponents['wday'];
    $dateToday = date('Y-m-d');

    $calendar = "<table class='calendar'>";
    $calendar .= "<caption>$monthName $year</caption>";
    $calendar .= "<tr>";

    $weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    foreach($weekdays as $day) {
        $calendar .= "<th class='header'>$day</th>";
    }

    $calendar .= "</tr><tr>";

    if ($dayOfWeek > 0) { 
        $calendar .= "<td colspan='$dayOfWeek'>&nbsp;</td>"; 
    }

    $currentDay = 1;

    while ($currentDay <= $numberDays) {
        if ($dayOfWeek == 7) {
            $dayOfWeek = 0;
            $calendar .= "</tr><tr>";
        }
        
        $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);
        $date = "$year-$month-$currentDayRel";
        
        $today = $date == $dateToday ? "today" : "";
        $past = strtotime($date) < strtotime($dateToday) ? "past" : "";
        
        $calendar .= "<td class='day $today $past' data-date='$date'>";
        $calendar .= $currentDay;
        $calendar .= "</td>";
        
        $currentDay++;
        $dayOfWeek++;
    }

    if ($dayOfWeek != 7) { 
        $remainingDays = 7 - $dayOfWeek;
        $calendar .= "<td colspan='$remainingDays'>&nbsp;</td>"; 
    }

    $calendar .= "</tr>";
    $calendar .= "</table>";

    return $calendar;
}

function outputBookingCalendar() {
    $month = date('m');
    $year = date('Y');
    echo generateCalendar($month, $year);
}


// Get service details
$stmt = $conn->prepare("SELECT * FROM service 
                        WHERE LOWER(service_name) = LOWER('pet daycare')
                        AND LOWER(service_variant) = LOWER('small dog')");
$stmt->execute();                        
$smallDaycare = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM service 
                        WHERE LOWER(service_name) = LOWER('pet daycare')
                        AND LOWER(service_variant) = LOWER('medium dog')");
$stmt->execute();                        
$medDaycare = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM service 
                        WHERE LOWER(service_name) = LOWER('pet daycare')
                        AND LOWER(service_variant) = LOWER('large dog')");
$stmt->execute();                        
$largeDaycare = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM service 
                        WHERE LOWER(service_name) = LOWER('pet daycare')
                        AND LOWER(service_variant) = LOWER('cats')");
$stmt->execute();                        
$catDaycare = $stmt->fetch(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book with Adorafur</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">

    <!-- Your custom CSS -->
    <link rel="stylesheet" href="book-pet-hotel.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Your custom JavaScript -->
    <script src="booking-daycare.js" defer></script>
    <link rel="icon" type="image/png" href="Header-Pics/logo.png">

</head>

<body>

    <?php include 'header.php'; ?>

    <div class="main">
        <div class="main-container">
            <div class="pet-hotel-title" id="flex">PET DAYCARE</div>
            <hr class="hr" id="flex">
            <div class="content-wrapper">
                <div class="calendar" id="flex">
                    <div class="calendar-header">
                        <button id="prevMonth" class="nav-arrow">&lt;</button>
                        <div class="month-year">
                            <span id="month"></span>
                            <span id="year"></span>
                        </div>
                        <button id="nextMonth" class="nav-arrow">&gt;</button>
                    </div>
                    <div class="line"></div>
                    <div class="calendar-body">
                        <div class="weekdays">
                            <div>Sun</div>
                            <div>Mon</div>
                            <div>Tue</div>
                            <div>Wed</div>
                            <div>Thu</div>
                            <div>Fri</div>
                            <div>Sat</div>
                        </div>
                        <div id="days" class="days-grid"></div>
                    </div>
                </div>

                <!-- Booking Section -->
                <div class="main-schedule-options">
                    <div class="schedule-options">
                        <div class="available-slot" id="align-1">Available Slots: <span id="availableSlotCount">-</span></div>
                        <!-- Bootstrap Dropdown -->
                        <div class="selection-dropdown" id="align-1">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="petSelectionMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Choose Pet
                            </button>
                            <div class="dropdown-menu" aria-labelledby="petSelectionMenu">
                                <button class="dropdown-item pet-type" id="dd-item-dog" type="button">Dog</button>
                                <button class="dropdown-item pet-type" id="dd-item-cat" type="button">Cat</button>
                            </div>
                        </div>
                    </div>
                    <div class="pet-selection">
                        <div class="pet-information-dog">
                            <div class="pet-info dog-info">
                                <img src="Booking/small_dog.png" alt="Small Dog" class="small-dog" data-selected-src="Booking/small_dog(selected).png">
                                <h3><?php echo $smallDaycare['service_variant']; ?></h3>
                                <h6>Weight: 10kg<br>
                                    ₱ <?php echo $smallDaycare['service_rate']; ?></h6>
                            </div>

                            <div class="pet-info dog-info">
                                <img src="Booking/reg_dog.png" alt="Regular Dog" class="reg-dog" data-selected-src="Booking/reg_dog(selected).png">
                                <h3><?php echo $medDaycare['service_variant']; ?></h3>
                                <h6>Weight: 26 - 40 lbs<br>
                                    ₱ <?php echo $medDaycare['service_rate']; ?></h6>
                            </div>

                            <div class="pet-info dog-info">
                                <img src="Booking/large_dog.png" alt="Large Dog" class="large-dog" data-selected-src="Booking/large_dog(selected).png">
                                <h3><?php echo $largeDaycare['service_variant']; ?></h3>
                                <h6>Weight: 40 lbs and above<br>
                                    ₱ <?php echo $largeDaycare['service_rate']; ?></h6>
                            </div>
                        </div>

                        <div class="pet-information-cat">
                            <div class="pet-info cat-info">
                                <img src="Booking/reg_cat.png" alt="Cat" class="cat" data-selected-src="Booking/reg_cat(selected).png">
                                <h3><?php echo $catDaycare['service_variant']; ?></h3>
                                <h6>Weight: 4 - 5kg<br>
                                    ₱ <?php echo $catDaycare['service_rate']; ?></h6>
                            </div>
                        </div>
                    </div>
                    <div class="checkin-out">
                        <div class="check-in" id="check">
                            <h3>Check In: </h3>
                            <div class="selection-dropdown-check" id="align-1">
                                <button class="btn btn-secondary dropdown-toggle" type="button" id="checkInMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Choose Time
                                </button>
                                <div class="dropdown-menu" aria-labelledby="checkInMenu">
                                    <button class="dropdown-item check-in-time" type="button">10:00 AM</button>
                                    <button class="dropdown-item check-in-time" type="button">11:00 AM</button>
                                    <button class="dropdown-item check-in-time" type="button">12:00 PM</button>
                                    <button class="dropdown-item check-in-time" type="button">1:00 PM</button>
                                    <button class="dropdown-item check-in-time" type="button">2:00 PM</button>
                                    <button class="dropdown-item check-in-time" type="button">3:00 PM</button>
                                    <button class="dropdown-item check-in-time" type="button">4:00 PM</button>
                                </div>
                            </div>
                        </div>


                        <div class="check-in" id="check">
                            <h3>Check Out: </h3>
                            <div class="selection-dropdown-check" id="align-1">
                                <button class="btn btn-secondary dropdown-toggle" type="button" id="checkOutMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Choose Time
                                </button>
                                <div class="dropdown-menu" aria-labelledby="checkOutMenu">
                                    <!-- Check-out times will be dynamically populated based on check-in time -->
                                </div>
                            </div>
                        </div>

                            <div class="book">BOOK</div>

                    </div>  
                </div>
                <?php if ($isLoggedIn): ?>
                <div class="book-1">
                    <div class="book-label">
                        <div class="client">
                            <b><?php echo $userDetails['c_first_name'] . ' ' . $userDetails['c_last_name']; ?></b><br>
                            <span class="client-email"><?php echo $userDetails['c_email']; ?></span>
                        </div>
                        <div class="pet-1">
                            <div class="pets"><b>Pet/s</b></div>

                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>NAME</th>
                                        <th>BREED</th>
                                        <th>AGE</th>
                                        <th>GENDER</th>
                                        <th>SIZE</th>
                                        <th>PRICE</th>
                                    </tr>
                                </thead>
                                <tbody id="petTableBody">
                                    <tr id="petSelectionRow">
                                        <td colspan="6">
                                            <select id="petDropdown" class="form-control">
                                                <option value="">Select a pet</option>
                                                <!-- Pet options will be loaded dynamically -->
                                            </select>
                                        </td>
                                    </tr>
                                    <!-- Additional rows will be added dynamically when pets are selected -->
                                </tbody>
                            </table>

                            <div class="lower-section">
                                <button type="button" class="btn" id="regPet" data-bs-toggle="modal" data-bs-target="#petRegistrationModal">
                                    <h6 class="regnewpet" style="font-weight: 600;">Need to register new pet?</h6>
                                </button>

                                <div class="modal fade" id="petRegistrationModal" data-backdrop="static" data-keyboard="false" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header d-flex justify-content-center align-items-center" id="modalHeader">
                                                <h1 class="modal-title" id="modalTitle">Register Your Pet</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body" id="modalBody">
                                                <div class="pet-registration-form">
                                                    <form class="pet-form" method="post" enctype="multipart/form-data">
                                                        <div class="container-fluid p-0">
                                                            <div class="row">
                                                                <!-- Left Column -->
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label for="petName" class="form-label">Pet Name</label>
                                                                        <input type="text" id="petName" name="pet_name" class="form-control" required>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label">Pet Size</label>
                                                                        <div class="radio-group">
                                                                            <div>
                                                                                <input type="radio" name="pet_size" id="sizeSmallDog" value="Small">
                                                                                <label for="sizeSmallDog">Small Dog</label>
                                                                            </div>
                                                                            <div>
                                                                                <input type="radio" name="pet_size" id="sizeLargeDog" value="Large">
                                                                                <label for="sizeLargeDog">Large Dog</label>
                                                                            </div>
                                                                            <div>
                                                                                <input type="radio" name="pet_size" id="sizeRegularDog" value="Medium">
                                                                                <label for="sizeRegularDog">Regular Dog</label>
                                                                            </div>
                                                                            <div>
                                                                                <input type="radio" name="pet_size" id="sizeRegularCat" value="Cat">
                                                                                <label for="sizeRegularCat">Regular Cat</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label for="petBreed" class="form-label">Breed</label>
                                                                        <input type="text" id="petBreed" name="breed" class="form-control" placeholder="Type Breed Here">
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label for="petAge" class="form-label">Age</label>
                                                                        <input type="text" id="petAge" name="age" class="form-control" placeholder="Type Age Here">
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label">Gender</label>
                                                                        <div class="radio-group">
                                                                            <div>
                                                                                <input type="radio" name="gender" id="genderMale" value="Male">
                                                                                <label for="genderMale">Male</label>
                                                                            </div>
                                                                            <div>
                                                                                <input type="radio" name="gender" id="genderFemale" value="Female">
                                                                                <label for="genderFemale">Female</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label for="petDescription" class="form-label">Description</label>
                                                                        <textarea id="petDescription" name="description" class="form-control" placeholder="e.g., White Spots" rows="3"></textarea>
                                                                    </div>
                                                                </div>

                                                                <!-- Right Column -->
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label for="petProfilePhoto" class="form-label">Pet Profile Photo</label>
                                                                        <input type="file" id="petProfilePhoto" name="pet_photo" class="form-control" accept="image/*,application/pdf">
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label class="form-label">Vaccination Status</label>
                                                                        <input type="file" name="vaccination_file" class="form-control mb-2" accept="image/*,application/pdf">
                                                                        <div class="radio-group">
                                                                            <div>
                                                                                <input type="radio" name="vaccination_status" id="vaccinatedYes" value="Up to date">
                                                                                <label for="vaccinatedYes">Vaccinated</label>
                                                                            </div>
                                                                            <div>
                                                                                <input type="radio" name="vaccination_status" id="vaccinatedNo" value="Not vaccinated">
                                                                                <label for="vaccinatedNo">Not Vaccinated</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label for="dateAdministered" class="form-label">Date Administered</label>
                                                                        <input type="date" id="dateAdministered" name="date_administered" class="form-control">
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label for="expiryDate" class="form-label">Expiry Date</label>
                                                                        <input type="date" id="expiryDate" name="expiry_date" class="form-control">
                                                                    </div>

                                                                    <div class="mb-3">
                                                                        <label for="specialInstructions" class="form-label">Special Instructions</label>
                                                                        <textarea id="specialInstructions" name="special_instructions" class="form-control" placeholder="e.g., Medications" rows="3"></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row mt-3">
                                                                <div class="col-12 text-center">
                                                                    <button type="submit" class="btn" id="saveButton">Save and Go Back</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Payment Modal -->
                                <!-- Payment Modal Button -->
                                <div class="proctopayment">
                                    <button type="button" class="btn payment-btn" data-bs-toggle="modal" data-bs-target="#petPaymentModal">
                                        Proceed to Payment
                                    </button>


                                    <!-- Payment Modal -->
                                    <div class="modal fade" id="petPaymentModal" tabindex="-1" aria-labelledby="petPaymentModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-body p-0">
                                                    <div class="payment-modal-content">
                                                        <h1>Let's Seal the Deal!</h1>
                                                        <p class="subtitle">To finalize your pet's stay, please scan the QR code below to securely process your payment.</p>

                                                        <div class="modal-grid">
                                                            <div class="details-section">
                                                                <p class="transaction-no">Transaction No. 4565789</p>
                                                                <h2 class="pet-name">Good Boi</h2>
                                                                <p class="dates">October 5, 12:00 NN - 6:00 PM</p>

                                                                <div class="info-grid">
                                                                    <div class="info-row"><span class="label">Service:</span><span class="value">Pet Daycare</span></div>
                                                                    <div class="info-row"><span class="label">Breed:</span><span class="value">Shih Tzu</span></div>
                                                                    <div class="info-row"><span class="label">Gender:</span><span class="value">Male</span></div>
                                                                    <div class="info-row"><span class="label">Age:</span><span class="value">7 years old</span></div>
                                                                    <div class="info-row"><span class="label">Owner:</span><span class="value">Jude Emmanuel Flores</span></div>
                                                                    <div class="info-row"><span class="label">Amount to pay:</span><span class="value">₱ 250.00</span></div>
                                                                    <div class="info-row"><span class="label">Remaining Balance:</span><span class="value">₱ 250.00</span></div>
                                                                </div>

                                                                <form method="POST" enctype="multipart/form-data">
                                                                    <div class="payment-section">
                                                                        <p class="section-label">Mode of Payment</p>
                                                                        <div class="radio-group">
                                                                            <label><input type="radio" name="payment_method" value="Maya" checked> <span>Maya</span></label>
                                                                            <label><input type="radio" name="payment_method" value="GCash"> <span>GCash</span></label>
                                                                        </div>

                                                                        <p class="section-label">Reference No. of Your Payment</p>
                                                                        <input type="text" name="reference_no" placeholder="Enter Reference Number" class="reference-input" required>

                                                                        <p class="section-label">Proof of Payment</p>
                                                                        <input type="file" name="payment_proof" accept="image/*" required>
                                                                    </div>


                                                                </form>
                                                            </div>

                                                            <div class="qr-section">
                                                                <div class="qr-codes">
                                                                    <img src="gcash.png" alt="GCash QR Code" class="qr-code">
                                                                    <img src="maya.png" alt="Maya QR Code" class="qr-code">
                                                                </div>
                                                                <p class="qr-instruction">We accept bank transfer to our GCash/Maya account or just scan the QR Code!</p>
                                                                <div class="account-info">
                                                                    <p>Account Number: <span>987654321</span></p>
                                                                    <p>Account Name: <span>Veatrice Delos Santos</span></p>
                                                                </div>
                                                                <button type="button" class="action-btn" style="margin-bottom: 100px;"
                                                                    data-bs-dismiss="modal"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#waiverForm">
                                                                    Complete Booking
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="waiverForm" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header" id="waiverForm-header">
                                                    <h1 class="  id="waiverForm-header">
                                                    <h1 class="modal-title" id="waiverForm-title">Liability Release and Waiver Form</h1>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body" id="waiverForm-body">

                                                    <p>
                                                        We care about the safety and wellbeing of all pets. We want to assure you that we will make every effort to make your pet's stay with us as pleasant as possible.
                                                        While we provide the best care for your fur babies, there are possible risks that come with availing of pet boarding services.
                                                    </p>

                                                    <ul>
                                                        <li>
                                                            Owner represents that his/her pet is in all respects healthy and received all required vaccines (Distemper/ Canine Adenovirus-2, Canine Parvovirus-2 and Rabies), currently flea protection (Frontline, Advantage or Revolution for dogs) and that said pet does not suffer from any disability, illness or condition which could affect the said paid, other pets, employees and customers safety.
                                                            If the Owner's pet has external parasites, Owner agrees by signing this form that ADORAFUR HAPPY STAY may apply frontline spray to Owner's pet at Owner's own cost, for such parasites so as not to contaminate this facility or the other pets saying at ADORAFUR HAPPY STAY.
                                                        </li>

                                                        <li>
                                                            I recognize that there are inherent risks of injury or illness in any environment associated with cageless pets in daycare and in boarding environments.
                                                            I also recognize that such risks may include, without limitation, injuries or illnesses resulting from fights, rough play and contagious diseases.
                                                            Knowing such inherent risks and dangers, I understand and affirm that ADORAFUR HAPPY STAY cannot be held responsible for any injury, illness or damage caused by my pet and that I am solely responsible for the same.
                                                            I agree to hold ADORAFUR HAPPY STAY free and harmless from any claims for damage, all defense costs, fees and business losses arising from any claim or any third party may have against ADORAFUR HAPPY STAY.
                                                        </li>

                                                        <li>
                                                            Pets must be sociable to be allowed to stay with us.
                                                            Some pets may have aggressive tendencies if triggered, despite being able to socialize.
                                                            If your pet has any history of aggression such as food, territorial, possessive aggression, or if they don't want to be touched in a certain body part, please inform us so we may cater to their behavior needs.
                                                            As much as possible we would love to avoid using restricting instruments to pets. However, if the need arise we may isolate, crate, leash or muzzle an aggressive pet.
                                                            In any case, we reserve the right to refuse any pet that are hostile, aggressive and appear to be ill for everyone's safety.
                                                        </li>

                                                    </ul>

                                                    <p>
                                                        <input type="checkbox" id="waiverForm-checkbox" name="agree" value="1" required>
                                                        I have read and agree with the Liability Release and Waiver Form
                                                    </p>
                                                </div>

                                                <div class="modal-footer" id="waiverForm-footer">
                                                    <button type="button" class="btn" id="complete-booking">Complete Booking</button>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /.main-container -->
                <?php endif; ?>
            </div><!-- /.main -->
        </div>
    </div>
</body>

</html>