<?php
require_once 'connect.php'; // Include database connection

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to get available slots for a date range and pet variant
function getAvailableSlotsHotel($checkInDate, $checkOutDate, $species = null, $variant = null) {
    global $conn;
    
    // Set maximum slots to 10 overall, regardless of species or variant
    $maxSlots = 10;
    
    try {
        // Base query to count total bookings per date in the range
        $query = "
            SELECT 
                booked_date,
                COUNT(DISTINCT booking_id) AS bookings_count
            FROM (
                SELECT 
                    b.booking_id AS booking_id,
                    d::date AS booked_date,
                    b.service_id
                FROM 
                    bookings b
                CROSS JOIN LATERAL 
                    generate_series(
                        b.booking_check_in, 
                        b.booking_check_out - INTERVAL '1 day', 
                        INTERVAL '1 day'
                    ) AS d
                WHERE 
                    d::date >= :check_in_date AND d::date <= :check_out_date
            ) AS dates_per_booking
            JOIN service s ON s.service_id = dates_per_booking.service_id
            GROUP BY booked_date
            ORDER BY booked_date
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':check_in_date', $checkInDate);
        $stmt->bindParam(':check_out_date', $checkOutDate);
        $stmt->execute();
        
        // Organize bookings by date
        $bookings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $date = $row['booked_date'];
            $count = $row['bookings_count'];
            $bookings[$date] = $count;
        }
        
        // Check each date in the range for availability
        $startDate = new DateTime($checkInDate);
        $endDate = new DateTime($checkOutDate);
        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));
        
        $minAvailable = $maxSlots; // Start with max and find the minimum available
        
        foreach ($dateRange as $date) {
            $dateStr = $date->format('Y-m-d');
            $bookedCount = isset($bookings[$dateStr]) ? $bookings[$dateStr] : 0;
            $available = max(0, $maxSlots - $bookedCount);
            
            if ($available < $minAvailable) {
                $minAvailable = $available;
            }
        }
        
        if ($species && $variant) {
            return $minAvailable;
        }
        
        // Otherwise return all available slots as a single value
        return ['Overall' => $minAvailable];
        
    } catch (PDOException $e) {
        // Log error
        error_log("Error getting available slots for hotel: " . $e->getMessage());
        return [];
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'get_hotel_slots') {
    $checkInDate = $_POST['check_in_date'] ?? null;
    $checkOutDate = $_POST['check_out_date'] ?? null;
    $species = $_POST['species'] ?? null;
    $variant = $_POST['variant'] ?? null;
    
    if (!$checkInDate || !$checkOutDate) {
        echo json_encode(['success' => false, 'message' => 'Check-in and check-out dates are required']);
        exit;
    }
    
    $availableSlots = getAvailableSlotsHotel($checkInDate, $checkOutDate, $species, $variant);
    
    echo json_encode([
        'success' => true,
        'check_in_date' => $checkInDate,
        'check_out_date' => $checkOutDate,
        'available_slots' => $availableSlots
    ]);
    exit;
}
?>
