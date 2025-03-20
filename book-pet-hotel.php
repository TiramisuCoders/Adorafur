<?php
require_once 'connect.php'; // Include database connection

// Initialize session variables for booking flow
if (!isset($_SESSION)) {
    session_start();
}

// Initial state flags
$petSelected = isset($_SESSION['pet_type']);
$dateSelected = isset($_SESSION['selected_date']);
$timeSelected = isset($_SESSION['check_in_time']) && isset($_SESSION['check_out_time']);

// Process AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle pet type selection
    if (isset($_POST['pet_type'])) {
        $_SESSION['pet_type'] = $_POST['pet_type'];
        echo json_encode(['success' => true, 'message' => 'Pet type saved']);
        exit;
    }
    
    // Handle date selection
    if (isset($_POST['selected_date'])) {
        $_SESSION['selected_date'] = $_POST['selected_date'];
        echo json_encode(['success' => true, 'message' => 'Date saved']);
        exit;
    }
    
    // Handle check-in time
    if (isset($_POST['check_in_time'])) {
        $_SESSION['check_in_time'] = $_POST['check_in_time'];
        echo json_encode(['success' => true, 'message' => 'Check-in time saved']);
        exit;
    }
    
    // Handle check-out time
    if (isset($_POST['check_out_time'])) {
        $_SESSION['check_out_time'] = $_POST['check_out_time'];
        echo json_encode(['success' => true, 'message' => 'Check-out time saved']);
        exit;
    }
    
    // Process booking completion
    if (isset($_POST['complete_booking'])) {
        // Here you would save all booking data to database
        // For demonstration, we'll just return success
        echo json_encode(['success' => true, 'message' => 'Booking completed successfully']);
        exit;
    }
}

try {
    // Fetch pet details from the database
    $stmt = $conn->prepare("SELECT pet_id, pet_name, pet_breed, pet_age, pet_gender, pet_size FROM Pet");
    $stmt->execute();
    $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get pricing for different pet sizes
    $pricingStmt = $conn->prepare("SELECT service_name, service_rate FROM Service WHERE service_name LIKE 'Pet Hotel%'");
    $pricingStmt->execute();
    $pricing = $pricingStmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Fetch logged in customer info if available
    $customerInfo = null;
    if (isset($_SESSION['c_id'])) {
        $custStmt = $conn->prepare("SELECT * FROM Customer WHERE c_id = ?");
        $custStmt->execute([$_SESSION['c_id']]);
        $customerInfo = $custStmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Generate a unique transaction number
$transactionNo = 'TRX'.time().rand(1000, 9999);
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
    <script src="book.js"></script>


</head>

<body>

    <?php include 'header.php'; ?>

    <div class="main">
        <div class="main-container">
            <div class="pet-hotel-title" id="flex">PET HOTEL</div>
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
                        <div class="available-slot" id="align-1">Available Slots</div>
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
                                <h3>Small Dog</h3>
                                <h6>Weight: 10kg<br>
                                    ₱ 700</h6>
                            </div>

                            <div class="pet-info dog-info">
                                <img src="Booking/reg_dog.png" alt="Regular Dog" class="reg-dog" data-selected-src="Booking/reg_dog(selected).png">
                                <h3>Regular Dog</h3>
                                <h6>Weight: 26 - 40 lbs<br>
                                    ₱ 800</h6>
                            </div>

                            <div class="pet-info dog-info">
                                <img src="Booking/large_dog.png" alt="Large Dog" class="large-dog" data-selected-src="Booking/large_dog(selected).png">
                                <h3>Large Dog</h3>
                                <h6>Weight: 40 lbs and above<br>
                                    ₱ 900</h6>
                            </div>
                        </div>

                        <div class="pet-information-cat">
                            <div class="pet-info cat-info">
                                <img src="Booking/reg_cat.png" alt="Cat" class="cat" data-selected-src="Booking/reg_cat(selected).png">
                                <h3>Cat</h3>
                                <h6>Weight: 4 - 5kg<br>
                                    ₱ 500</h6>
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
                                    <button class="dropdown-item check-in-time" type="button">5:00 PM</button>
                                    <button class="dropdown-item check-in-time" type="button">6:00 PM</button>
                                    <button class="dropdown-item check-in-time" type="button">7:00 PM</button>
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
                                    <button class="dropdown-item check-out-time" type="button">10:00 AM</button>
                                    <button class="dropdown-item check-out-time" type="button">11:00 AM</button>
                                    <button class="dropdown-item check-out-time" type="button">12:00 PM</button>
                                    <button class="dropdown-item check-out-time" type="button">1:00 PM</button>
                                    <button class="dropdown-item check-out-time" type="button">2:00 PM</button>
                                    <button class="dropdown-item check-out-time" type="button">3:00 PM</button>
                                    <button class="dropdown-item check-out-time" type="button">4:00 PM</button>
                                    <button class="dropdown-item check-out-time" type="button">5:00 PM</button>
                                    <button class="dropdown-item check-out-time" type="button">6:00 PM</button>
                                    <button class="dropdown-item check-out-time" type="button">7:00 PM</button>
                                </div>
                            </div>
                        </div>

                        <div class="book">BOOK</div>

                    </div>
                </div>
                <div class="book-1">
                    <div class="book-label">
                        <div class="client">
                            <b>Client name</b><br>
                            <span class="client-email">Client Email</span>
                        </div>
                        <div class="pet-1">
                            <div class="pets"><b>Pet/s</b></div>

                            <table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Breed</th>
            <th>Age</th>
            <th>Gender</th>
            <th>Size</th>
            <th>Price</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody id="petTableBody">
        <tr>
            <!-- Dropdown inside the Name column -->
            <td data-label="Name">
                <select class="petSelect" onchange="updatePetDetails(this)">
                    <option value="">Choose Pet</option>
                    <?php foreach ($pets as $pet): ?>
                        <option value="<?= htmlspecialchars(json_encode([
                            'pet_breed' => $pet['pet_breed'],
                            'pet_age' => $pet['pet_age'],
                            'pet_gender' => $pet['pet_gender'],
                            'pet_size' => $pet['pet_size'],
                            'pet_price' => isset($pet['pet_price']) ? $pet['pet_price'] : '₱0.00'
                        ]), ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($pet['pet_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td data-label="Breed"></td>
            <td data-label="Age"></td>
            <td data-label="Gender"></td>
            <td data-label="Size"></td>
            <td data-label="Price">₱0.00</td>
            <td><button type="button" onclick="addPetRow()">+</button></td>
        </tr>
    </tbody>
</table>

<script>
    function updatePetDetails(selectElement) {
        let selectedPet = selectElement.value ? JSON.parse(selectElement.value) : null;
        let row = selectElement.closest("tr");

        row.querySelector("[data-label='Breed']").textContent = selectedPet ? selectedPet.pet_breed : "";
        row.querySelector("[data-label='Age']").textContent = selectedPet ? selectedPet.pet_age + " years" : "";
        row.querySelector("[data-label='Gender']").textContent = selectedPet ? selectedPet.pet_gender : "";
        row.querySelector("[data-label='Size']").textContent = selectedPet ? selectedPet.pet_size : "";
        
        // Set price based on pet size
        let price = "₱0.00";
        if (selectedPet) {
            switch(selectedPet.pet_size) {
                case 'Cat':
                    price = "₱500.00";
                    break;
                case 'Small':
                    price = "₱700.00";
                    break;
                case 'Medium':
                    price = "₱800.00";
                    break;
                case 'Large':
                case 'XL':
                case 'XXL':
                    price = "₱900.00";
                    break;
            }
        }
        row.querySelector("[data-label='Price']").textContent = price;
        
        // Update total price
        calculateTotalPrice();
    }

    function addPetRow() {
        let tableBody = document.getElementById("petTableBody");
        let newRow = document.createElement("tr");

        newRow.innerHTML = `
            <td data-label="Name">
                <select class="petSelect" onchange="updatePetDetails(this)">
                    <option value="">Choose Pet</option>
                    <?php foreach ($pets as $pet): ?>
                        <option value="<?= htmlspecialchars(json_encode([
                            'pet_breed' => $pet['pet_breed'],
                            'pet_age' => $pet['pet_age'],
                            'pet_gender' => $pet['pet_gender'],
                            'pet_size' => $pet['pet_size'],
                            'pet_price' => isset($pet['pet_price']) ? $pet['pet_price'] : '₱0.00'
                        ]), ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($pet['pet_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td data-label="Breed"></td>
            <td data-label="Age"></td>
            <td data-label="Gender"></td>
            <td data-label="Size"></td>
            <td data-label="Price">₱0.00</td>
            <td><button type="button" onclick="removePetRow(this)">-</button></td>
        `;

        tableBody.appendChild(newRow);
    }

    function removePetRow(button) {
        let row = button.closest("tr");
        row.remove();
        
        // Update total price after removing a pet
        calculateTotalPrice();
    }
    
    function calculateTotalPrice() {
        // Get all price cells
        let priceCells = document.querySelectorAll("[data-label='Price']");
        let total = 0;
        
        // Sum up all prices
        priceCells.forEach(cell => {
            let priceText = cell.textContent.replace('₱', '').replace(',', '');
            let price = parseFloat(priceText);
            if (!isNaN(price)) {
                total += price;
            }
        });
        
        // Format the total with two decimal places
        const formattedTotal = total.toFixed(2);
        
        // Set total in the payment modal
        let totalElement = document.getElementById("summaryTotalAmount");
        if (totalElement) {
            totalElement.textContent = "₱ " + formattedTotal;
        }
        
        // Set remaining balance (same as total for now)
        let balanceElement = document.getElementById("summaryRemainingBalance");
        if (balanceElement) {
            balanceElement.textContent = "₱ " + formattedTotal;
        }
        
        return total;
    }
</script>

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
                                                                                <input type="radio" name="pet_size" id="sizeSmallDog" value="small_dog">
                                                                                <label for="sizeSmallDog">Small Dog</label>
                                                                            </div>
                                                                            <div>
                                                                                <input type="radio" name="pet_size" id="sizeLargeDog" value="large_dog">
                                                                                <label for="sizeLargeDog">Large Dog</label>
                                                                            </div>
                                                                            <div>
                                                                                <input type="radio" name="pet_size" id="sizeRegularDog" value="regular_dog">
                                                                                <label for="sizeRegularDog">Regular Dog</label>
                                                                            </div>
                                                                            <div>
                                                                                <input type="radio" name="pet_size" id="sizeRegularCat" value="regular_cat">
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
                                                                                <input type="radio" name="gender" id="genderMale" value="male">
                                                                                <label for="genderMale">Male</label>
                                                                            </div>
                                                                            <div>
                                                                                <input type="radio" name="gender" id="genderFemale" value="female">
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
                                                                                <input type="radio" name="vaccination_status" id="vaccinatedYes" value="vaccinated">
                                                                                <label for="vaccinatedYes">Vaccinated</label>
                                                                            </div>
                                                                            <div>
                                                                                <input type="radio" name="vaccination_status" id="vaccinatedNo" value="not_vaccinated">
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
                            <p class="transaction-no">Transaction No. <?php echo $transactionNo; ?></p>
                            <h2 class="pet-name" id="summaryPetName">Good Boi</h2>
                            <div class="booking-dates">
                                <p><strong>Check in:</strong> <span id="summaryCheckIn">October 5, 12:00 PM</span></p>
                                <p><strong>Check out:</strong> <span id="summaryCheckOut">October 5, 6:00 PM</span></p>
                            </div>

                            <div class="info-grid">
                                <div class="info-row"><span class="label">Service:</span><span class="value">Pet Hotel</span></div>
                                <div id="petSummaryDetails">
                                    <!-- Pet details will be inserted here dynamically -->
                                </div>
                                <div class="info-row"><span class="label">Owner:</span><span class="value">
                                    <?php echo isset($customerInfo) ? htmlspecialchars($customerInfo['c_first_name'] . ' ' . $customerInfo['c_last_name']) : 'Jude Emmanuel Flores'; ?>
                                </span></div>
                                <div class="info-row"><span class="label">Amount to pay:</span><span class="value" id="summaryTotalAmount">₱ 250.00</span></div>
                                <div class="info-row"><span class="label">Remaining Balance:</span><span class="value" id="summaryRemainingBalance">₱ 250.00</span></div>
                            </div>

                            <form method="POST" enctype="multipart/form-data" id="paymentForm">
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
                                <img src="gcash.png" alt="GCash QR Code" class="qr-code" id="gcashQR" style="display: none;">
                                <img src="maya.png" alt="Maya QR Code" class="qr-code" id="mayaQR">
                            </div>
                            <p class="qr-instruction">We accept bank transfer to our GCash/Maya account or just scan the QR Code!</p>
                            <div class="account-info">
                                <p>Account Number: <span>987654321</span></p>
                                <p>Account Name: <span>Veatrice Delos Santos</span></p>
                            </div>
                            <button type="button" class="btn btn-primary action-btn" id="proceed-to-waiver" data-toggle="modal" data-target="#waiverForm" disabled>
                                Complete Booking
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                                    <div class="modal fade" id="waiverForm" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" id="waiverForm-header">
                <h1 class="modal-title" id="waiverForm-title">Liability Release and Waiver Form</h1>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="waiverForm-body">
                <!-- Waiver content remains the same -->
                <p>
                    We care about the safety and wellbeing of all pets. We want to assure you that we will make every effort to make your pet's stay with us as pleasant as possible.
                    While we provide the best care for your fur babies, there are possible risks that come with availing of pet boarding services.
                </p>

                <!-- Rest of the waiver content -->
                <ul>
                    <!-- <h6> Health & Vaccination Requirements</h6> -->
                    <li>
                        Owner represents that his/her pet is in all respects healthy and received all required vaccines (Distemper/ Canine Adenovirus-2, Canine Parvovirus-2 and Rabies), currently flea protection (Frontline, Advantage or Revolution for dogs) and that said pet does not suffer from any disability, illness or condition which could affect the said paid, other pets, employees and customers safety.
                        If the Owner's pet has external parasites, Owner agrees by signing this form that ADORAFUR HAPPY STAY may apply frontline spray to Owner's pet at Owner's own cost, for such parasites so as not to contaminate this facility or the other pets saying at ADORAFUR HAPPY STAY.
                    </li>

                    <!-- <h6> Risk and Responsibilities</h6> -->
                    <li>
                        I recognize that there are inherent risks of injury or illness in any environment associated with cageless pets in daycare and in boarding environments.
                        I also recognize that such risks may include, without limitation, injuries or illnesses resulting from fights, rough play and contagious diseases.
                        Knowing such inherent risks and dangers, I understand and affirm that ADORAFUR HAPPY STAY cannot be held responsible for any injury, illness or damage caused by my pet and that I am solely responsible for the same.
                        I agree to hold ADORAFUR HAPPY STAY free and harmless from any claims for damage, all defense costs, fees and business losses arising from any claim or any third party may have against ADORAFUR HAPPY STAY.
                    </li>

                    <!-- <h6>Aggressive Pets Pollicy</h6> -->
                    <li>
                        Pets must be sociable to be allowed to stay with us.
                        Some pets may have aggressive tendencies if triggered, despite being able to socialize.
                        If your pet has any history of aggression such as food, territorial, possessive aggression, or if they don't want to be touched in a certain body part, please inform us so we may cater to their behavior needs.
                        As much as possible we would love to avoid using restricting instruments to pets. However, if the need arise we may isolate, crate, leash or muzzle an aggressive pet.
                        In any case, we reserve the right to refuse any pet that are hostile, aggressive and appear to be ill for everyone's safety.
                    </li>

                    <!-- <h6>Emergency Vet Care</h6> -->
                    <li>
                        Please be aware that we strive to avoid any accidents during their stay.
                        Pets can be unpredictable and injuries, illness or escaping may occur.
                        Minor injuries from nicks from clippers during grooming or rough play may result if your pet does not respond to the handler to behave properly during their stay.
                        All pet owners are required to accept these and other risks as a condition of their pet's participation in our services at Adorafur Happy Stay.
                    </li>

                    <!-- <h6>Ownership & Liability</h6> -->
                    <li>
                        Adorafur Happy Stay will not be held responsible for any sickness, injury or death caused by the pet to itself during grooming,
                        from pre-existing health conditions, natural disasters, or any illness a pet acquires due to non-vaccination or expired vaccines.
                    </li>

                    <!-- <h6>Non-Payment & Abandonment Policy</h6> -->
                    <li>
                        I agree to hold Adorafur Happy Stay harmless from any claims for damage, all defense costs, fees and business losses arising resulting from any claims to be made against Adorafur Happy Stay
                        for which its agents or employees are not ultimately held to be legally responsible.
                    </li>

                    <!-- <h6>Owner Responsibilities</h6> -->
                    <li> I certify that my pet has never unduly harmed or threatened anyone or any other pets.</li>
                    <li> I expressly agree to be held responsible for any damage to property (i.e. kennels, fencing, walls, flooring etc.) caused by my pet.</li>
                    <li> I expressly agree to be held responsible for medical costs for any human injury caused by my pet. </li>

                    <!-- <h6>Pet Health & Medical Disclosures</h6> -->
                    <li>The Owner understands that it is possible for us to discover a pet's illness during their stay with us such as arthritis, cysts,
                        cancer or any health problems old age brings for senior dogs.</li>

                    These conditions take time to develop and could be discovered during their stay.</li>

                    In that case, we will notify you immediately if something feels off with your pet and we would take them to the vet to get a diagnosis and proper treatment,
                    costs shall be shouldered by the owner. We understand how stressful and worrisome this is if this may happen to your pet.
                    Rest assured we will give them the care they need and provide the best comfort for them as much as possible. We will send you daily updates, vet's advice and etc.

                    <li>
                        Your pet's safety and well being is our absolute #1 priority.
                    </li>

                    <li>
                        Should the owner leave intentionally their pet in ADORAFUR HAPPY STAY without giving any communication for more than 1 week,
                        ADORAFUR HAPPY STAY reserves the right to hold the pet as a security for non-payment of the services and may sell and alienate the same,
                                                            without the consent of the owner, to a third party to satisfy any claims it may have against the customer.
                                                            Otherwise, Adorafur Happy Stay shall have the dogs/ cats adopted or endorse them to the necessary dog impounding station as deemed necessary
                                                        </li>

                                                    </ul>

                                                    Adorafur Happy Stay holds the highest standards to ensure that your pet is handled with respect and cared for properly.
                                                    It is extremely important to us that you know when your pet is under our supervision, Adorafur Happy Stay will provide them with the best care we can provide,
                                                    meeting the high expectations that we personally have for our own pets when under the supervision of another person.
                                                    We recognize and respect that all pets are living beings who have feelings and experience emotion. We value that you have entrusted your pet to us to provide our services to them.

                                                    <hr>

                                                    <strong>Conforme: </strong>

                                                    <p>
                                                        By submitting this agreement form, I, the Owner, acknowledge represent that I have made full disclosure and have read, understand and accept the terms and conditions stated in this agreement.
                                                        I acknowledge all of the statements above and understand and agree to release Adorafur Happy Stay and its employees from any and all liabilities, expenses, and costs (including veterinary and legal fees)
                                                        resulting from any service provided, or unintentional injury to my pet while under their care or afterwards. I acknowledge this agreement shall be effective and binding on both parties.
                                                        I also agree to follow the health and safety protocols of Adorafur Happy Stay.
                                                    </p>

                                                    <p>
                                                        <input type="checkbox" id="waiverForm-checkbox1" name="agree" value="1">
                                                        I hereby grant Adorafur Happy Stay  and its care takers permission to board and care for my pet
                                                    </p>
                                                    <p>
                                                        <input type="checkbox" id="waiverForm-checkbox2" name="agree" value="1">
                                                        I have read and agree with the  Liability Release and Waiver Form
                                                    </p>
                                                </div>

                                                <div class="modal-footer" id="waiverForm-footer">
                                                    <button type="button" class="btn btn-primary" id="complete-booking">Complete Booking</button>
                                                    
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /.main-container -->
            </div><!-- /.main -->

            <script>
                $(document).ready(function() {
                    // Initially hide pet information and headings
                    $(".pet-information-dog, .pet-information-cat").hide();
                    $(".pet-info h3, .pet-info h6").hide();

                    // Handle pet selection from dropdown menu
                    $("#petSelectionMenu + .dropdown-menu .dropdown-item").click(function() {
                        var selectedPet = $(this).text();
                        $("#petSelectionMenu").text(selectedPet);
                        $(".pet-information-dog, .pet-information-cat").hide();
                        if (selectedPet === "Dog") $(".pet-information-dog").fadeIn();
                        else if (selectedPet === "Cat") $(".pet-information-cat").fadeIn();
                    });

                    let selectedPet = null;

                    // Handle hover effect for pet info
                    $(".pet-info").hover(
                        function() {
                            $(this).find("h3, h6").fadeIn();
                        },
                        function() {
                            if (!$(this).hasClass("selected")) $(this).find("h3, h6").fadeOut();
                        }
                    );

                    // Handle click event for pet info
                    $(".pet-info").click(function() {
                        const img = $(this).find("img");
                        if (selectedPet === this) {
                            $(this).removeClass("selected");
                            swapImage(img);
                            $(this).find("h3, h6").fadeOut();
                            selectedPet = null;
                        } else {
                            if (selectedPet) {
                                swapImage($(selectedPet).find("img"));
                                $(selectedPet).removeClass("selected");
                                $(selectedPet).find("h3, h6").fadeOut();
                            }
                            $(this).addClass("selected");
                            swapImage(img);
                            $(this).find("h3, h6").fadeIn();
                            selectedPet = this;
                        }
                    });

                    // Function to swap images
                    function swapImage(img) {
                        let tempSrc = img.attr("src");
                        img.attr("src", img.attr("data-selected-src"));
                        img.attr("data-selected-src", tempSrc);
                    }

                    // Handle check-in and check-out time selection
                    $(".check-in-time").click(function() {
                        $("#checkInMenu").text($(this).text());
                    });
                    $(".check-out-time").click(function() {
                        $("#checkOutMenu").text($(this).text());
                    });

                    // Handle Book button click
                    $(".book").click(function() {
                        $(".main-schedule-options").fadeOut(function() {
                            $(".book-1").fadeIn();
                        });
                    });

                    // Ensure book-1 is initially hidden
                    $(".book-1").hide();
                });
            </script>

            <script>
            $(document).ready(function() {
                // Initially disable calendar, time selection, and book button
                $(".calendar").addClass("disabled-section");
                $(".checkin-out").addClass("disabled-section");
                $(".book").addClass("disabled-section");

                // Pet selection logic
                $("#petSelectionMenu + .dropdown-menu .dropdown-item").click(function() {
                    var selectedPet = $(this).text();
                    $("#petSelectionMenu").text(selectedPet);
                    $(".pet-information-dog, .pet-information-cat").hide();
                    if (selectedPet === "Dog") $(".pet-information-dog").fadeIn();
                    else if (selectedPet === "Cat") $(".pet-information-cat").fadeIn();

                    // Enable calendar after pet selection
                    $(".calendar").removeClass("disabled-section");

                    // Store the selection via AJAX
                    $.ajax({
                        type: "POST",
                        url: window.location.href,
                        data: { pet_type: selectedPet },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                console.log(response.message);
                            }
                        }
                    });
                });

                // Calendar date selection
                $(document).on("click", ".days-grid .day:not(.disabled)", function() {
                    if ($(".calendar").hasClass("disabled-section")) return;

                    $(".days-grid .day").removeClass("selected");
                    $(this).addClass("selected");

                    // Enable time selection after date selection
                    $(".checkin-out").removeClass("disabled-section");

                    // Store the selected date via AJAX
                    var selectedDate = $(this).data("date");
                    $.ajax({
                        type: "POST",
                        url: window.location.href,
                        data: { selected_date: selectedDate },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                console.log(response.message);
                            }
                        }
                    });
                });

                // Time selection logic
                let checkInSelected = false;
                let checkOutSelected = false;

                $(".check-in-time").click(function() {
                    if ($(".checkin-out").hasClass("disabled-section")) return;

                    $("#checkInMenu").text($(this).text());
                    checkInSelected = true;
                    updateBookButton();

                    // Store check-in time
                    $.ajax({
                        type: "POST",
                        url: window.location.href,
                        data: { check_in_time: $(this).text() },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                console.log(response.message);
                            }
                        }
                    });
                });

                $(".check-out-time").click(function() {
                    if ($(".checkin-out").hasClass("disabled-section")) return;

                    $("#checkOutMenu").text($(this).text());
                    checkOutSelected = true;
                    updateBookButton();

                    // Store check-out time
                    $.ajax({
                        type: "POST",
                        url: window.location.href,
                        data: { check_out_time: $(this).text() },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                console.log(response.message);
                            }
                        }
                    });
                });

                function updateBookButton() {
                    if (checkInSelected && checkOutSelected) {
                        $(".book").removeClass("disabled-section");
                    }
                }

                // Initialize based on PHP variables
                <?php if ($petSelected): ?>
                    $(".calendar").removeClass("disabled-section");
                <?php endif; ?>

                <?php if ($dateSelected): ?>
                    $(".checkin-out").removeClass("disabled-section");
                <?php endif; ?>

                <?php if ($timeSelected): ?>
                    $(".book").removeClass("disabled-section");
                <?php endif; ?>
            });
            </script>

            <script>
                    $("#complete-booking").click(function() {
                    // Debug: Log the state of checkboxes
                    console.log("Checkbox 1 state:", $("#waiverForm-checkbox1").is(":checked"));
                    console.log("Checkbox 2 state:", $("#waiverForm-checkbox2").is(":checked"));

                    // Check if waiver checkboxes are checked
                    if (!$("#waiverForm-checkbox1").prop("checked") || !$("#waiverForm-checkbox2").prop("checked")) {
                        alert("You must agree to the terms and conditions to complete your booking.");
                        return;
                    }
                    
                    // Show processing notification
                    alert("Your booking is being processed. Please wait...");
                    
                    // Disable the button to prevent multiple submissions
                    $(this).prop('disabled', true).text('Processing...');
                    
                    // Get the payment form
                    var paymentForm = $("#petPaymentModal form");
                    var formData = new FormData(paymentForm[0]);
                    
                    $.ajax({
                        type: "POST",
                        url: "book-pet-daycare.php",
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert("Booking completed successfully!");
                                $("#waiverForm").modal("hide");
                                // Redirect to confirmation page or update UI
                                window.location.href = "booking-confirmation.php";
                            } else {
                                alert("Error: " + response.error);
                                // Re-enable the button if there's an error
                                $("#complete-booking").prop('disabled', false).text('Complete Booking');
                            }
                        },
                        error: function() {
                            alert("An error occurred while processing your payment.");
                            // Re-enable the button if there's an error
                            $("#complete-booking").prop('disabled', false).text('Complete Booking');
                        }
                    });
                });
            </script>

<script>
    $(document).ready(function () {
        <?php if ($petSelected): ?>
            $(".calendar").removeClass("disabled-section");
        <?php endif; ?>

        <?php if ($dateSelected): ?>
            $(".checkin-out").removeClass("disabled-section");
        <?php endif; ?>

        <?php if ($timeSelected): ?>
            $(".book").removeClass("disabled-section");
        <?php endif; ?>
    });
</script>

<script>
    $(document).ready(function() {
        // Store booking data globally
        window.bookingData = {
            pets: [], // Array to store multiple pets
            checkInDate: "",
            checkInTime: "",
            checkOutDate: "",
            checkOutTime: ""
        };

        // Function to update the booking summary in the payment modal
        function updateBookingSummary() {
            // Update pet details
            if (bookingData.pets.length > 0) {
                // If there's only one pet
                if (bookingData.pets.length === 1) {
                    const pet = bookingData.pets[0];
                    $('#summaryPetName').text(pet.name);
                    
                    // Update the pet details section
                    $('#petSummaryDetails').html(`
                        <div class="info-row"><span class="label">Breed:</span><span class="value">${pet.breed}</span></div>
                        <div class="info-row"><span class="label">Gender:</span><span class="value">${pet.gender}</span></div>
                        <div class="info-row"><span class="label">Age:</span><span class="value">${pet.age} years old</span></div>
                    `);
                } 
                // If there are multiple pets
                else {
                    // Update the pet name to show count
                    $('#summaryPetName').text(`${bookingData.pets.length} Pets`);
                    
                    // Create a list of all pets with their details
                    let petDetailsHtml = '';
                    bookingData.pets.forEach((pet, index) => {
                        petDetailsHtml += `
                            <div class="pet-summary-item">
                                <h4>${pet.name}</h4>
                                <div class="info-row"><span class="label">Breed:</span><span class="value">${pet.breed}</span></div>
                                <div class="info-row"><span class="label">Gender:</span><span class="value">${pet.gender}</span></div>
                                <div class="info-row"><span class="label">Age:</span><span class="value">${pet.age} years old</span></div>
                                ${index < bookingData.pets.length - 1 ? '<hr>' : ''}
                            </div>
                        `;
                    });
                    
                    // Update the pet details section
                    $('#petSummaryDetails').html(petDetailsHtml);
                }
            }

            // Update dates if available
            if (bookingData.checkInDate && bookingData.checkInTime) {
                $('#summaryCheckIn').text(`${bookingData.checkInDate}, ${bookingData.checkInTime}`);
            } else {
                // Set default values if not available
                if (!$('#summaryCheckIn').text()) {
                    $('#summaryCheckIn').text("Not selected");
                }
            }
            
            if (bookingData.checkOutDate && bookingData.checkOutTime) {
                $('#summaryCheckOut').text(`${bookingData.checkOutDate}, ${bookingData.checkOutTime}`);
            } else {
                // Set default values if not available
                if (!$('#summaryCheckOut').text()) {
                    $('#summaryCheckOut').text("Not selected");
                }
            }
            
            // Update total price
            calculateTotalPrice();
        }

        // Update pet details when a pet is selected from dropdown
        $(document).on('change', '.petSelect', function() {
            const selectedOption = $(this).find('option:selected');
            const petName = selectedOption.text();
            
            if (petName && petName !== "Choose Pet") {
                // Get pet details from the JSON
                const petDetails = JSON.parse($(this).val());
                
                // Create pet object
                const pet = {
                    name: petName,
                    breed: petDetails.pet_breed,
                    gender: petDetails.pet_gender,
                    age: petDetails.pet_age
                };
                
                // Check if this pet is already in the array
                const existingPetIndex = bookingData.pets.findIndex(p => p.name === petName);
                
                if (existingPetIndex >= 0) {
                    // Update existing pet
                    bookingData.pets[existingPetIndex] = pet;
                } else {
                    // Add new pet
                    bookingData.pets.push(pet);
                }
                
                // Update summary
                updateBookingSummary();
            }
        });

        // Initialize the payment modal when it's opened
        $('#petPaymentModal').on('show.bs.modal', function() {
            // Set default values for check-in and check-out if they're empty
            if (!bookingData.checkInDate || !bookingData.checkInTime) {
                // Try to get values from the UI
                const checkInDate = $('.days-grid .day.selected').first().data('date');
                const checkInTime = $('#checkInMenu').text();
                
                if (checkInDate && checkInTime && checkInTime !== "Choose Time") {
                    const formattedDate = new Date(checkInDate).toLocaleDateString('en-US', {
                        month: 'long',
                        day: 'numeric'
                    });
                    bookingData.checkInDate = formattedDate;
                    bookingData.checkInTime = checkInTime;
                } else {
                    $('#summaryCheckIn').text("Not selected");
                }
            }
            
            if (!bookingData.checkOutDate || !bookingData.checkOutTime) {
                // Try to get values from the UI
                const checkOutDate = $('.days-grid .day.selected').last().data('date');
                const checkOutTime = $('#checkOutMenu').text();
                
                if (checkOutDate && checkOutTime && checkOutTime !== "Choose Time") {
                    const formattedDate = new Date(checkOutDate).toLocaleDateString('en-US', {
                        month: 'long',
                        day: 'numeric'
                    });
                    bookingData.checkOutDate = formattedDate;
                    bookingData.checkOutTime = checkOutTime;
                } else {
                    $('#summaryCheckOut').text("Not selected");
                }
            }
            
            // If no pets are selected, try to get from the table
            if (bookingData.pets.length === 0) {
                $('.petSelect').each(function() {
                    const select = $(this);
                    const selectedOption = select.find('option:selected');
                    const petName = selectedOption.text();
                    
                    if (petName && petName !== "Choose Pet") {
                        try {
                            const petDetails = JSON.parse(select.val());
                            
                            // Create pet object
                            const pet = {
                                name: petName,
                                breed: petDetails.pet_breed,
                                gender: petDetails.pet_gender,
                                age: petDetails.pet_age
                            };
                            
                            // Add to pets array
                            bookingData.pets.push(pet);
                        } catch (e) {
                            console.error("Error parsing pet details:", e);
                        }
                    }
                });
            }
            
            // Update the summary with all available data
            updateBookingSummary();
        });

        // Update summary when payment modal is opened
        $('#petPaymentModal').on('shown.bs.modal', function() {
            updateBookingSummary();
        });
    });
</script>

</body>
</html>

