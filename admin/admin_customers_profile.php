<?php

if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect back to the customers list if no ID is provided
    header('Location: admin_navbar/admin_customers.php');
    exit;
}

$customerId = $_GET['id'];

include('../connect.php');


// Process membership update form if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['memb-status'])) {
    try {
        // Get form data
        $membershipStatus = $_POST['memb-status'];
        $paymentMethod = $_POST['paymentMethod'] ?? '';
        $amount = $_POST['membershipAmount'] ?? 0;
        $refNumber = $_POST['refNumber'] ?? '';
        
        // Update customer membership status
        $updateSql = "UPDATE customer SET c_membership_status = :status WHERE c_id = :id";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bindParam(':status', $membershipStatus, PDO::PARAM_STR);
        $updateStmt->bindParam(':id', $customerId, PDO::PARAM_INT);
        $updateStmt->execute();
        
        // Handle file upload if provided
        if (isset($_FILES['proofOfPayment']) && $_FILES['proofOfPayment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['proofOfPayment']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['proofOfPayment']['tmp_name'], $uploadFile)) {
                // Insert payment record
                $paymentSql = "INSERT INTO payment (customer_id, pay_amount, pay_method, pay_reference_number, pay_status, proof_of_payment) 
                               VALUES (:customer_id, :amount, :method, :ref_number, 'Completed', :proof)";
                $paymentStmt = $updatePdo->prepare($paymentSql);
                $paymentStmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
                $paymentStmt->bindParam(':amount', $amount, PDO::PARAM_STR);
                $paymentStmt->bindParam(':method', $paymentMethod, PDO::PARAM_STR);
                $paymentStmt->bindParam(':ref_number', $refNumber, PDO::PARAM_STR);
                $paymentStmt->bindParam(':proof', $uploadFile, PDO::PARAM_STR);
                $paymentStmt->execute();
            }
        }
        
        // Redirect to refresh the page
        header("Location: admin_customers_profile.php?id=$customerId&updated=1");
        exit;
    } catch (PDOException $e) {
        $updateError = "Error updating membership: " . $e->getMessage();
    }
}

try {
    $customerSql = "SELECT 
                    c.c_id, 
                    CONCAT(c.c_first_name, ' ', c.c_last_name) AS owner_name,
                    m.membership_status as membership_status
                FROM 
                    customer c
                LEFT JOIN
                    membership_status m on c.c_membership_status = m.membership_id
                WHERE 
                    c.c_id = :customer_id";
    
    $customerStmt = $conn->prepare($customerSql);
    $customerStmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
    $customerStmt->execute();
    
    // Check if customer exists
    if ($customerStmt->rowCount() === 0) {
        // Redirect back to the customers list if customer not found
        header('Location: admin_customers.php');
        exit;
    }
    
    // Fetch customer data
    $customer = $customerStmt->fetch(PDO::FETCH_ASSOC);
    
    // Query to fetch pets with correct breeds
    $petsSql = "SELECT 
                pet_name,
                pet_breed
                FROM 
                pet
                WHERE 
                customer_id = :customer_id";
                
    $petsStmt = $conn->prepare($petsSql);
    $petsStmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
    $petsStmt->execute();

    $pets = $petsStmt->fetchAll(PDO::FETCH_ASSOC);

    
    $transactionsSql = "SELECT 
        t.booking_id,
        s.service_name,
        p.pet_name,
        c.c_id,
        pay.pay_amount,
        pay.pay_status,
        pay.pay_reference_number,
        pay.pay_method,
        pay.pay_date
    FROM 
        bookings t
    JOIN 
        service s ON t.service_id = s.service_id
    JOIN 
        pet p ON t.pet_id = p.pet_id
    JOIN 
        customer c ON p.customer_id = c.c_id 
    JOIN 
        payment pay ON pay.booking_id = t.booking_id
    WHERE 
        c.c_id = :customer_id
    ORDER BY 
        pay.pay_date DESC";

    $transactionsStmt = $conn->prepare($transactionsSql);
    $transactionsStmt->bindParam(':customer_id', $customerId, PDO::PARAM_INT);
    $transactionsStmt->execute();
    
    // Fetch all transactions
    $transactions = $transactionsStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}


// If no transactions found, create an empty array
if (empty($transactions)) {
    $transactions = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Customer Profile</title>
    <link rel="stylesheet" href="admin-css/admin_header01.css">
    <link rel="stylesheet" href="admin-css/admin_customer_profile01.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="admin-pics/adorafur-logo.png">
    <style>
        body { background: #e8e8e8; }
    </style>
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
            <a href="admin_bookings.php" class="booking-text">Appointments</a>
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
        </div>

    </nav>

    <!-- Main Content -->
    <div class="panel-container">

        <div class="head">
            <div class="head-text">Customers</div>
            <div class="time-text" id="real-time-clock">Loading...</div>
        </div>
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == 1): ?>
        <div class="alert alert-success">
            Membership status updated successfully!
        </div>
        <?php endif; ?>
        
        <?php if (isset($updateError)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($updateError); ?>
        </div>
        <?php endif; ?>

        <div class="profile-box">
            <!-- User Section -->
            <div class="user-section">
                <a href="admin_customers.php">
                    <img src="admin-pics/back-icon.png" class="back-icon" alt="Back">
                </a>
                <img src="admin-pics/crown.png" class="crown-img">
                <div class="user-name"><?php echo htmlspecialchars($customer['owner_name']); ?></div>
                
                <?php
                    $status = $customer['membership_status'] ?? 'Regular';
                    $statusClass = strtolower($status); // Convert to lowercase for class consistency
                ?>
                
                <div class="gd-status <?php echo $statusClass; ?>">
                <?php echo htmlspecialchars($customer['membership_status'] ?? 'Regular'); ?> Member
                    <button id="openModalBtn" class="open-btn">
                        <img src="admin-pics/edit-icon.png" class="edit-img" alt="Edit">
                    </button>
                </div>
                <?php foreach ($pets as $pet): ?>
                <div class="pet-card">
                    <div class="pet-profile1"><?php echo htmlspecialchars($pet['pet_name']); ?></div>
                    <div class="pet-profile2"><?php echo htmlspecialchars($pet['pet_breed']); ?></div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($pets)): ?>
                <div class="pet-card">
                    <div class="pet-profile1">No pets registered</div>
                </div>
                <?php endif; ?>
            </div>

            <div class="divider-line"></div>

            
            <!-- Transactions Section -->
            <div class="transactions-box">
                <div class="transac-header">Reservations</div>
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" 
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Sort
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li><a class="dropdown-item" href="#" data-sort="oldest">Oldest</a></li>
                        <li><a class="dropdown-item" href="#" data-sort="newest">Newest</a></li>
                        <li><a class="dropdown-item active" href="#" data-sort="newest">Newest</a></li>
                    </ul>
                </div>
                
                <div class="booking-list-container">
                <!-- Transactions Table -->
                <table class="transactions-table" id="transactionsTable">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Service</th>
                            <th>Pet Name</th>
                            <th>Amount Paid</th>
                            <th>Ref. Number</th>
                            <th>Date Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($transactions)): ?>
                            <?php foreach ($transactions as $transaction): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($transaction['booking_id']); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['service_name']); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['pet_name']); ?></td>
                                    <td>
                                        <div class="amount-container">
                                            <span class="amount-value"><?php echo htmlspecialchars($transaction['pay_amount']); ?></span> <br>
                                            <span class="amount-status"><?php echo htmlspecialchars($transaction['pay_status']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="ref-container">
                                            <span><?php echo htmlspecialchars($transaction['pay_reference_number']); ?></span> <br>
                                            <span class="ref-source"><?php echo htmlspecialchars($transaction['pay_method']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($transaction['pay_date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No transactions found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
    </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="membershipModal" class="modal">
        <div class="modal-content">

            <img src="admin-pics/memb-modal-header.png" class="status-header">
            <h2 class="memb-status-header">Membership Status Update</h2>
            <form id="membershipForm" action="admin_customers_profile.php?id=<?php echo htmlspecialchars($customerId); ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customerId); ?>">
    
    <div style="display: flex;">
        <div style="width: 50%; padding-right: 10px;">
            <label for="paymentMethod">Payment Method:</label>
            <select id="paymentMethod" name="paymentMethod" onchange="togglePaymentFields()">
                <option value="gcash">GCash</option>
                <option value="maya">Maya</option>
                <option value="cash">Cash</option>
            </select>
            
            <br>
            <label for="membershipStatus">Membership Status:</label><br>
            <input type="radio" id="status-reg" name="memb-status" value="1" <?php echo ($customer['membership_status'] == 'REGULAR' || !$customer['membership_status']) ? 'checked' : ''; ?>>
            <label for="status-reg">Regular</label><br>
            <input type="radio" id="status-silver" name="memb-status" value="2" <?php echo ($customer['membership_status'] == 'SILVER') ? 'checked' : ''; ?>>
            <label for="status-silver">Silver</label><br>
            <input type="radio" id="status-gold" name="memb-status" value="3" <?php echo ($customer['membership_status'] == 'GOLD') ? 'checked' : ''; ?>>
            <label for="status-gold">Gold</label><br>
            <input type="radio" id="status-platinum" name="memb-status" value="4" <?php echo ($customer['membership_status'] == 'PLATINUM') ? 'checked' : ''; ?>>
            <label for="status-platinum">Platinum</label><br>
        </div>

        <div style="width: 50%; padding-left: 10px;">
            <div class="input-group">
                <label for="membershipAmount">Amount</label>
                <input type="text" name="membershipAmount" placeholder="Enter amount">
            </div>

            <div class="input-group">
                <label for="refNumber">Reference No.</label>
                <input type="text" id="refNumber" name="refNumber" placeholder="Enter reference number">
            </div>

            <label for="proofofPayment">Proof of Payment</label>
            <input type="file" id="proofOfPayment" name="proofOfPayment">
        </div>
    </div>

    <div class="cancel-save-btns">
        <button type="button" id="cancelBtn" class="modal-button">Cancel</button>
        <button type="submit" class="modal-button">Save</button>
    </div>
</form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        
        // JavaScript for modal functionality
        var modal = document.getElementById("membershipModal");
        var btn = document.getElementById("openModalBtn");
        var cancelBtn = document.getElementById("cancelBtn");

        btn.onclick = function() {
            modal.style.display = "block";
        }

        cancelBtn.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
        
        // Sort transactions
        document.querySelectorAll('.dropdown-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();

                
                // Remove active class from all items
                document.querySelectorAll('.dropdown-item').forEach(i => {
                    i.classList.remove('active');
                });
                
                // Add active class to clicked item
                this.classList.add('active');

                const sortOrder = this.getAttribute('data-sort');
                const transactions = Array.from(document.querySelectorAll('.trans-record'));
                
                transactions.sort((a, b) => {
                    const dateA = new Date(a.querySelector('.trans-record-date').textContent);
                    const dateB = new Date(b.querySelector('.trans-record-date').textContent);
                    
                    return sortOrder === 'oldest' ? dateA - dateB : dateB - dateA;
                });
                
                // Remove existing transactions
                document.querySelectorAll('.trans-record').forEach(tr => tr.remove());
                
                // Add sorted transactions
                const transactionLine = document.querySelector('.transac-line');
                transactions.forEach(tr => {
                    transactionContainer.appendChild(tr);
                });
            });
        });


        function togglePaymentFields() {
    var paymentMethod = document.getElementById("paymentMethod").value;
    var otherInput = document.getElementById("otherPaymentMethod");
    var refNumber = document.getElementById("refNumber");
    var proofOfPayment = document.getElementById("proofOfPayment");

    if (paymentMethod === "others") {
        otherInput.style.display = "block";
        otherInput.setAttribute("required", "true");
    } else {
        otherInput.style.display = "none";
        otherInput.removeAttribute("required");
    }

    if (paymentMethod === "cash") {
        refNumber.value = "NA";  // Pass "NA" to payment table
        refNumber.setAttribute("readonly", "true");
        refNumber.removeAttribute("required");
        proofOfPayment.setAttribute("readonly", "true");
        proofOfPayment.removeAttribute("required");
    } else {
        refNumber.value = "";
        refNumber.removeAttribute("readonly");
        refNumber.setAttribute("required", "true");

        proofOfPayment.setAttribute("required", "true");
    }
}
    </script>
        <script src="admin.js"></script>
</body>
</html>