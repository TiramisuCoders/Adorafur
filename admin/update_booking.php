<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("../connect.php");

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$admin_id = $_SESSION['admin_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Log received data for debugging
    error_log("Received POST data: " . print_r($_POST, true));
    
    // Get form data
    $booking_id = $_POST['booking_id'];
    $check_in = isset($_POST['checkIn']) ? $_POST['checkIn'] : null;
    $check_out = isset($_POST['checkOut']) ? $_POST['checkOut'] : null;
    $booking_status = isset($_POST['booking_status']) ? $_POST['booking_status'] : null;
    $payment_status = isset($_POST['paymentStatus']) ? $_POST['paymentStatus'] : null;
    $staff = isset($_POST['staff']) ? $_POST['staff'] : null;
    
    // Validate required fields
    if (empty($booking_id)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        exit();
    }
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Update booking information
        $update_fields = [];
        $params = [':booking_id' => $booking_id];
        
        if (!empty($check_in)) {
            $update_fields[] = "booking_check_in = :check_in";
            $params[':check_in'] = $check_in;
        }
        
        if (!empty($check_out)) {
            $update_fields[] = "booking_check_out = :check_out";
            $params[':check_out'] = $check_out;
        }
        
        if (!empty($booking_status)) {
            $update_fields[] = "booking_status = :booking_status";
            $params[':booking_status'] = $booking_status;
        }
        
        if (!empty($staff)) {
            $update_fields[] = "assigned_staff = :staff";
            $params[':staff'] = $staff;
        }
        
        // Only update booking table if there are fields to update
        if (!empty($update_fields)) {
            $update_query = "UPDATE bookings SET " . implode(", ", $update_fields) . " WHERE booking_id = :booking_id";
            
            $stmt = $conn->prepare($update_query);
            $stmt->execute($params);
            
            error_log("Booking update query: " . $update_query);
            error_log("Booking update params: " . print_r($params, true));
        }
        
        // Update payment status if provided
        if (!empty($payment_status)) {
            $payment_update_query = "UPDATE payment SET pay_status = :payment_status WHERE booking_id = :booking_id";
            $payment_params = [
                ':payment_status' => $payment_status,
                ':booking_id' => $booking_id
            ];
            
            $payment_stmt = $conn->prepare($payment_update_query);
            $payment_stmt->execute($payment_params);
            
            error_log("Payment update query: " . $payment_update_query);
            error_log("Payment update params: " . print_r($payment_params, true));
        }
        
        // Log the admin action
        $log_query = "INSERT INTO admin_logs (admin_id, action_type, action_details, booking_id) 
                      VALUES (:admin_id, 'update_booking', :action_details, :booking_id)";
        $log_params = [
            ':admin_id' => $admin_id,
            ':action_details' => 'Updated booking information',
            ':booking_id' => $booking_id
        ];
        
        $log_stmt = $conn->prepare($log_query);
        $log_stmt->execute($log_params);
        
        // Commit transaction
        $conn->commit();
        
        // Return success response
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        
        error_log("Database error: " . $e->getMessage());
        error_log("Error code: " . $e->getCode());
        error_log("Error trace: " . $e->getTraceAsString());
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error updating booking: ' . $e->getMessage()]);
    }
} else {
    // Not a POST request
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
exit();
?>
