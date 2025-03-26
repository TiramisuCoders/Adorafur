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
                            <?php if (!$isLoggedIn): ?>
                                <div class="login-warning">Please log in to continue booking</div>
                            <?php endif; ?>
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
                                        <td><button type="button" onclick="addPetRow()">+</button></td>
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
                                    <button type="button" class="btn payment-btn" data-toggle="modal" data-target="#petPaymentModal">
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

    <script>
    // Initialize booking data object
    window.bookingData = {
        pets: [],
        checkInDate: "",
        checkInTime: "",
        checkOutDate: "",
        checkOutTime: ""
    };

    // Calendar functionality
    $(document).ready(function() {
        // Initialize variables
        const currentDate = new Date();
        let currentMonth = currentDate.getMonth();
        let currentYear = currentDate.getFullYear();
        const months = ["JANUARY", "FEBRUARY", "MARCH", "APRIL", "MAY", "JUNE", "JULY", "AUGUST", "SEPTEMBER", "OCTOBER", "NOVEMBER", "DECEMBER"];
        let selectedDates = {
            checkIn: null,
            checkOut: null
        };

        // Check if user is logged in
        const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
        
        // Initially hide pet information sections
        $(".pet-information-dog, .pet-information-cat").hide();
        
        // Initially hide booking details section
        $(".book-1").hide();
        
        // Disable sections if not logged in
        if (!isLoggedIn) {
            $(".calendar").addClass("disabled-section");
            $(".checkin-out").addClass("disabled-section");
            $(".book").addClass("disabled-section");
        }

        // Render calendar
        function renderCalendar() {
            // Update month and year display
            $("#month").text(months[currentMonth]);
            $("#year").text(currentYear);
            
            // Get first day of month and total days
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            
            // Clear previous calendar days
            $("#days").empty();
            
            // Add empty cells for days before the first day of month
            for (let i = 0; i < firstDay; i++) {
                $("#days").append('<div class="day empty"></div>');
            }
            
            // Create current date for comparison
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            // Add calendar days
            for (let day = 1; day <= daysInMonth; day++) {
                const thisDate = new Date(currentYear, currentMonth, day);
                const formattedDate = thisDate.toISOString().split('T')[0];
                
                let dayClass = "day";
                if (thisDate < today) {
                    dayClass += " past-day";
                }
                
                if (thisDate.getTime() === today.getTime()) {
                    dayClass += " current-day";
                }
                
                // Check if this date is selected
                if (selectedDates.checkIn && formattedDate === selectedDates.checkIn.toISOString().split('T')[0]) {
                    dayClass += " selected-date";
                }
                if (selectedDates.checkOut && formattedDate === selectedDates.checkOut.toISOString().split('T')[0]) {
                    dayClass += " selected-date";
                }
                
                // Add date to calendar
                const dayElement = $(`<div class="${dayClass}" data-date="${formattedDate}">${day}</div>`);
                
                // Add click handler only for future dates
                if (!dayClass.includes("past-day")) {
                    dayElement.on("click", function() {
                        handleDateClick(thisDate, $(this));
                    });
                }
                
                $("#days").append(dayElement);
            }
            
            // Highlight date range if both dates are selected
            highlightDateRange();
        }
        
        // Handle date click
        function handleDateClick(date, element) {
            if (!selectedDates.checkIn || (selectedDates.checkIn && selectedDates.checkOut)) {
                // Start new selection
                clearDateSelection();
                selectedDates.checkIn = date;
                element.addClass("selected-date");
                
                // Update booking data
                window.bookingData.checkInDate = date.toLocaleDateString('en-US', {
                    month: 'long',
                    day: 'numeric'
                });
                window.bookingData.checkOutDate = window.bookingData.checkInDate;
            } else {
                // Complete selection
                if (date > selectedDates.checkIn) {
                    selectedDates.checkOut = date;
                    
                    // Clear all highlights first
                    $(".day").removeClass("selected-date highlighted");
                    
                    // Find and highlight the first date
                    $(`.day[data-date="${selectedDates.checkIn.toISOString().split('T')[0]}"]`).addClass("selected-date");
                    
                    // Highlight the last date
                    element.addClass("selected-date");
                    
                    // Update booking data
                    window.bookingData.checkOutDate = date.toLocaleDateString('en-US', {
                        month: 'long',
                        day: 'numeric'
                    });
                    
                    // Highlight dates in between
                    highlightDateRange();
                }
            }
            
            // Enable time selection after date selection
            $(".checkin-out").removeClass("disabled-section");
            
            // Update summary
            updateBookingSummary();
        }
        
        // Clear date selection
        function clearDateSelection() {
            selectedDates.checkIn = null;
            selectedDates.checkOut = null;
            $(".day").removeClass("selected-date highlighted");
        }
        
        // Highlight date range
        function highlightDateRange() {
            if (!selectedDates.checkIn || !selectedDates.checkOut) return;
            
            $(".day").each(function() {
                const dateStr = $(this).attr("data-date");
                if (!dateStr) return;
                
                const date = new Date(dateStr);
                
                if (date > selectedDates.checkIn && date < selectedDates.checkOut) {
                    $(this).addClass("highlighted");
                }
            });
        }
        
        // Initialize calendar
        renderCalendar();
        
        // Set up month navigation
        $("#prevMonth").on("click", function() {
            const now = new Date();
            const prevMonth = new Date(currentYear, currentMonth - 1, 1);
            
            // Don't allow navigating to past months
            if (prevMonth.getFullYear() < now.getFullYear() || 
                (prevMonth.getFullYear() === now.getFullYear() && prevMonth.getMonth() < now.getMonth())) {
                return;
            }
            
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            renderCalendar();
        });
        
        $("#nextMonth").on("click", function() {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            renderCalendar();
        });
        
        // Pet selection logic
        $("#petSelectionMenu + .dropdown-menu .dropdown-item").click(function() {
            var selectedPet = $(this).text();
            $("#petSelectionMenu").text(selectedPet);
            $(".pet-information-dog, .pet-information-cat").hide();
            
            if (selectedPet === "Dog") {
                $(".pet-information-dog").fadeIn();
            } else if (selectedPet === "Cat") {
                $(".pet-information-cat").fadeIn();
            }
            
            // Enable calendar after pet selection
            $(".calendar").removeClass("disabled-section");
        });
        
        // Handle pet info selection
        let selectedPet = null;
        
        $(".pet-info").hover(
            function() {
                $(this).find("h3, h6").fadeIn();
            },
            function() {
                if (!$(this).hasClass("selected")) {
                    $(this).find("h3, h6").fadeOut();
                }
            }
        );
        
        $(".pet-info").click(function() {
            const img = $(this).find("img");
            const petType = $(this).find("h3").text();
            const petPriceMatch = $(this).find("h6").text().match(/₱\s*(\d+)/);
            const petPrice = petPriceMatch ? Number.parseInt(petPriceMatch[1]) : 0;
            
            if (selectedPet === this) {
                // Deselect
                $(this).removeClass("selected");
                swapImage(img);
                $(this).find("h3, h6").fadeOut();
                selectedPet = null;
                
                // Remove from booking data
                const existingPetIndex = window.bookingData.pets.findIndex(p => p.name === petType);
                if (existingPetIndex >= 0) {
                    window.bookingData.pets.splice(existingPetIndex, 1);
                }
            } else {
                // Deselect previous
                if (selectedPet) {
                    $(selectedPet).removeClass("selected");
                    swapImage($(selectedPet).find("img"));
                    $(selectedPet).find("h3, h6").fadeOut();
                    
                    // Remove previous pet from booking data
                    const prevPetType = $(selectedPet).find("h3").text();
                    const existingPetIndex = window.bookingData.pets.findIndex(p => p.name === prevPetType);
                    if (existingPetIndex >= 0) {
                        window.bookingData.pets.splice(existingPetIndex, 1);
                    }
                }
                
                // Select new
                $(this).addClass("selected");
                swapImage(img);
                $(this).find("h3, h6").fadeIn();
                selectedPet = this;
                
                // Add to booking data
                window.bookingData.pets.push({
                    name: petType,
                    size: petType,
                    price: petPrice
                });
            }
            
            // Update summary
            updateBookingSummary();
        });
        
        // Function to swap images
        function swapImage(img) {
            let tempSrc = img.attr("src");
            img.attr("src", img.attr("data-selected-src"));
            img.attr("data-selected-src", tempSrc);
        }
        
        // Handle check-in and check-out time selection
        $(".check-in-time").click(function() {
            const selectedTime = $(this).text();
            $("#checkInMenu").text(selectedTime);
            window.bookingData.checkInTime = selectedTime;
            updateBookingSummary();
            checkTimeSelection();
        });
        
        $(".check-out-time").click(function() {
            const selectedTime = $(this).text();
            $("#checkOutMenu").text(selectedTime);
            window.bookingData.checkOutTime = selectedTime;
            updateBookingSummary();
            checkTimeSelection();
        });
        
        // Check if both times are selected
        function checkTimeSelection() {
            if (window.bookingData.checkInTime && window.bookingData.checkOutTime) {
                $(".book").removeClass("disabled-section");
            }
        }
        
        // Handle Book button click
        $(".book").click(function() {
            if (!isLoggedIn) {
                alert("Please log in to continue booking.");
                return;
            }
            
            $(".main-schedule-options").fadeOut(function() {
                $(".book-1").fadeIn();
                
                // Fetch customer pets for the dropdown
                fetchCustomerPets();
            });
        });
        
        // Fetch customer pets
        function fetchCustomerPets() {
            $.ajax({
                type: "POST",
                url: "get-user-pets.php",
                data: { c_id: <?php echo $isLoggedIn ? $_SESSION['c_id'] : 0; ?> },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.pets.length > 0) {
                        // Clear existing options except the first one
                        $(".petSelect").find("option:not(:first)").remove();
                        
                        // Add the customer's pets to the dropdown
                        response.pets.forEach(function(pet) {
                            const petData = {
                                pet_id: pet.pet_id,
                                pet_breed: pet.pet_breed,
                                pet_age: pet.pet_age,
                                pet_gender: pet.pet_gender,
                                pet_size: pet.pet_size
                            };
                            
                            const option = $("<option>")
                                .val(JSON.stringify(petData))
                                .text(pet.pet_name);
                                
                            $(".petSelect").append(option);
                        });
                    } else {
                        console.log("No pets found or error:", response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                }
            });
        }
        
        // Update pet details when selected from dropdown
        window.updatePetDetails = function(selectElement) {
            const selectedOption = $(selectElement).find("option:selected");
            const petName = selectedOption.text();
            
            if (petName && petName !== "Choose Pet") {
                try {
                    // Get pet details from the JSON
                    const petDetails = JSON.parse($(selectElement).val());
                    const row = $(selectElement).closest("tr");
                    
                    // Update row cells
                    row.find("[data-label='Breed']").text(petDetails.pet_breed || "");
                    row.find("[data-label='Age']").text(petDetails.pet_age ? petDetails.pet_age + " years" : "");
                    row.find("[data-label='Gender']").text(petDetails.pet_gender || "");
                    row.find("[data-label='Size']").text(petDetails.pet_size || "");
                    
                    // Set price based on pet size
                    let price = 0;
                    switch(petDetails.pet_size) {
                        case 'Cat':
                            price = 500;
                            break;
                        case 'Small':
                            price = 700;
                            break;
                        case 'Regular':
                            price = 800;
                            break;
                        case 'Large':
                            price = 900;
                            break;
                    }
                    
                    row.find("[data-label='Price']").text(`₱${price.toFixed(2)}`);
                    
                    // Create pet object
                    const pet = {
                        name: petName,
                        breed: petDetails.pet_breed,
                        gender: petDetails.pet_gender,
                        age: petDetails.pet_age,
                        size: petDetails.pet_size,
                        price: price
                    };
                    
                    // Check if this pet is already in the array
                    const existingPetIndex = window.bookingData.pets.findIndex(p => p.name === petName);
                    
                    if (existingPetIndex >= 0) {
                        // Update existing pet
                        window.bookingData.pets[existingPetIndex] = pet;
                    } else {
                        // Add new pet
                        window.bookingData.pets.push(pet);
                    }
                    
                    // Update summary
                    calculateTotalPrice();
                    updateBookingSummary();
                } catch (e) {
                    console.error("Error parsing pet details:", e);
                }
            } else {
                // Clear row if "Choose Pet" is selected
                const row = $(selectElement).closest("tr");
                row.find("[data-label='Breed']").text("");
                row.find("[data-label='Age']").text("");
                row.find("[data-label='Gender']").text("");
                row.find("[data-label='Size']").text("");
                row.find("[data-label='Price']").text("₱0.00");
            }
        };
        
        // Add new pet row
        window.addPetRow = function() {
            const newRow = `
                <tr>
                    <td data-label="Name">
                        <select class="petSelect" onchange="updatePetDetails(this)">
                            <option value="">Choose Pet</option>
                            ${$(".petSelect").first().find("option:not(:first)").clone().prop('outerHTML')}
                        </select>
                    </td>
                    <td data-label="Breed"></td>
                    <td data-label="Age"></td>
                    <td data-label="Gender"></td>
                    <td data-label="Size"></td>
                    <td data-label="Price">₱0.00</td>
                    <td><button type="button" onclick="removePetRow(this)">-</button></td>
                </tr>
            `;
            
            $("#petTableBody").append(newRow);
        };
        
        // Remove pet row
        window.removePetRow = function(button) {
            const row = $(button).closest("tr");
            const petName = row.find(".petSelect option:selected").text();
            
            // Remove from booking data if exists
            if (petName && petName !== "Choose Pet") {
                const existingPetIndex = window.bookingData.pets.findIndex(p => p.name === petName);
                if (existingPetIndex >= 0) {
                    window.bookingData.pets.splice(existingPetIndex, 1);
                }
            }
            
            // Remove row
            row.remove();
            
            // Update total price
            calculateTotalPrice();
            updateBookingSummary();
        };
        
        // Calculate total price
        window.calculateTotalPrice = function() {
            let totalPrice = 0;
            
            // Calculate number of days between check-in and check-out
            let numberOfDays = 1; // Default to 1 day
            
            if (selectedDates.checkIn && selectedDates.checkOut) {
                // Calculate the difference in days
                const checkIn = new Date(selectedDates.checkIn);
                const checkOut = new Date(selectedDates.checkOut);
                const timeDiff = Math.abs(checkOut.getTime() - checkIn.getTime());
                numberOfDays = Math.ceil(timeDiff / (1000 * 3600 * 24)) || 1;
            }
            
            // Calculate price based on selected pets
            if (window.bookingData.pets.length > 0) {
                window.bookingData.pets.forEach(function(pet) {
                    // Multiply pet price by number of days
                    totalPrice += (pet.price || 0) * numberOfDays;
                });
            }
            
            // Update the total price display
            $("#summaryTotalAmount").text(`₱ ${totalPrice.toFixed(2)}`);
            $("#summaryRemainingBalance").text(`₱ ${totalPrice.toFixed(2)}`);
            
            return totalPrice;
        };
        
        // Update booking summary
        function updateBookingSummary() {
            // Update pet details in summary
            if (window.bookingData.pets.length > 0) {
                // If there's only one pet
                if (window.bookingData.pets.length === 1) {
                    const pet = window.bookingData.pets[0];
                    $('#summaryPetName').text(pet.name);
                    
                    // Update the pet details section
                    $('#petSummaryDetails').html(`
                        <div class="info-row"><span class="label">Breed:</span><span class="value">${pet.breed || 'Not specified'}</span></div>
                        <div class="info-row"><span class="label">Gender:</span><span class="value">${pet.gender || 'Not specified'}</span></div>
                        <div class="info-row"><span class="label">Age:</span><span class="value">${pet.age ? pet.age + ' years old' : 'Not specified'}</span></div>
                    `);
                } 
                // If there are multiple pets
                else {
                    // Update the pet name to show count
                    $('#summaryPetName').text(`${window.bookingData.pets.length} Pets`);
                    
                    // Create a list of all pets with their details
                    let petDetailsHtml = '';
                    window.bookingData.pets.forEach((pet, index) => {
                        petDetailsHtml += `
                            <div class="pet-summary-item">
                                <h4>${pet.name}</h4>
                                <div class="info-row"><span class="label">Breed:</span><span class="value">${pet.breed || 'Not specified'}</span></div>
                                <div class="info-row"><span class="label">Gender:</span><span class="value">${pet.gender || 'Not specified'}</span></div>
                                <div class="info-row"><span class="label">Age:</span><span class="value">${pet.age ? pet.age + ' years old' : 'Not specified'}</span></div>
                                ${index < window.bookingData.pets.length - 1 ? '<hr>' : ''}
                            </div>
                        `;
                    });
                    
                    // Update the pet details section
                    $('#petSummaryDetails').html(petDetailsHtml);
                }
            }
            
            // Update dates if available
            if (window.bookingData.checkInDate) {
                if (window.bookingData.checkInTime) {
                    $('#summaryCheckIn').text(`${window.bookingData.checkInDate}, ${window.bookingData.checkInTime}`);
                } else {
                    $('#summaryCheckIn').text(window.bookingData.checkInDate);
                }
            }
            
            if (window.bookingData.checkOutDate) {
                if (window.bookingData.checkOutTime) {
                    $('#summaryCheckOut').text(`${window.bookingData.checkOutDate}, ${window.bookingData.checkOutTime}`);
                } else {
                    $('#summaryCheckOut').text(window.bookingData.checkOutDate);
                }
            }
            
            // Update total price
            calculateTotalPrice();
        }
        
        // Payment method handling
        $(document).on('change', 'input[name="payment_method"]', function() {
            const selectedPayment = $(this).val();
            
            if (selectedPayment === "GCash") {
                $("#gcashQR").show();
                $("#mayaQR").hide();
            } else {
                $("#gcashQR").hide();
                $("#mayaQR").show();
            }
        });
        
        // Payment form validation
        function validatePaymentForm() {
            const referenceNo = $('input[name="reference_no"]').val().trim();
            const paymentProof = $('input[name="payment_proof"]').prop('files').length;
            
            // Enable button only if both fields are filled
            if (referenceNo && paymentProof > 0) {
                $("#proceed-to-waiver").prop("disabled", false);
            } else {
                $("#proceed-to-waiver").prop("disabled", true);
            }
        }
        
        // Attach validation handlers
        $(document).on('input', 'input[name="reference_no"]', validatePaymentForm);
        $(document).on('change', 'input[name="payment_proof"]', validatePaymentForm);
        
        // Initialize payment modal
        $("#petPaymentModal").on("show.bs.modal", function() {
            // Validate pet selection
            if (window.bookingData.pets.length === 0) {
                alert("Please select at least one pet before proceeding to payment.");
                return false;
            }
            
            // Reset form
            $("#paymentForm")[0].reset();
            $("#proceed-to-waiver").prop("disabled", true);
            
            // Show default QR code (Maya)
            $("#gcashQR").hide();
            $("#mayaQR").show();
            
            // Update summary
            updateBookingSummary();
        });
        
        // Handle proceed to waiver button
        $("#proceed-to-waiver").on("click", function() {
            $("#petPaymentModal").modal("hide");
            setTimeout(function() {
                $("#waiverForm").modal("show");
            }, 500);
        });
        
        // Handle complete booking button
        $("#complete-booking").on("click", function() {
            // Check if waiver checkboxes are checked
            if (!$("#waiverForm-checkbox1").prop("checked") || !$("#waiverForm-checkbox2").prop("checked")) {
                alert("You must agree to the terms and conditions to complete your booking.");
                return;
            }
            
            // Show processing notification
            alert("Your booking is being processed. Please wait...");
            
            // Disable the button to prevent multiple submissions
            $(this).prop("disabled", true).text("Processing...");
            
            // Get the payment form data
            var formData = new FormData($("#paymentForm")[0]);
            formData.append("complete_booking", "true");
            
            // Add booking data to form
            formData.append("booking_data", JSON.stringify(window.bookingData));
            
            $.ajax({
                type: "POST",
                url: "process-booking.php",
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json",
                success: function(response) {
                    if (response.success) {
                        alert("Booking completed successfully!");
                        $("#waiverForm").modal("hide");
                        // Redirect to confirmation page or refresh
                        window.location.href = "booking-confirmation.php";
                    } else {
                        alert("Error: " + (response.message || "Unknown error"));
                        // Re-enable the button if there's an error
                        $("#complete-booking").prop("disabled", false).text("Complete Booking");
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", error);
                    alert("An error occurred while processing your booking. Please try again later.");
                    // Re-enable the button if there's an error
                    $("#complete-booking").prop("disabled", false).text("Complete Booking");
                }
            });
        });
        
        // Handle payment button click
        $(".payment-btn").on("click", function(e) {
            // Check if a pet is selected
            if (window.bookingData.pets.length === 0) {
                e.preventDefault();
                alert("Please select at least one pet before proceeding to payment.");
                return;
            }
            
            // Check if dates are selected
            if (!window.bookingData.checkInDate || !window.bookingData.checkOutDate) {
                e.preventDefault();
                alert("Please select check-in and check-out dates.");
                return;
            }
            
            // If all validations pass, show payment modal
            $("#petPaymentModal").modal("show");
        });
    });
    </script>

    <!-- Additional PHP files for AJAX requests -->
    <?php
    // Create check-login.php
    $check_login_content = '<?php
    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    $isLoggedIn = isset($_SESSION["c_id"]);
    $response = [
        "isLoggedIn" => $isLoggedIn
    ];

    if ($isLoggedIn) {
        $response["c_id"] = $_SESSION["c_id"];
    }

    // Return JSON response
    header("Content-Type: application/json");
    echo json_encode($response);
    ?>';
    file_put_contents('check-login.php', $check_login_content);

    // Create get-user-pets.php
    $get_user_pets_content = '<?php
    require_once "connect.php"; // Include database connection

    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    $isLoggedIn = isset($_SESSION["c_id"]);
    if (!$isLoggedIn && !isset($_POST["c_id"])) {
        echo json_encode([
            "success" => false,
            "message" => "User not logged in"
        ]);
        exit;
    }

    // Get client ID from session or POST
    $clientId = isset($_POST["c_id"]) ? $_POST["c_id"] : $_SESSION["c_id"];

    try {
        $stmt = $conn->prepare("SELECT pet_id, pet_name, pet_breed, pet_age, pet_gender, pet_size 
                               FROM pet 
                               WHERE customer_id = :customer_id");
        $stmt->bindParam(":customer_id", $clientId, PDO::PARAM_INT);
        $stmt->execute();
        
        $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            "success" => true,
            "pets" => $pets
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
    ?>';
    file_put_contents('get-user-pets.php', $get_user_pets_content);

    // Create get-client-info.php
    $get_client_info_content = '<?php
    require_once "connect.php"; // Include database connection

    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    $isLoggedIn = isset($_SESSION["c_id"]);
    if (!$isLoggedIn) {
        echo json_encode([
            "success" => false,
            "message" => "User not logged in"
        ]);
        exit;
    }

    // Get client ID from session or POST
    $clientId = isset($_POST["c_id"]) ? $_POST["c_id"] : $_SESSION["c_id"];

    try {
        $stmt = $conn->prepare("SELECT c_first_name, c_last_name, c_email 
                               FROM customer 
                               WHERE c_id = :customer_id");
        $stmt->bindParam(":customer_id", $clientId, PDO::PARAM_INT);
        $stmt->execute();
        
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($customer) {
            echo json_encode([
                "success" => true,
                "client_name" => $customer["c_first_name"] . " " . $customer["c_last_name"],
                "client_email" => $customer["c_email"]
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Customer not found"
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
    ?>';
    file_put_contents('get-client-info.php', $get_client_info_content);

    // Create process-booking.php
    $process_booking_content = '<?php
    require_once "connect.php"; // Include database connection

    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    $isLoggedIn = isset($_SESSION["c_id"]);
    if (!$isLoggedIn) {
        echo json_encode([
            "success" => false,
            "message" => "User not logged in"
        ]);
        exit;
    }

    // Process booking completion
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["complete_booking"])) {
        $customerId = $_SESSION["c_id"];
        $bookingData = json_decode($_POST["booking_data"], true);
        $paymentMethod = $_POST["payment_method"];
        $referenceNo = $_POST["reference_no"];
        
        // Handle file upload for payment proof
        $paymentProofPath = "";
        if (isset($_FILES["payment_proof"]) && $_FILES["payment_proof"]["error"] == 0) {
            $targetDir = "uploads/payment_proofs/";
            
            // Create directory if it doesn\'t exist
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            $fileName = time() . "_" . basename($_FILES["payment_proof"]["name"]);
            $targetFilePath = $targetDir . $fileName;
            
            // Upload file
            if (move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $targetFilePath)) {
                $paymentProofPath = $targetFilePath;
            } else {
                echo json_encode([
                    "success" => false,
                    "message" => "Failed to upload payment proof"
                ]);
                exit;
            }
        }
        
        try {
            // Start transaction
            $conn->beginTransaction();
            
            // 1. Create booking record
            $stmt = $conn->prepare("INSERT INTO booking (customer_id, check_in_date, check_in_time, check_out_date, check_out_time, payment_method, reference_no, payment_proof, status, created_at) 
                                   VALUES (:customer_id, :check_in_date, :check_in_time, :check_out_date, :check_out_time, :payment_method, :reference_no, :payment_proof, \'confirmed\', NOW())");
            
            $checkInDate = date("Y-m-d", strtotime($bookingData["checkInDate"]));
            $checkOutDate = date("Y-m-d", strtotime($bookingData["checkOutDate"]));
            
            $stmt->bindParam(":customer_id", $customerId, PDO::PARAM_INT);
            $stmt->bindParam(":check_in_date", $checkInDate);
            $stmt->bindParam(":check_in_time", $bookingData["checkInTime"]);
            $stmt->bindParam(":check_out_date", $checkOutDate);
            $stmt->bindParam(":check_out_time", $bookingData["checkOutTime"]);
            $stmt->bindParam(":payment_method", $paymentMethod);
            $stmt->bindParam(":reference_no", $referenceNo);
            $stmt->bindParam(":payment_proof", $paymentProofPath);
            
            $stmt->execute();
            $bookingId = $conn->lastInsertId();
            
            // 2. Add booking details for each pet
            if (!empty($bookingData["pets"])) {
                $stmt = $conn->prepare("INSERT INTO booking_details (booking_id, pet_id, pet_name, pet_size, price) 
                                       VALUES (:booking_id, :pet_id, :pet_name, :pet_size, :price)");
                
                foreach ($bookingData["pets"] as $pet) {
                    // Get pet ID from name
                    $petStmt = $conn->prepare("SELECT pet_id FROM pet WHERE pet_name = :pet_name AND customer_id = :customer_id LIMIT 1");
                    $petStmt->bindParam(":pet_name", $pet["name"]);
                    $petStmt->bindParam(":customer_id", $customerId, PDO::PARAM_INT);
                    $petStmt->execute();
                    $petResult = $petStmt->fetch(PDO::FETCH_ASSOC);
                    
                    $petId = $petResult ? $petResult["pet_id"] : 0;
                    
                    $stmt->bindParam(":booking_id", $bookingId, PDO::PARAM_INT);
                    $stmt->bindParam(":pet_id", $petId, PDO::PARAM_INT);
                    $stmt->bindParam(":pet_name", $pet["name"]);
                    $stmt->bindParam(":pet_size", $pet["size"]);
                    $stmt->bindParam(":price", $pet["price"]);
                    
                    $stmt->execute();
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            // Return success response
            echo json_encode([
                "success" => true,
                "message" => "Booking completed successfully",
                "booking_id" => $bookingId
            ]);
            
        } catch (PDOException $e) {
            // Rollback transaction on error
            $conn->rollBack();
            
            echo json_encode([
                "success" => false,
                "message" => "Database error: " . $e->getMessage()
            ]);
        }
        
        exit;
    }

    // Default response for invalid requests
    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);
    ?>';
    file_put_contents('process-booking.php', $process_booking_content);
    ?>
</body>
</html>