<?php
require '../connect.php';

$booking_id = $_POST['booking_id'];
$amount = $_POST['payment_amount'];
$method = $_POST['payment_method'];

$payment_id = uniqid('pay_');

try {
    $stmt = $pdo->prepare("INSERT INTO payments (payment_id, booking_id, amount, method) VALUES (:payment_id, :booking_id, :amount, :method)");
    $stmt->execute([
        ':payment_id' => $payment_id,
        ':booking_id' => $booking_id,
        ':amount' => $amount,
        ':method' => $method
    ]);
    echo "payment_saved";
} catch (PDOException $e) {
    echo "error: " . $e->getMessage();
}
?>
