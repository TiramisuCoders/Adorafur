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
                                    $full_path = $_SERVER['DOCUMENT_ROOT'] . $proof_path;

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

        fetch('get_booking_data.php?booking_id=' + bookingId)
            .then(response => response.json())
            .then(data => {
                if (data.booking_status) {
                    var bookingStatusSelect = document.getElementById('bookingStatusUpdate');
                    var bookingStatus = data.booking_status.toLowerCase();
                    
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

    function openModal(bookingId, currentStatus) {
        document.getElementById('bookingId').value = bookingId;
        document.getElementById('bookingStatus').value = currentStatus;
        const myModal = new bootstrap.Modal(document.getElementById('bookingModal'));
        myModal.show();
        }
    });

    function openPaymentModal(bookingId, currentBalance) {
  document.getElementById("paymentBookingId").value = bookingId
  document.getElementById("currentBalance").value = currentBalance

  document.getElementById("addPaymentForm").reset()
  document.getElementById("amountPaid").value = ""
  document.getElementById("refNo").value = ""

  document.getElementById("otherPaymentMode").classList.add("d-none")

  const paymentModal = new bootstrap.Modal(document.getElementById("addPaymentModal"))
  paymentModal.show()
}

document.addEventListener("DOMContentLoaded", () => {
  const savePaymentBtn = document.getElementById("savePaymentBtn")

  if (savePaymentBtn) {
    savePaymentBtn.addEventListener("click", () => {
      const form = document.getElementById("addPaymentForm")

      if (!form.checkValidity()) {
        form.reportValidity()
        return
      }

      // Disable button to prevent double submission
      savePaymentBtn.disabled = true
      savePaymentBtn.textContent = "Processing..."

      const formData = new FormData(form)

      const amountPaid = Number.parseFloat(formData.get("amount_paid"))
      const currentBalance = Number.parseFloat(formData.get("current_balance"))

      if (amountPaid <= 0) {
        alert("Amount paid must be greater than zero.")
        savePaymentBtn.disabled = false
        savePaymentBtn.textContent = "Save Payment"
        return
      }

      if (amountPaid > currentBalance) {
        alert("Amount paid cannot be greater than the current balance.")
        savePaymentBtn.disabled = false
        savePaymentBtn.textContent = "Save Payment"
        return
      }

      fetch("add_payment.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.text())
        .then((text) => {
          // Try to parse as JSON, but handle HTML responses
          let data
          try {
            data = JSON.parse(text)
          } catch (e) {
            console.error("Server returned non-JSON response:", text)
            throw new Error("Server returned an invalid response. Check server logs.")
          }

          if (data.success) {
            // Close the modal
            const modalElement = document.getElementById("addPaymentModal")
            const modal = bootstrap.Modal.getInstance(modalElement)
            if (modal) {
              modal.hide()
            }

            // Update the UI with new balance
            if (data.booking_balance !== undefined) {
              document.getElementById("bookBalance").value = data.booking_balance

              // Update payment status if balance is zero
              if (Number.parseFloat(data.booking_balance) === 0) {
                document.getElementById("paymentStatus").value = "Fully Paid"
              } else if (Number.parseFloat(data.booking_balance) > 0) {
                document.getElementById("paymentStatus").value = "Down Payment"
              }

              // Show success message
              alert("Payment added successfully! New balance: " + data.booking_balance)
            } else {
              alert("Payment added successfully!")
            }

            // Optional: Reload the page to refresh all data
            // location.reload();
          } else {
            alert("Error: " + (data.message || "Unknown error occurred"))
          }
        })
        .catch((error) => {
          console.error("Error:", error)
          alert("An error occurred while processing the payment: " + error.message)
        })
        .finally(() => {
          // Re-enable button
          savePaymentBtn.disabled = false
          savePaymentBtn.textContent = "Save Payment"
        })
    })
  }

  // Add event listener for payment mode change
  const paymentModeSelect = document.getElementById("paymentModeAdd")
  if (paymentModeSelect) {
    paymentModeSelect.addEventListener("change", function () {
      const otherPaymentMode = document.getElementById("otherPaymentMode")
      if (this.value === "others") {
        otherPaymentMode.classList.remove("d-none")
      } else {
        otherPaymentMode.classList.add("d-none")
      }
    })
  }
})



// Add event listeners to check-in and check-out date inputs to recalculate total amount and balance
document.getElementById('checkIn').addEventListener('change', recalculateBookingAmount);
document.getElementById('checkOut').addEventListener('change', recalculateBookingAmount);

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