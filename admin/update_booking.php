<?php
// Prevent any HTML output before JSON response
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 0); // Change to 0 to prevent HTML errors in output

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("../connect.php");

// Function to send JSON response and exit
function send_json_response($success, $message, $data = []) {
    // Clear any output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: application/json');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message
    ], $data));
    exit();
}

if (!isset($_SESSION['admin_id'])) {
    send_json_response(false, 'Unauthorized access');
}

$admin_id = $_SESSION['admin_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("Received POST data: " . print_r($_POST, true));
    
    $booking_id = isset($_POST['booking_id']) ? $_POST['booking_id'] : '';
    $check_in = isset($_POST['checkIn']) ? $_POST['checkIn'] : null;
    $check_out = isset($_POST['checkOut']) ? $_POST['checkOut'] : null;
    $booking_status = isset($_POST['booking_status']) ? $_POST['booking_status'] : null;
    $payment_status = isset($_POST['paymentStatus']) ? $_POST['paymentStatus'] : null;
    $staff_id = isset($_POST['staff']) ? $_POST['staff'] : null;
    
    error_log("Booking status received: " . ($booking_status ?? 'NULL'));
    
    if (empty($booking_id)) {
        send_json_response(false, 'Booking ID is required');
    }
    
    try {
        $conn->beginTransaction();
        
        $update_fields = [];
        $params = [':booking_id' => $booking_id];
        
        if (!empty($check_in) && !empty($check_out)) {
            // Get booking details to retrieve pet size and service
            $stmt = $conn->prepare("
                SELECT 
                    b.service_id, 
                    p.pet_size,
                    s.service_name
                FROM bookings b
                JOIN pet p ON b.pet_id = p.pet_id
                JOIN service s ON b.service_id = s.service_id
                WHERE b.booking_id = :booking_id
            ");
            $stmt->bindParam(':booking_id', $booking_id);
            $stmt->execute();
            $booking_details = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($booking_details) {
                $pet_size = $booking_details['pet_size'];
                $service_id = $booking_details['service_id'];
                $service_name = $booking_details['service_name'];
                
                // Calculate duration between check-in and check-out dates
                $check_in_date = new DateTime($check_in);
                $check_out_date = new DateTime($check_out);
                $duration = $check_in_date->diff($check_out_date)->days;
                
                // Ensure minimum duration of 1 day
                $duration = max(1, $duration);
                
                // Get service rate based on service_id and pet_size
                $stmt = $conn->prepare("
                    SELECT 
                        s.service_rate 
                    FROM service s
                    WHERE s.service_id = :service_id
                    AND s.service_variant = :pet_size
                ");
                $stmt->bindParam(':pet_size', $pet_size);
                $stmt->bindParam(':service_id', $service_id);
                $stmt->execute();
                $service_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($service_data) {
                    $service_rate = floatval($service_data['service_rate']);
                    
                    // Calculate total amount (duration * service_rate)
                    $total_amount = $duration * $service_rate;
                    
                    // Get total paid amount from payments table
                    $stmt = $conn->prepare("
                        SELECT COALESCE(SUM(pay_amount), 0) AS total_paid
                        FROM payment
                        WHERE booking_id = :booking_id
                    ");
                    $stmt->bindParam(':booking_id', $booking_id);
                    $stmt->execute();
                    $payment_data = $stmt->fetch(PDO::FETCH_ASSOC);
                    $total_paid = floatval($payment_data['total_paid']);
                    
                    // Calculate booking balance
                    $booking_balance = $total_amount - $total_paid;
                    $booking_balance = max(0, $booking_balance); // Ensure balance is not negative
                    
                    // Add total_amount and booking_balance to the update fields
                    $update_fields[] = "booking_total_amount = :total_amount";
                    $params[':total_amount'] = $total_amount;
                    
                    $update_fields[] = "booking_balance = :booking_balance";
                    $params[':booking_balance'] = $booking_balance;
                    
                    // Update payment status if balance is zero
                    if ($booking_balance == 0 && $total_paid > 0) {
                        $payment_status = "Fully Paid";
                    }
                }
            }
            
            $update_fields[] = "booking_check_in = :check_in";
            $params[':check_in'] = $check_in;
            
            $update_fields[] = "booking_check_out = :check_out";
            $params[':check_out'] = $check_out;
        }
        
        if ($booking_status !== null) {
            $booking_status = ucfirst($booking_status);
            $update_fields[] = "booking_status = :booking_status";
            $params[':booking_status'] = $booking_status;
            
            error_log("Processed booking status: " . $booking_status);
        }
        
        if (!empty($staff_id)) {
            $update_fields[] = "admin_id = :staff_id";
            $params[':staff_id'] = $staff_id;
        }
        
        if (!empty($update_fields)) {
            $update_query = "UPDATE bookings SET " . implode(", ", $update_fields) . " WHERE booking_id = :booking_id";
            
            error_log("Booking update query: " . $update_query);
            error_log("Booking update params: " . print_r($params, true));
            
            $stmt = $conn->prepare($update_query);
            $result = $stmt->execute($params);
            
            if (!$result) {
                error_log("Update failed. PDO error info: " . print_r($stmt->errorInfo(), true));
                throw new PDOException("Failed to update booking status");
            }
            
            $rowCount = $stmt->rowCount();
            error_log("Rows affected by update: " . $rowCount);
            
            if ($rowCount === 0) {
                error_log("Warning: No rows were updated. Booking ID might not exist: " . $booking_id);
            }
        }
        
        if (!empty($payment_status)) {
            $payment_update_query = "UPDATE payment SET pay_status = :payment_status WHERE booking_id = :booking_id";
            $payment_params = [
                ':payment_status' => $payment_status,
                ':booking_id' => $booking_id
            ];
            
            $payment_stmt = $conn->prepare($payment_update_query);
            $payment_result = $payment_stmt->execute($payment_params);
            
            error_log("Payment update query: " . $payment_update_query);
            error_log("Payment update params: " . print_r($payment_params, true));
            error_log("Payment update result: " . ($payment_result ? 'Success' : 'Failed'));
        }
        
        $conn->commit();
        
        send_json_response(true, 'Booking updated successfully');
        
    } catch (PDOException $e) {
        $conn->rollBack();
        
        error_log("Database error: " . $e->getMessage());
        error_log("Error code: " . $e->getCode());
        error_log("Error trace: " . $e->getTraceAsString());
        
        send_json_response(false, 'Error updating booking: ' . $e->getMessage());
    } catch (Exception $e) {
        $conn->rollBack();
        
        error_log("General error: " . $e->getMessage());
        error_log("Error trace: " . $e->getTraceAsString());
        
        send_json_response(false, 'Error updating booking: ' . $e->getMessage());
    }
} else {
    send_json_response(false, 'Invalid request method');
}
?>
