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
    
    $description = trim($_POST['description'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $time = trim($_POST['time'] ?? '');
    $type = trim($_POST['type'] ?? '');

    error_log("Received data: " . json_encode($_POST));

    if (empty($description) || empty($date) || empty($time) || empty($type)) {
        throw new Exception('All fields are required');
    }

    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $date) {
        throw new Exception('Invalid date format. Use YYYY-MM-DD');
    }

    $timeObj = DateTime::createFromFormat('H:i', $time);
    if (!$timeObj || $timeObj->format('H:i') !== $time) {
        throw new Exception('Invalid time format. Use HH:MM');
    }

    $type = ucfirst(strtolower($type));
    if (!in_array($type, ['Task', 'Reminder'])) {
        throw new Exception('Invalid activity type. Must be Task or Reminder');
    }

    // Using PDO prepared statement with named parameters
    $sql = "INSERT INTO Admin_Activities_Reminders 
            (admin_id, activity_date, activity_time, activity_description, activity_type) 
            VALUES (:admin_id, :date, :time, :description, :type)";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters using named parameters
    $stmt->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->bindParam(':time', $time, PDO::PARAM_STR);
    $stmt->bindParam(':description', $description, PDO::PARAM_STR);
    $stmt->bindParam(':type', $type, PDO::PARAM_STR);
    
    // Execute the statement
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception('Failed to insert record into database');
    }

    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;

} catch (Exception $e) {
    error_log("Error in add_activity.php: " . $e->getMessage());
    
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}
?>