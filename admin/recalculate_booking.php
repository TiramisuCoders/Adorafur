<?php
session_start();
// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

include("../connect.php");

// Initialize response array
$response = array('success' => false, 'message' => '');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get form data
        $booking_id = $_POST['booking_id'];
        $check_in = $_POST['check_in'];
        $check_out = $_POST['check_out'];
        $pet_size = $_POST['pet_size'];
        $service = $_POST['service'];
        
        // Validate required fields
        if (empty($booking_id) || empty($check_in) || empty($check_out) || empty($pet_size) || empty($service)) {
            throw new Exception('All fields are required');
        }
        
        // Begin transaction
        $conn->beginTransaction();
        
        // 1. Get booking details to retrieve service_id and pet_id
        $stmt = $conn->prepare("SELECT b.service_id, b.pet_id FROM bookings b WHERE b.booking_id = :booking_id");
        $stmt->bindParam(':booking_id', $booking_id);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$booking) {
            throw new Exception('Booking not found');
        }
        
        $service_id = $booking['service_id'];
        $pet_id = $booking['pet_id'];
        
        // 2. Calculate duration between check-in and check-out dates
        $check_in_date = new DateTime($check_in);
        $check_out_date = new DateTime($check_out);
        $duration = $check_in_date->diff($check_out_date)->days;
        
        // Ensure minimum duration of 1 day
        $duration = max(1, $duration);
        
        // 3. Get service rate based on service_id and pet_size
        $stmt = $conn->prepare("
            SELECT service_rate
FROM service
WHERE service_id = :service_id
  AND service_variant = :pet_size

        ");
        $stmt->bindParam(':pet_size', $pet_size);
        $stmt->bindParam(':service_id', $service_id);
        $stmt->execute();
        $service_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$service_data) {
            throw new Exception('Service rate not found');
        }
        
        $service_rate = floatval($service_data['service_rate']);
        
        // 4. Calculate total amount (duration * service_rate)
        $total_amount = $duration * $service_rate;
        
        // 5. Get total paid amount from payments table
        $stmt = $conn->prepare("
            SELECT COALESCE(SUM(pay_amount), 0) AS total_paid
            FROM payment
            WHERE booking_id = :booking_id
        ");
        $stmt->bindParam(':booking_id', $booking_id);
        $stmt->execute();
        $payment_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $total_paid = floatval($payment_data['total_paid']);
        
        // 6. Calculate booking balance
        $booking_balance = $total_amount - $total_paid;
        $booking_balance = max(0, $booking_balance); // Ensure balance is not negative
        
        // 7. Update booking with new dates, total amount, and balance
        $stmt = $conn->prepare("
            UPDATE bookings
            SET 
                booking_check_in = :check_in,
                booking_check_out = :check_out,
                booking_total_amount = :total_amount,
                booking_balance = :booking_balance
            WHERE booking_id = :booking_id
        ");
        $stmt->bindParam(':check_in', $check_in);
        $stmt->bindParam(':check_out', $check_out);
        $stmt->bindParam(':total_amount', $total_amount);
        $stmt->bindParam(':booking_balance', $booking_balance);
        $stmt->bindParam(':booking_id', $booking_id);
        $stmt->execute();
        
        // 8. Update payment status if balance is zero
        if ($booking_balance == 0 && $total_paid > 0) {
            $fully_paid = "Fully Paid";
            $stmt = $conn->prepare("UPDATE payment SET pay_status = :pay_status WHERE booking_id = :booking_id");
            $stmt->bindParam(':pay_status', $fully_paid);
            $stmt->bindParam(':booking_id', $booking_id);
            $stmt->execute();
        }
        
        // Commit transaction
        $conn->commit();
        
        // Return success response with calculated values
        $response['success'] = true;
        $response['message'] = 'Booking recalculated successfully';
        $response['duration'] = $duration;
        $response['service_rate'] = $service_rate;
        $response['total_amount'] = number_format($total_amount, 2, '.', '');
        $response['total_paid'] = number_format($total_paid, 2, '.', '');
        $response['booking_balance'] = number_format($booking_balance, 2, '.', '');
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        $response['message'] = $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>
