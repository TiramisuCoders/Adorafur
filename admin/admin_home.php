<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
  // Redirect to login page if not logged in
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
    $stmt = $conn->prepare($sql);  // Prepare the query
    $stmt->execute();  // Execute the query
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);  // Fetch results as an associative array

    // Query to get staff names
    $stmt = $conn->query("SELECT  admin_id, admin_name FROM admin");

    // Fetch all results into an associative array
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
    <link rel="stylesheet" href="admin-css/admin_header01.css">
    <link rel="stylesheet" href="admin-css/admin_home01.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin.js"></script>
    <title>Admin Homepage</title>

</head>

<body style="background-color: #eee;">
    <!-- NAVIGATION BAR -->
    <nav class="nav-bar">
        <a href = "../home.php"><img class="adorafur-logo" src="admin-pics/adorafur-logo.png" alt="Adorafur Logo" /></a>
        <div class="nav-container">
            <div class="home-button active">
                <a href="admin_home.php" class="home-text">Home</a>
            </div>
            <div class="book-button">
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

    <!-- HOME PAGE -->
    <div class="panel-container">
        <div class="head">
            <h6  class="head-text">Admin Panel</h6>
            <!-- Real-time clock -->
            <div class="time-text" id="real-time-clock">Loading...</div>
           
        </div>      
       
        <div class="date-and-day">Loading date...</div>

        <div class="reservations-container">
        <table class="reservations">
            <?php
            if ($stmt->rowCount() > 0) { // Check if there are results
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
                        data-book-balance="<?php echo htmlspecialchars($fetch_reservations['b_balance'])?>"
                        
                        

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

    <!-- Bootstrap Modal -->
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
                        // Check if there are staff names to display
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
                                <input type="hidden" name="ownerId" id="ownerId"?>
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
                                <?php
$proof_path = '/Adorafur/' . $fetch_reservations['pay_proof_of_payment'];
$full_path = $_SERVER['DOCUMENT_ROOT'] . $proof_path; // absolute path on the server

if (!empty($fetch_reservations['pay_proof_of_payment']) && file_exists($full_path)) {
    echo '<a href="' . $proof_path . '" target="_blank" class="btn" id="view-photo">View Proof</a>';
} else {
    echo '<span>No proof of payment</span>';
}
?>
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
                                    <!-- Replace the card with this button -->
<!-- <div class="mb-3"> -->
    <button type="button" class="btn btn-primary w-100" onclick="openPaymentModal(document.getElementById('modalBookingId').textContent, document.getElementById('bookBalance').value)">
        Add Payment
    </button>
<!-- </div> -->
                                    <!-- <div id="paymentForm" class="collapse">
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label fw-bold text-brown mb-2">Amount Paid:</label>
                                                        <input type="text" class="form-control" name="amountPaid" id="amountPaid">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                <label class="form-label fw-bold text-brown mb-2">Mode of Payment:</label>
                                                <select class="form-control" name="paymentModeAdd" id="paymentModeAdd" onchange="toggleOtherPaymentMode()">
                                                    <option value="cash">Cash</option>
                                                    <option value="gcash">GCash</option>
                                                    <option value="maya">Maya</option>
                                                </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                    <label class="form-label fw-bold text-brown mb-2">Payment Status:</label>
                                                    <select class="form-control" name="paymentStatusAdd" id="paymentStatusAdd">
                                                        <option value="fully_paid">Fully Paid</option>
                                                        <option value="down_payment">Down Payment</option>
                                                    </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label fw-bold text-brown mb-2">Reference No.:</label>
                                                        <input type="text" class="form-control" name="refNo" id="refNo">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <button type="button" class="btn btn-success w-100" onclick="savePayment()">Save Payment</button>
                                            </div>
                                        </div>
                                    </div> -->
                                </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
    
<script>
document.addEventListener('DOMContentLoaded', function() {
    var bookingModal = document.getElementById('bookingModal');
    bookingModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var bookingId = button.getAttribute('data-booking-id');
        var modalBookingId = document.getElementById('modalBookingId');
        modalBookingId.textContent = bookingId;


        // Set the modal header class based on the service type
        var service = button.getAttribute('data-service');
        var modalHeader = document.getElementById('modalHeader');
        modalHeader.className = 'modal-header ' + (service.toLowerCase() === 'pet hotel' ? 'modal-hotel' : 'modal-daycare');


        // Populate form fields with data from button attributes
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

        // Fetch additional booking data
        fetch('get_booking_data.php?booking_id=' + bookingId)
            .then(response => response.json())
            .then(data => {
                if (data.booking_status) {
                    var bookingStatusSelect = document.getElementById('bookingStatusUpdate');
                    var bookingStatus = data.booking_status.toLowerCase();
                    
                    // Find the matching option
                    for (var i = 0; i < bookingStatusSelect.options.length; i++) {
                        if (bookingStatusSelect.options[i].value === bookingStatus) {
                            bookingStatusSelect.selectedIndex = i;
                            break;
                        }
                    }
                }
            })
            .catch(error => console.error('Error:', error));
    });

    document.getElementById('saveButton').addEventListener('click', function (e) {
    e.preventDefault();

    // Create FormData object
    var formData = new FormData();
    
    // Add booking ID
    formData.append('booking_id', document.getElementById('modalBookingId').textContent);
    
    // Add check-in and check-out dates
    formData.append('checkIn', document.getElementById('checkIn').value);
    formData.append('checkOut', document.getElementById('checkOut').value);
    
    // Add booking status - note the name change from bookingStatusUpdate to booking_status
    formData.append('booking_status', document.getElementById('bookingStatusUpdate').value);
    
    // Add payment status
    formData.append('paymentStatus', document.getElementById('paymentStatus').value);
    
    // Add staff selection
    formData.append('staff', document.getElementById('staffSelect').value);

    // Send the AJAX request
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

    function openModal(bookingId, currentStatus) {
    document.getElementById('bookingId').value = bookingId;
    document.getElementById('bookingStatus').value = currentStatus;
    const myModal = new bootstrap.Modal(document.getElementById('bookingModal'));
    myModal.show();
    }

    // function saveBooking() {
    // const formData = new FormData(document.getElementById('bookingForm'));
    // fetch('update_booking.php', {
    //     method: 'POST',
    //     body: formData
    // })
    // .then(res => res.text())
    // .then(data => {
    //     alert('Booking updated!');
    //     location.reload();
    // });
    // }

    // function savePayment() {
    // const formData = new FormData(document.getElementById('bookingForm'));
    // fetch('add_payment.php', {
    //     method: 'POST',
    //     body: formData
    // })
    // .then(res => res.text())
    // .then(data => {
    //     alert('Payment added!');
    //     location.reload();
    // });
    // }
});


// Function to open the payment modal
function openPaymentModal(bookingId, currentBalance) {
  // Set the booking ID and current balance in the hidden fields
  document.getElementById("paymentBookingId").value = bookingId
  document.getElementById("currentBalance").value = currentBalance

  // Reset form fields
  document.getElementById("addPaymentForm").reset()
  document.getElementById("amountPaid").value = ""
  document.getElementById("refNo").value = ""

  // Hide the "other" payment mode field
  document.getElementById("otherPaymentMode").classList.add("d-none")

  // Open the modal
  const paymentModal = new bootstrap.Modal(document.getElementById("addPaymentModal"))
  paymentModal.show()
}

// Function to handle payment submission
document.addEventListener("DOMContentLoaded", () => {
  const savePaymentBtn = document.getElementById("savePaymentBtn")

  if (savePaymentBtn) {
    savePaymentBtn.addEventListener("click", () => {
      const form = document.getElementById("addPaymentForm")

      // Basic form validation
      if (!form.checkValidity()) {
        form.reportValidity()
        return
      }

      // Get form data
      const formData = new FormData(form)

      // Check if amount paid is not greater than current balance
      const amountPaid = Number.parseFloat(formData.get("amount_paid"))
      const currentBalance = Number.parseFloat(formData.get("current_balance"))

      if (amountPaid <= 0) {
        alert("Amount paid must be greater than zero.")
        return
      }

      if (amountPaid > currentBalance) {
        alert("Amount paid cannot be greater than the current balance.")
        return
      }

      // Submit the form data to the server
      fetch("add_payment.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Close the payment modal
            const paymentModal = bootstrap.Modal.getInstance(document.getElementById("addPaymentModal"))
            paymentModal.hide()

            // Update the balance in the booking modal
            const newBalance = currentBalance - amountPaid
            document.getElementById("bookBalance").value = newBalance.toFixed(2)

            // Update payment status if fully paid
            if (newBalance === 0) {
              document.getElementById("paymentStatus").value = "Fully Paid"
            }

            // Show success message
            alert("Payment added successfully!")
          } else {
            alert("Error: " + data.message)
          }
        })
        .catch((error) => {
          console.error("Error:", error)
          alert("An error occurred while processing the payment.")
        })
    })
  }
})

</script>

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
          
          <input type="hidden" name="customer_id" value="<?= $fetch_reservations['owner_id']?>">

          
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

</body>
</html>

