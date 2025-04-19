<?php
require_once 'connect.php'; // Include database connection

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Default response
$response = [
    'success' => false,
    'available_slots' => 0,
    'message' => 'No date provided',
    'max_slots' => 10 // Added max slots to the response
];

// Check if required parameters are provided
if (isset($_POST['action']) && $_POST['action'] === 'get_hotel_slots' && 
    isset($_POST['check_in_date']) && isset($_POST['check_out_date'])) {
    
    $check_in_date = $_POST['check_in_date'];
    $check_out_date = $_POST['check_out_date'];
    
    try {
        // Query to count bookings for the specified date range for hotel service
        $stmt = $conn->prepare("SELECT COUNT(*) AS booking_count
            FROM bookings
            WHERE 
                ((booking_check_in <= ? AND booking_check_out >= ?) 
                OR (booking_check_in <= ? AND booking_check_out >= ?)
                OR (booking_check_in >= ? AND booking_check_out <= ?))
                AND booking_status = 'Confirmed'
                AND service_id IN (
                    SELECT service_id FROM service WHERE service_name = 'Pet Hotel'
                )");
        
        $stmt->execute([
            $check_out_date, $check_in_date,  // Scenario 1: Booking spans our entire stay
            $check_in_date, $check_in_date,   // Scenario 2: Booking starts before and ends during our stay
            $check_in_date, $check_out_date   // Scenario 3: Booking is entirely within our stay
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Define maximum slots
        $max_slots = 10;
        
        if ($result) {
            $booked_slots = (int)$result['booking_count'];
            $available_slots = max(0, $max_slots - $booked_slots);
            
            $response = [
                'success' => true,
                'available_slots' => $available_slots,
                'message' => 'Bookings retrieved successfully',
                'max_slots' => $max_slots
            ];
        } else {
            $response = [
                'success' => true,
                'available_slots' => $max_slots,
                'message' => 'No bookings found',
                'max_slots' => $max_slots
            ];
        }
        
        // Uncomment this and comment out the above code block if your database isn't ready yet
        /*
        // For testing purposes, return a random number of available slots
        $randomBookings = rand(0, 10);
        $available_slots = max(0, 10 - $randomBookings);
        
        $response = [
            'success' => true,
            'available_slots' => $available_slots,
            'message' => 'Bookings retrieved successfully',
            'max_slots' => 10
        ];
        */
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage(),
            'available_slots' => 0,
            'max_slots' => 10
        ];
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
