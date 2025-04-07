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
require_once 'connect.php';
session_start();

try {
    // Ensure admin ID is retrieved from session
    $admin_id = $_SESSION['admin_id'] ?? 1; // Fallback to 1 for testing
    
    // Get start and end dates from request
    $start_date = $_GET['start_date'] ?? date('Y-m-d');
    $end_date = $_GET['end_date'] ?? date('Y-m-d', strtotime('+6 days', strtotime($start_date)));
    
    // Fetch bookings for the date range
    $query = "SELECT b.booking_id, b.booking_status, 
                     DATE(b.booking_check_in) as check_in_date,
                     TIME(b.booking_check_in) as check_in_time,
                     DATE(b.booking_check_out) as check_out_date,
                     TIME(b.booking_check_out) as check_out_time,
                     p.pet_name, p.pet_size, s.service_name, s.service_variant
              FROM bookings b
              JOIN pet p ON b.pet_id = p.pet_id
              JOIN service s ON b.service_id = s.service_id
              WHERE (DATE(b.booking_check_in) BETWEEN ? AND ?) 
                 OR (DATE(b.booking_check_out) BETWEEN ? AND ?)
                 OR (DATE(b.booking_check_in) <= ? AND DATE(b.booking_check_out) >= ?)
              ORDER BY b.booking_check_in ASC";
    
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception("Database error: " . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "ssssss", $start_date, $end_date, $start_date, $end_date, $start_date, $end_date);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Query execution failed: " . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    
    $bookings = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Format dates for display
        $check_in_time = date('g:i A', strtotime($row['check_in_time']));
        $check_out_time = date('g:i A', strtotime($row['check_out_time']));
        
        // Add formatted data
        $row['formatted_check_in_time'] = $check_in_time;
        $row['formatted_check_out_time'] = $check_out_time;
        
        // Group bookings by date
        $check_in_date = $row['check_in_date'];
        if (!isset($bookings[$check_in_date])) {
            $bookings[$check_in_date] = [];
        }
        $bookings[$check_in_date][] = $row;
    }
    
    mysqli_stmt_close($stmt);
    
    echo json_encode([
        "success" => true, 
        "data" => $bookings,
        "start_date" => $start_date,
        "end_date" => $end_date
    ]);
    
} catch (Exception $e) {
    error_log("Error in fetch_bookings.php: " . $e->getMessage());
    echo json_encode([
        "success" => false, 
        "error" => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?>

