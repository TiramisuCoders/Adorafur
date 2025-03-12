<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to prevent any unwanted output
ob_start();
session_start();

try {
    // Include database connection
    require_once '../connect.php'; // Make sure path is correct

    // Log incoming data for debugging
    error_log("POST data: " . print_r($_POST, true));

    // Validate and sanitize inputs
    $admin_id = 1; // Replace with actual admin ID from session if available
    $description = trim($_POST['description'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $type = trim($_POST['type'] ?? '');

    // Ensure all fields are provided
    if (empty($description) || empty($date) || empty($time) || empty($type)) {
        throw new Exception('All fields are required');
    }

    // Convert type to proper case
    $type = ucfirst(strtolower($type));
    if (!in_array($type, ['Task', 'Reminder'])) {
        throw new Exception('Invalid activity type');
    }

    // Insert into the database
    $stmt = $conn->prepare("
        INSERT INTO admin_activities_reminders 
        (admin_id, activity_date, activity_time, activity_description, activity_type) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    if (!$stmt) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }
    
    $stmt->bind_param("issss", $admin_id, $date, $time, $description, $type);
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception('Database error: ' . $conn->error);
    }

    // Clear any output and send JSON response
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Activity added successfully']);
    exit;

} catch (Exception $e) {
    // Log and return error
    error_log("Error in add_activity.php: " . $e->getMessage());

    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
?>

