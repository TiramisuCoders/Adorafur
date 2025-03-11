<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include("connect.php");

if (!isset($_SESSION['c_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['c_id'];

if (isset($_GET['pet_id'])) {
    $pet_id = $_GET['pet_id'];
    
    // Verify pet belongs to user
    $query = "SELECT * FROM pet WHERE pet_id = :pet_id AND customer_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':pet_id', $pet_id);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $pet_data = $stmt->fetch(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($pet_data);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Pet not found or access denied']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No pet ID provided']);
}
?>