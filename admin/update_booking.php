<?php
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to browser
ini_set('log_errors', 1); // Log errors instead

// Start logging for debugging
error_log("Starting update_booking.php execution");

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

// Debug: Log all POST data
error_log("POST data: " . print_r($_POST, true));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $booking_id = isset($_POST['booking_id']) ? $_POST['booking_id'] : '';
    $check_in = isset($_POST['checkIn']) ? $_POST['checkIn'] : null;
    $check_out = isset($_POST['checkOut']) ? $_POST['checkOut'] : null;
    $booking_status = isset($_POST['booking_status']) ? $_POST['booking_status'] : null;
    $payment_status = isset($_POST['paymentStatus']) ? $_POST['paymentStatus'] : null;
    $staff_id = isset($_POST['staff']) ? $_POST['staff'] : null;
    
    // Debug logs
    error_log("Booking ID: " . $booking_id);
    error_log("Check-in: " . $check_in);
    error_log("Check-out: " . $check_out);
    error_log("Booking status: " . $booking_status);
    error_log("Payment status: " . $payment_status);
    error_log("Staff ID: " . $staff_id);
    
    // Validate required fields
    if (empty($booking_id)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        exit();
    }
    
    try {
        // Get the previous booking status before updating
        $prev_status_stmt = $conn->prepare("SELECT booking_status FROM bookings WHERE booking_id = ?");
        $prev_status_stmt->execute([$booking_id]);
        $previous_status = $prev_status_stmt->fetchColumn();
        
        error_log("Previous booking status: " . ($previous_status ?? 'NULL'));
        
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
        
        // Always include booking status in the update if provided
        if ($booking_status !== null) {
            // Ensure proper capitalization to match database constraint
            $booking_status = ucfirst($booking_status);
            $update_fields[] = "booking_status = :booking_status";
            $params[':booking_status'] = $booking_status;
            
            error_log("Processed booking status: " . $booking_status);
        }
        
        if (!empty($staff_id)) {
            $update_fields[] = "admin_id = :staff_id";
            $params[':staff_id'] = $staff_id;
        }
        
        // Only update booking table if there are fields to update
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
        }
        
        // Update payment status if provided
        if (!empty($payment_status)) {
            $payment_update_query = "UPDATE payment SET pay_status = :payment_status WHERE booking_id = :booking_id";
            $payment_params = [
                ':payment_status' => $payment_status,
                ':booking_id' => $booking_id
            ];
            
            $payment_stmt = $conn->prepare($payment_update_query);
            $payment_result = $payment_stmt->execute($payment_params);
            
            error_log("Payment update result: " . ($payment_result ? 'Success' : 'Failed'));
        }
        
        // Commit transaction
        $conn->commit();
        
        // Check if booking status has changed to one of the notification triggers
        $notify_statuses = ['Confirmed', 'Completed', 'Cancelled'];
        
        if ($booking_status !== null && $previous_status != $booking_status && in_array($booking_status, $notify_statuses)) {
            error_log("Booking status changed from {$previous_status} to {$booking_status}. Sending email notification.");
            
            // Get booking details for email
            $email_data_query = "SELECT 
                b.booking_id,
                p.pet_name,
                p.pet_id,
                DATE_FORMAT(b.booking_check_in, '%M %d, %Y') as formatted_check_in,
                DATE_FORMAT(b.booking_check_out, '%M %d, %Y') as formatted_check_out,
                c.c_first_name,
                c.c_last_name,
                c.c_email,
                s.service_name
                FROM bookings b
                JOIN pet p ON b.pet_id = p.pet_id
                JOIN customer c ON p.customer_id = c.c_id
                JOIN service s ON b.service_id = s.service_id
                WHERE b.booking_id = ?";
            
            $email_stmt = $conn->prepare($email_data_query);
            $email_stmt->execute([$booking_id]);
            $booking_data = $email_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($booking_data) {
                error_log("Email data: " . print_r($booking_data, true));
                
                // Send email directly using PHP mail function
                $to = $booking_data['c_email'];
                $subject = "Your Pet Booking Has Been " . $booking_status;
                
                // Create a simple HTML email
                $message = "
                <html>
                <head>
                    <title>Booking Update</title>
                </head>
                <body>
                    <h2>Adorafur Pet Hotel</h2>
                    <p>Hi " . $booking_data['c_first_name'] . " " . $booking_data['c_last_name'] . "!</p>
                    <p>Your pet <strong>" . $booking_data['pet_name'] . "</strong>'s booking has been <strong>" . strtolower($booking_status) . "</strong>.</p>
                    <p><strong>Booking Details:</strong></p>
                    <ul>
                        <li>Pet: " . $booking_data['pet_name'] . "</li>
                        <li>Service: " . $booking_data['service_name'] . "</li>
                        <li>Check-in: " . $booking_data['formatted_check_in'] . "</li>
                        <li>Check-out: " . $booking_data['formatted_check_out'] . "</li>
                    </ul>
                    <p>Thank you for choosing Adorafur Pet Hotel!</p>
                </body>
                </html>
                ";
                
                // Set content-type header for sending HTML email
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= "From: Adorafur Pet Hotel <notifications@adorafur.com>" . "\r\n";
                
                // Send email
                $mail_result = mail($to, $subject, $message, $headers);
                error_log("Mail result: " . ($mail_result ? 'Success' : 'Failed'));
            } else {
                error_log("Could not find booking data for email notification");
            }
        }
        
        // Return success response
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        
        error_log("Database error: " . $e->getMessage());
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'WHAT THE FUCK R U DOING? ' . $e->getMessage()]);
    }
} else {
    // Not a POST request
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
exit();
?>