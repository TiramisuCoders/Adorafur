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

    // Check if the completed column exists, if not, add it
    $checkColumnQuery = "SELECT column_name 
                        FROM information_schema.columns 
                        WHERE table_name = 'admin_activities_reminders' 
                        AND column_name = 'completed'";
    
    $checkStmt = $conn->prepare($checkColumnQuery);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        // Column doesn't exist, so add it
        $alterTableQuery = "ALTER TABLE Admin_Activities_Reminders 
                           ADD COLUMN completed BOOLEAN DEFAULT FALSE";
        
        $conn->exec($alterTableQuery);
        error_log("Added 'completed' column to Admin_Activities_Reminders table");
    }

    // Check if the hidden column exists, if not, add it
    $checkColumnQuery = "SELECT column_name 
                        FROM information_schema.columns 
                        WHERE table_name = 'admin_activities_reminders' 
                        AND column_name = 'hidden'";
    
    $checkStmt = $conn->prepare($checkColumnQuery);
    $checkStmt->execute();
    
    if ($checkStmt->rowCount() === 0) {
        // Column doesn't exist, so add it
        $alterTableQuery = "ALTER TABLE Admin_Activities_Reminders 
                           ADD COLUMN hidden BOOLEAN DEFAULT FALSE";
        
        $conn->exec($alterTableQuery);
        error_log("Added 'hidden' column to Admin_Activities_Reminders table");
    }

    // Fetch latest reminders and tasks using PDO, excluding hidden items
    $query = "SELECT activity_id, activity_date, activity_time, activity_description, activity_type, 
              COALESCE(completed, FALSE) as completed 
              FROM Admin_Activities_Reminders 
              WHERE admin_id = :admin_id 
              AND (hidden IS NULL OR hidden = FALSE)
              ORDER BY activity_date ASC, activity_time ASC";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $activities = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Format the date and time for display
        $dateObj = new DateTime($row['activity_date']);
        $row['formatted_date'] = $dateObj->format('M d, Y');
        
        $timeObj = new DateTime($row['activity_time']);
        $row['formatted_time'] = $timeObj->format('g:i A');
        
        // Convert completed to boolean for JavaScript
        $row['completed'] = (bool)$row['completed'];
        
        $activities[] = $row;
    }
    
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
}
?>
