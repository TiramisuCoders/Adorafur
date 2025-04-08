<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Set headers to prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: application/json');

// Include database connection
require_once '../connect.php';
session_start();

try {
    // Ensure admin ID is retrieved from session
    $admin_id = $_SESSION['admin_id'] ?? null;
    if (!$admin_id) {
        throw new Exception("Unauthorized access. Please log in.");
    }

    // Get date range from request
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;

    if (!$start_date || !$end_date) {
        throw new Exception("Start date and end date are required.");
    }

    // Fetch bookings for the specified date range
    $query = "SELECT b.booking_id, b.booking_status, 
                     b.booking_check_in, b.booking_check_out,
                     p.pet_name, 
                     s.service_name, s.service_variant
              FROM bookings b
              JOIN pet p ON b.pet_id = p.pet_id
              JOIN service s ON b.service_id = s.service_id
              WHERE b.admin_id = :admin_id 
              AND DATE(b.booking_check_in) BETWEEN :start_date AND :end_date
              ORDER BY b.booking_check_in ASC";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $stmt->bindParam(':start_date', $start_date, PDO::PARAM_STR);
    $stmt->bindParam(':end_date', $end_date, PDO::PARAM_STR);
    $stmt->execute();
    
    // Group bookings by date
    $bookings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Format the check-in and check-out times
        $checkInDateTime = new DateTime($row['booking_check_in']);
        $checkOutDateTime = new DateTime($row['booking_check_out']);
        
        $row['formatted_check_in_time'] = $checkInDateTime->format('g:i A');
        $row['formatted_check_out_time'] = $checkOutDateTime->format('g:i A');
        
        // Get the date part only for grouping
        $bookingDate = $checkInDateTime->format('Y-m-d');
        
        // Initialize the array for this date if it doesn't exist
        if (!isset($bookings[$bookingDate])) {
            $bookings[$bookingDate] = [];
        }
        
        // Add the booking to the appropriate date
        $bookings[$bookingDate][] = $row;
    }
    
    echo json_encode([
        "success" => true, 
        "data" => $bookings
    ]);

} catch (Exception $e) {
    error_log("Error in fetch_bookings.php: " . $e->getMessage());
    echo json_encode([
        "success" => false, 
        "error" => $e->getMessage()
    ]);
}
?>
