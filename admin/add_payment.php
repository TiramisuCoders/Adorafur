<?php
// Start output buffering to prevent any unexpected output
ob_start();

session_start();
// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
  // Redirect to login page if not logged in
  header("Location: ../index.php");
  exit();
}

include("../connect.php");

// Initialize response array
$response = array('success' => false, 'message' => '');

try {
  // Check if all required fields are present
  if (!isset($_POST['booking_id']) || !isset($_POST['amount_paid']) || !isset($_POST['payment_mode']) || !isset($_POST['payment_status'])) {
    $response['message'] = 'Missing required fields';
    throw new Exception('Missing required fields');
  }
  
  // Get form data
  $bookingId = $_POST['booking_id'];
  $amountPaid = floatval($_POST['amount_paid']);
  $paymentMode = $_POST['payment_mode'];
  $referenceNo = isset($_POST['reference_no']) ? $_POST['reference_no'] : '';
  $paymentStatus = $_POST['payment_status'];
  $adminId = $_SESSION['admin_id']; // Get the admin ID from the session
  $customerId = isset($_POST['customer_id']) ? $_POST['customer_id'] : null;
  
  if (!$customerId) {
    // If customer_id is not provided, try to get it from the booking
    $stmt = $conn->prepare("
      SELECT p.customer_id 
      FROM bookings b 
      JOIN pet p ON b.pet_id = p.pet_id 
      WHERE b.booking_id = :booking_id
    ");
    $stmt->bindParam(':booking_id', $bookingId);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result && isset($result['customer_id'])) {
      $customerId = $result['customer_id'];
    } else {
      throw new Exception('Customer ID not found');
    }
  }
  
  // If payment mode is "others", use the specified value
  if ($paymentMode === 'others' && isset($_POST['other_payment_mode']) && !empty($_POST['other_payment_mode'])) {
    $paymentMode = $_POST['other_payment_mode'];
  }
  
  // Begin transaction
  $conn->beginTransaction();
  
  // 1. Get current balance
  $stmt = $conn->prepare("SELECT booking_balance FROM bookings WHERE booking_id = :booking_id");
  $stmt->bindParam(':booking_id', $bookingId);
  $stmt->execute();
  $booking = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if (!$booking) {
    throw new Exception('Booking not found');
  }
  
  $currentBalance = floatval($booking['booking_balance']);
  
  // Check if amount paid is valid
  if ($amountPaid <= 0) {
    throw new Exception('Amount paid must be greater than zero');
  }
  
  if ($amountPaid > $currentBalance) {
    throw new Exception('Amount paid cannot be greater than the current balance');
  }
  
  // 2. Calculate new balance
  $newBalance = $currentBalance - $amountPaid;
  
  // 3. Update booking balance
  $stmt = $conn->prepare("UPDATE bookings SET booking_balance = :new_balance WHERE booking_id = :booking_id");
  $stmt->bindParam(':new_balance', $newBalance);
  $stmt->bindParam(':booking_id', $bookingId);
  $stmt->execute();
  
  // 4. Insert payment record
  $stmt = $conn->prepare("INSERT INTO payment (booking_id, customer_id, pay_amount, pay_method, pay_reference_number, pay_category, pay_status, pay_date, admin_id) 
                         VALUES (:booking_id, :customer_id, :pay_amount, :pay_method, :pay_reference_number, 'Booking Fee', :pay_status, NOW(), :admin_id)");
  $stmt->bindParam(':booking_id', $bookingId);
  $stmt->bindParam(':customer_id', $customerId);
  $stmt->bindParam(':pay_amount', $amountPaid);
  $stmt->bindParam(':pay_method', $paymentMode);
  $stmt->bindParam(':pay_reference_number', $referenceNo);
  $stmt->bindParam(':pay_status', $paymentStatus);
  $stmt->bindParam(':admin_id', $adminId);
  $stmt->execute();
  
  // If balance is zero, update payment status to "Fully Paid" for all payment records of this booking
  if ($newBalance == 0) {
    $fullyPaid = "Fully Paid";
    $stmt = $conn->prepare("UPDATE payment SET pay_status = :pay_status WHERE booking_id = :booking_id");
    $stmt->bindParam(':pay_status', $fullyPaid);
    $stmt->bindParam(':booking_id', $bookingId);
    $stmt->execute();
  }
  
  // Commit transaction
  $conn->commit();
  
  // Return success response
  $response['success'] = true;
  $response['message'] = 'Payment added successfully';
  $response['booking_balance'] = $newBalance;
  
} catch (Exception $e) {
  // Rollback transaction on error
  if ($conn && $conn->inTransaction()) {
    $conn->rollBack();
  }
  
  $response['message'] = $e->getMessage();
  error_log("Payment error: " . $e->getMessage());
} finally {
  // Clear any output buffers
  while (ob_get_level()) {
    ob_end_clean();
  }
  
  // Return JSON response
  header('Content-Type: application/json');
  echo json_encode($response);
  exit();
}
?>
