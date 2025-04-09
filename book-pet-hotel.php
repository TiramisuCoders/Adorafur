<?php
require_once 'connect.php'; // Include database connection

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['c_id']);
$customerInfo = null;

// Fetch customer info if logged in
if ($isLoggedIn) {
    try {
        $stmt = $conn->prepare("SELECT * FROM customer WHERE c_id = ?");
        $stmt->execute([$_SESSION['c_id']]);
        $customerInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle error silently
    }
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

    <link rel="icon" type="image/png" href="Header-Pics/logo.png">
    <style>
    .action-btn {
        background-color: #5a3e36;
        border: 1px solid #d4a373;
        color: white;
        border-radius: 4px;
        padding: 2px 8px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .action-btn:hover {
        background-color: #d4a373;
        color: white;
    }
    .current-day {
        color: #ccc;
        pointer-events: none;
        text-decoration: none !important;
    }
</style>
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
                        <div class="available-slot" id="align-1">
                            Available Slots
                            
                        </div>
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
                            <b><?php echo $isLoggedIn && $customerInfo ? htmlspecialchars($customerInfo['c_first_name'] . ' ' . $customerInfo['c_last_name']) : 'Client name'; ?></b><br>
                            <span class="client-email"><?php echo $isLoggedIn && $customerInfo ? htmlspecialchars($customerInfo['c_email']) : 'Client Email'; ?></span>
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
                                                <!-- Pet options will be loaded via AJAX -->
                                            </select>
                                        </td>
                                        <td data-label="Breed"></td>
                                        <td data-label="Age"></td>
                                        <td data-label="Gender"></td>
                                        <td data-label="Size"></td>
                                        <td data-label="Price">₱0.00</td>
                                        <td><button type="button" onclick="addPetRow()" class="action-btn">(Add)</button></td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="lower-section">
                                <button type="button" class="btn" id="regPet" data-toggle="modal" data-target="#petRegistrationModal">
                                    <h6 class="regnewpet" style="font-weight: 600;">Need to register new pet?</h6>
                                </button>

                                <div class="modal fade" id="petRegistrationModal" data-backdrop="static" data-keyboard="false" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header d-flex justify-content-center align-items-center" id="modalHeader">
                                                <h1 class="modal-title" id="modalTitle">Register Your Pet</h1>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body" id="modalBody">
                                                <!-- Pet registration form content -->
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
                                                                                <input type="radio" name="pet_size" id="sizeRegularDog" value="Regular">
                                                                                <label for="sizeRegularDog">Regular Dog</label>
                                                                            </div>
                                                                            <div>
                                                                                <input type="radio" name="pet_size" id="sizeLargeDog" value="Large">
                                                                                <label for="sizeLargeDog">Large Dog</label>
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
                                
                                <!-- Payment Button -->
                                <div class="proctopayment">
                                    <button type="button" class="btn payment-btn" id="proceedToPaymentBtn">
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
                                                                <h2 class="pet-name" id="summaryPetName">Your Pet</h2>
                                                                <div class="booking-dates">
                                                                    <p><strong>Check in:</strong> <span id="summaryCheckIn">Not selected</span></p>
                                                                    <p><strong>Check out:</strong> <span id="summaryCheckOut">Not selected</span></p>
                                                                </div>

                                                                <div class="info-grid">
                                                                    <div class="info-row"><span class="label">Service:</span><span class="value">Pet Hotel</span></div>
                                                                    <div id="petSummaryDetails">
                                                                        <!-- Pet details will be inserted here dynamically -->
                                                                    </div>
                                                                    <div class="info-row"><span class="label">Owner:</span><span class="value">
                                                                        <?php echo $isLoggedIn && $customerInfo ? htmlspecialchars($customerInfo['c_first_name'] . ' ' . $customerInfo['c_last_name']) : 'Not logged in'; ?>
                                                                    </span></div>
                                                                    <div class="info-row"><span class="label">Amount to pay:</span><span class="value" id="summaryTotalAmount">₱ 0.00</span></div>
                                                                    <div class="info-row"><span class="label">Remaining Balance:</span><span class="value" id="summaryRemainingBalance">₱ 0.00</span></div>
                                                                </div>

                                                                <form method="POST" enctype="multipart/form-data" id="paymentForm">
                                                                    <input type="hidden" name="visible_pets" id="visiblePetsData" value="">
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
                                    
                                    <!-- Waiver Modal -->
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
                                                    <!-- Waiver content -->
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

                                                        <!-- Additional waiver content -->
                                                        <li>
                                                            Pets must be sociable to be allowed to stay with us.
                                                            Some pets may have aggressive tendencies if triggered, despite being able to socialize.
                                                            If your pet has any history of aggression such as food, territorial, possessive aggression, or if they don't want to be touched in a certain body part, please inform us so we may cater to their behavior needs.
                                                            As much as possible we would love to avoid using restricting instruments to pets. However, if the need arise we may isolate, crate, leash or muzzle an aggressive pet.
                                                            In any case, we reserve the right to refuse any pet that are hostile, aggressive and appear to be ill for everyone's safety.
                                                        </li>

                                                        <li>
                                                            Please be aware that we strive to avoid any accidents during their stay.
                                                            Pets can be unpredictable and injuries, illness or escaping may occur.
                                                            Minor injuries from nicks from clippers during grooming or rough play may result if your pet does not respond to the handler to behave properly during their stay.
                                                            All pet owners are required to accept these and other risks as a condition of their pet's participation in our services at Adorafur Happy Stay.
                                                        </li>

                                                        <li>
                                                            Adorafur Happy Stay will not be held responsible for any sickness, injury or death caused by the pet to itself during grooming,
                                                            from pre-existing health conditions, natural disasters, or any illness a pet acquires due to non-vaccination or expired vaccines.
                                                        </li>

                                                        <li>
                                                            I agree to hold Adorafur Happy Stay harmless from any claims for damage, all defense costs, fees and business losses arising resulting from any claims to be made against Adorafur Happy Stay
                                                            for which its agents or employees are not ultimately held to be legally responsible.
                                                        </li>

                                                        <li>I certify that my pet has never unduly harmed or threatened anyone or any other pets.</li>
                                                        <li>I expressly agree to be held responsible for any damage to property (i.e. kennels, fencing, walls, flooring etc.) caused by my pet.</li>
                                                        <li>I expressly agree to be held responsible for medical costs for any human injury caused by my pet.</li>

                                                        <li>The Owner understands that it is possible for us to discover a pet's illness during their stay with us such as arthritis, cysts,
                                                            cancer or any health problems old age brings for senior dogs.</li>

                                                        <li>
                                                            These conditions take time to develop and could be discovered during their stay.
                                                            In that case, we will notify you immediately if something feels off with your pet and we would take them to the vet to get a diagnosis and proper treatment,
                                                            costs shall be shouldered by the owner. We understand how stressful and worrisome this is if this may happen to your pet.
                                                            Rest assured we will give them the care they need and provide the best comfort for them as much as possible. We will send you daily updates, vet's advice and etc.
                                                        </li>

                                                        <li>
                                                            Your pet's safety and well being is our absolute #1 priority.
                                                        </li>

                                                        <li>
                                                            Should the owner leave intentionally their pet in ADORAFUR HAPPY STAY without giving any communication for more than 1 week,
                                                            ADORAFUR HAPPY STAY reserves the right to hold the pet as a security for non-payment of the services and may sell and alienate the same, without the consent of the owner, to a third party to satisfy any claims it may have against the customer. Otherwise, Adorafur Happy Stay shall have the dogs/ cats adopted or endorse them to the necessary dog impounding station as deemed necessary
                                                        </li>
                                                    </ul>

                                                    <p>
                                                        Adorafur Happy Stay holds the highest standards to ensure that your pet is handled with respect and cared for properly.
                                                        It is extremely important to us that you know when your pet is under our supervision, Adorafur Happy Stay will provide them with the best care we can provide,
                                                        meeting the high expectations that we personally have for our own pets when under the supervision of another person.
                                                        We recognize and respect that all pets are living beings who have feelings and experience emotion. We value that you have entrusted your pet to us to provide our services to them.
                                                    </p>

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
                                                        I hereby grant Adorafur Happy Stay and its care takers permission to board and care for my pet
                                                    </p>
                                                    <p>
                                                        <input type="checkbox" id="waiverForm-checkbox2" name="agree" value="1">
                                                        I have read and agree with the Liability Release and Waiver Form
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
                </div>
            </div>
        </div>
    </div>

    <!-- Pass PHP variables to JavaScript -->
    <script>
        // Set login status and customer ID for JavaScript
        window.isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        window.customerId = <?php echo $isLoggedIn ? $_SESSION['c_id'] : 0; ?>;
    </script>
    
    <!-- Include the external JavaScript file -->
    <script src="books.js"></script>
</body>
</html>

