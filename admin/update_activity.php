<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

ob_start();
session_start();

try {
    require_once '../connect.php';
    
    // Use session admin_id instead of hardcoded value
    $admin_id = $_SESSION['admin_id'] ?? null;
    
    // Check if admin_id exists
    if (!$admin_id) {
        throw new Exception('User not authenticated. Please log in.');
    }
    
    // Get the activity ID and action type from the request
    $activity_id = isset($_POST['activity_id']) ? intval($_POST['activity_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $completed = isset($_POST['completed']) ? filter_var($_POST['completed'], FILTER_VALIDATE_BOOLEAN) : null;
    
    if (empty($activity_id)) {
        throw new Exception('Activity ID is required');
    }
    
    if (empty($action)) {
        throw new Exception('Action is required');
    }
    
    // Debug log
    error_log("Processing {$action} for activity ID: {$activity_id}");
    
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
    
    // For hide action (previously delete), we don't need to verify if it exists first
    if ($action === 'delete' || $action === 'hide') {
        // Debug log
        error_log("Hiding activity ID: $activity_id");
        
        // Hide the activity instead of deleting it
        $sql = "UPDATE Admin_Activities_Reminders 
                SET hidden = TRUE 
                WHERE activity_id = :activity_id AND admin_id = :admin_id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':activity_id', $activity_id, PDO::PARAM_INT);
        $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
        
        // Execute the statement
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("Database error during hide: " . print_r($stmt->errorInfo(), true));
            throw new Exception('Failed to hide the activity');
        }
        
        // Even if no rows were affected, consider it a success
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
    
    // For update action, we need to verify the activity exists
    if ($action === 'update' && $completed !== null) {
        // Verify the activity belongs to the logged-in admin
        $checkQuery = "SELECT * FROM Admin_Activities_Reminders 
                      WHERE activity_id = :activity_id AND admin_id = :admin_id";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindParam(':activity_id', $activity_id, PDO::PARAM_INT);
        $checkStmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() === 0) {
            throw new Exception('Activity not found or you do not have permission to modify it');
        }
        
        // Debug log
        error_log("Updating activity ID: $activity_id, Completed: " . ($completed ? 'true' : 'false'));
        
        // Update the completed status
        $sql = "UPDATE Admin_Activities_Reminders 
                SET completed = :completed
                WHERE activity_id = :activity_id AND admin_id = :admin_id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':activity_id', $activity_id, PDO::PARAM_INT);
        $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
        $stmt->bindParam(':completed', $completed, PDO::PARAM_BOOL);
        
        // Execute the statement
        $result = $stmt->execute();
        
        if (!$result) {
            error_log("Database error during update: " . print_r($stmt->errorInfo(), true));
            throw new Exception('Failed to update the activity');
        }
        
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
    
    // If we get here, the action was invalid
    throw new Exception('Invalid action');
    
} catch (Exception $e) {
    error_log("Error in update_activity.php: " . $e->getMessage());
    
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}
?>
