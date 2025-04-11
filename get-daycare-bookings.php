<?php
require_once 'connect.php'; // Include database connection

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Default response
$response = [
    'success' => false,
    'bookings' => 0,
    'message' => 'No date provided',
    'max_slots' => 10 // Added max slots to the response
];

// Check if date is provided
if (isset($_POST['date']) && !empty($_POST['date'])) {
    $date = $_POST['date'];
    
    try {
        // This is the actual database query you should use when your database is ready
        // Query to count bookings for the specified date for daycare service
        $stmt = $conn->prepare("SELECT COUNT(*) AS booking_count
FROM bookings
WHERE booking_check_in = ?
AND booking_status = 'Confirmed'
  AND service_id IN (
    SELECT service_id FROM service WHERE service_name = 'Pet Daycare'
  );

");
        $stmt->execute([$date]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $response = [
                'success' => true,
                'bookings' => (int)$result['booking_count'],
                'message' => 'Bookings retrieved successfully',
                'max_slots' => 10
            ];
        } else {
            $response = [
                'success' => true,
                'bookings' => 0,
                'message' => 'No bookings found',
                'max_slots' => 10
            ];
        }
        
        // Uncomment this and comment out the above code block if your database isn't ready yet
        /*
        // For testing purposes, return a random number of bookings
        $randomBookings = rand(0, 10);
        
        $response = [
            'success' => true,
            'bookings' => $randomBookings,
            'message' => 'Bookings retrieved successfully',
            'max_slots' => 10
        ];
        */
    } catch (PDOException $e) {
        $response = [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage(),
            'max_slots' => 10
        ];
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
