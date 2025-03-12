<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
include("../connect.php"); // Ensure this connects properly

// Get today's date
$currentDate = date('Y-m-d');

// Prepare SQL query to fetch reminders and tasks
$sql = "SELECT 
            activity_id, 
            admin_id, 
            activity_date, 
            activity_time, 
            activity_description, 
            activity_type
        FROM admin_activities_reminders
        WHERE activity_date >= ?
        ORDER BY activity_date, activity_time"; // Sort by date and time

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentDate);
$stmt->execute();
$result = $stmt->get_result();

// Check for errors
if (!$result) {
    header("Content-Type: application/json");
    die(json_encode(["error" => "Error fetching data: " . $conn->error]));
}

// Fetch all rows into an array
$reminders = [];
while ($row = $result->fetch_assoc()) {
    // Format the date for display
    $row['formatted_date'] = date('F j, Y', strtotime($row['activity_date']));
    $reminders[] = $row;
}

// Return JSON data
header("Content-Type: application/json");
echo json_encode($reminders);
exit;
?>

