<?php
// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['c_id'])) {
    // Redirect to login page if not logged in
    header("Location: index.php");
    exit();
}

// Include database connection
include("connect.php");

// Check if booking_id is set and is a valid integer
if (isset($_POST['booking_id']) && !empty($_POST['booking_id']) && is_numeric($_POST['booking_id'])) {
    $booking_id = (int)$_POST['booking_id']; // Cast to integer to ensure it's a valid integer
    $customer_id = $_SESSION['c_id'];
    
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // First, verify that this booking belongs to the logged-in user
        $verify_query = "SELECT b.* FROM bookings b 
                        JOIN pet p ON b.pet_id = p.pet_id 
                        WHERE b.booking_id = :booking_id 
                        AND p.customer_id = :customer_id";
        
        $verify_stmt = $conn->prepare($verify_query);
        $verify_stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT); // Explicitly specify integer parameter
        $verify_stmt->bindParam(':customer_id', $customer_id, PDO::PARAM_INT);
        $verify_stmt->execute();
        
        if ($verify_stmt->rowCount() > 0) {
            // Booking belongs to user, proceed with cancellation
            
            // Get cancellation reason if provided
            $reason = isset($_POST['reason']) ? $_POST['reason'] : 'Not specified';
            
            // If reason is "Other" and other_reason is provided, use that instead
            if ($reason === 'Other' && isset($_POST['other_reason']) && !empty($_POST['other_reason'])) {
                $reason = 'Other: ' . $_POST['other_reason'];
            }
            
            // Update booking status to Cancelled
            $update_query = "UPDATE bookings SET 
                            booking_status = 'Cancelled'
                            WHERE booking_id = :booking_id";
            
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT); // Explicitly specify integer parameter
            $update_stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            // Set success message
            // $_SESSION['success_message'] = "Your booking has been successfully cancelled.";
        } else {
            // Booking doesn't belong to user or doesn't exist
            // $_SESSION['error_message'] = "Invalid booking or you don't have permission to cancel this booking.";
        }
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $_SESSION['error_message'] = "Error cancelling booking: " . $e->getMessage();
    }
} else {
    // No valid booking_id provided
    $_SESSION['error_message'] = "Invalid booking ID for cancellation. Please try again.";
    // For debugging
    error_log("Invalid booking_id: " . (isset($_POST['booking_id']) ? $_POST['booking_id'] : 'not set'));
}

// Redirect back to profile page
header("Location: Profile.php");
exit();
?>
