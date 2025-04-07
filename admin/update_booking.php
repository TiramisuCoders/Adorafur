<?php
require '../connect.php'; // your db connection file with PDO

$booking_id = $_POST['booking_id'];
$booking_status = $_POST['booking_status'];

try {
    $stmt = $pdo->prepare("UPDATE bookings SET status = :status WHERE id = :id");
    $stmt->execute([
        ':status' => $booking_status,
        ':id' => $booking_id
    ]);
    echo "success";
} catch (PDOException $e) {
    echo "error: " . $e->getMessage();
}
?>