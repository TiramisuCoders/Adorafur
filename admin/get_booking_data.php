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

if (isset($_GET['booking_id'])) {
    try {
        $booking_id = $_GET['booking_id'];
        
        // Get comprehensive booking data including balance
        $stmt = $conn->prepare("
            SELECT 
                b.booking_id,
                b.booking_status,
                b.booking_check_in,
                b.booking_check_out,
                b.booking_balance,
                b.total_amount,
                p.pet_name,
                p.pet_breed,
                p.pet_size,
                s.service_name,
                CONCAT(c.c_first_name, ' ', c.c_last_name) AS owner_name,
                c.c_contact_number,
                pay.pay_status,
                pay.pay_method,
                pay.pay_reference_number
            FROM bookings b
            JOIN pet p ON b.pet_id = p.pet_id
            JOIN customer c ON p.customer_id = c.c_id
            JOIN service s ON b.service_id = s.service_id
            LEFT JOIN (
                SELECT DISTINCT ON (booking_id)
                    *
                FROM payment
                ORDER BY booking_id, pay_date DESC
            ) pay ON b.booking_id = pay.booking_id
            WHERE b.booking_id = :booking_id
        ");
        
        $stmt->bindParam(':booking_id', $booking_id);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($booking) {
            $response = array_merge($response, $booking);
            $response['success'] = true;
        } else {
            $response['message'] = 'Booking not found';
        }
        
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Booking ID is required';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit();
?>
