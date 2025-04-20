<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

require_once '../connect.php';

try {
    // Check if the column already exists
    $checkColumnQuery = "SELECT column_name 
                        FROM information_schema.columns 
                        WHERE table_name = 'admin_activities_reminders' 
                        AND column_name = 'completed'";
    
    $stmt = $conn->prepare($checkColumnQuery);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        // Column doesn't exist, so add it
        $alterTableQuery = "ALTER TABLE Admin_Activities_Reminders 
                           ADD COLUMN completed BOOLEAN DEFAULT FALSE";
        
        $conn->exec($alterTableQuery);
        echo "Success: 'completed' column added to Admin_Activities_Reminders table.";
    } else {
        echo "Info: 'completed' column already exists in Admin_Activities_Reminders table.";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
