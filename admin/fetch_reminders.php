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
    $admin_id = $_SESSION['admin_id'] ?? null;
    if (!$admin_id) {
        throw new Exception("Unauthorized access. Please log in.");
    }

    // Fetch latest reminders and tasks
    $query = "SELECT activity_date, activity_time, activity_description, activity_type 
              FROM Admin_Activities_Reminders 
              WHERE admin_id = ? 
              ORDER BY activity_date ASC, activity_time ASC";

    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception("Database error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "i", $admin_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Query execution failed: " . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);

    $activities = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Format the date and time for display
        $dateObj = new DateTime($row['activity_date']);
        $row['formatted_date'] = $dateObj->format('M d, Y');
        
        $timeObj = new DateTime($row['activity_time']);
        $row['formatted_time'] = $timeObj->format('g:i A');
        
        $activities[] = $row;
    }

    mysqli_stmt_close($stmt);
    
    echo json_encode([
        "success" => true, 
        "data" => $activities,
        "count" => count($activities)
    ]);

} catch (Exception $e) {
    error_log("Error in fetch_reminders.php: " . $e->getMessage());
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