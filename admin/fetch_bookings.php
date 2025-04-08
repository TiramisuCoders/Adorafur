<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Prevent caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: application/json');

// Include PDO connection
require_once '../connect.php';
session_start();

try {
    // Get admin ID from session or fallback
    $admin_id = $_SESSION['admin_id'] ?? 1;

    // Get start and end date from request
    $start_date = $_GET['start_date'] ?? date('Y-m-d');
    $end_date = $_GET['end_date'] ?? date('Y-m-d', strtotime('+6 days', strtotime($start_date)));

    // Prepare SQL query
    $query = "
        SELECT b.booking_id, b.booking_status, 
               DATE(b.booking_check_in) as check_in_date,
               TIME(b.booking_check_in) as check_in_time,
               DATE(b.booking_check_out) as check_out_date,
               TIME(b.booking_check_out) as check_out_time,
               p.pet_name, p.pet_size, s.service_name, s.service_variant
        FROM bookings b
        JOIN pet p ON b.pet_id = p.pet_id
        JOIN service s ON b.service_id = s.service_id
        WHERE (DATE(b.booking_check_in) BETWEEN :start_date AND :end_date)
           OR (DATE(b.booking_check_out) BETWEEN :start_date AND :end_date)
           OR (DATE(b.booking_check_in) <= :start_date AND DATE(b.booking_check_out) >= :end_date)
        ORDER BY b.booking_check_in ASC
    ";

    $stmt = $conn->prepare($query);

    $stmt->execute([
        ':start_date' => $start_date,
        ':end_date'   => $end_date
    ]);

    $bookings = [];

    while ($row = $stmt->fetch()) {
        $check_in_time = date('g:i A', strtotime($row['check_in_time']));
        $check_out_time = date('g:i A', strtotime($row['check_out_time']));

        $row['formatted_check_in_time'] = $check_in_time;
        $row['formatted_check_out_time'] = $check_out_time;

        $check_in_date = $row['check_in_date'];
        if (!isset($bookings[$check_in_date])) {
            $bookings[$check_in_date] = [];
        }

        $bookings[$check_in_date][] = $row;
    }

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
}
?>
