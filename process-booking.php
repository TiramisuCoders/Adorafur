<?php
require_once "connect.php"; // Include database connection

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION["c_id"]);
if (!$isLoggedIn) {
    echo json_encode([
        "success" => false,
        "message" => "User not logged in"
    ]);
    exit;
}

// Process booking completion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["complete_booking"])) {
    $customerId = $_SESSION["c_id"];
    $bookingData = json_decode($_POST["booking_data"], true);
    $paymentMethod = $_POST["payment_method"];
    $referenceNo = $_POST["reference_no"];
    $transactionId = $_POST["transaction_id"] ?? null; // Get transaction ID if provided
    
    // Get visible pets data
    $visiblePets = json_decode($_POST["visible_pets"], true);

    if (empty($visiblePets) || !is_array($visiblePets) || count($visiblePets) < 1) {
        echo json_encode([
            "success" => false,
            "message" => "No pet selected for booking"
        ]);
        exit;
    }
    
    // Get the first pet from the array
    $pet = $visiblePets[0];
    
    // Handle file upload for payment proof
    $paymentProofPath = "";
    if (isset($_FILES["payment_proof"]) && $_FILES["payment_proof"]["error"] == 0) {
        $targetDir = "uploads/payment_proofs/";
        
        // Create directory if it doesn't exist
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = time() . "_" . basename($_FILES["payment_proof"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        
        // Upload file
        if (move_uploaded_file($_FILES["payment_proof"]["tmp_name"], $targetFilePath)) {
            $paymentProofPath = $targetFilePath;
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Failed to upload payment proof"
            ]);
            exit;
        }
    }
    
    try {
        // Start transaction
        $conn->beginTransaction();

        $petStmt = $conn->prepare("SELECT pet_id FROM pet WHERE pet_name = :pet_name AND customer_id = :customer_id LIMIT 1");
                $petStmt->bindParam(":pet_name", $pet["name"]);
                $petStmt->bindParam(":customer_id", $customerId, PDO::PARAM_INT);
                $petStmt->execute();
                $petResult = $petStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$petResult) {
                    throw new Exception("Pet not found: " . $pet["name"]);
                }
                
                $petId = $petResult["pet_id"];
                
                // Get service ID based on pet size
                $serviceStmt = $conn->prepare("SELECT service_id FROM service WHERE service_name = 'Pet Hotel' AND service_variant = :service_variant LIMIT 1");
                
                // Map pet size to service variant
                $serviceVariant = "";
                switch($pet["size"]) {
                    case 'Cat':
                        $serviceVariant = "Cats";
                        break;
                    case 'Small':
                        $serviceVariant = "Small Dog";
                        break;
                    case 'Regular':
                        $serviceVariant = "Medium Dog";
                        break;
                    case 'Large':
                        $serviceVariant = "Large Dog";
                        break;
                    default:
                        $serviceVariant = "Medium Dog"; // Default if not matched
                }
        
                $serviceStmt->bindParam(":service_variant", $serviceVariant);
                $serviceStmt->execute();
                $serviceResult = $serviceStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$serviceResult) {
                    throw new Exception("Service not found for: " . $serviceVariant);
                }
                
                $serviceId = $serviceResult["service_id"];
                
                // Format check-in and check-out dates
                $checkInDate = date("Y-m-d", strtotime($bookingData["checkInDate"])) . " " . 
                               date("H:i:s", strtotime($bookingData["checkInTime"]));
                
                $checkOutDate = date("Y-m-d", strtotime($bookingData["checkOutDate"])) . " " . 
                                date("H:i:s", strtotime($bookingData["checkOutTime"]));
                
        
                                $bookingStmt = $conn->prepare("INSERT INTO bookings 
                                (pet_id, service_id, admin_id, booking_status, booking_check_in, booking_check_out, 
                                booking_total_amount, booking_balance, transaction_id) 
                                VALUES 
                                (:pet_id, :service_id, 1, 'Pending', :check_in, :check_out, 
                                :booking_amount, :booking_amount, :transaction_id)");
   
                   $bookingStmt->bindParam(":pet_id", $petId, PDO::PARAM_INT);
                   $bookingStmt->bindParam(":service_id", $serviceId, PDO::PARAM_INT);
                   $bookingStmt->bindParam(":check_in", $checkInDate);
                   $bookingStmt->bindParam(":check_out", $checkOutDate);
                   $bookingStmt->bindParam(":booking_amount", $pet["price"]);
                   $bookingStmt->bindParam(":transaction_id", $transactionId);
                   
                   $bookingStmt->execute();

                   $bookingId = $conn->lastInsertId();

                   $stmt = $conn->prepare("INSERT INTO payment (customer_id, admin_id, pay_method, pay_reference_number, pay_category, 
                       pay_amount, pay_status, pay_date, proof_of_payment, booking_id) 
                       VALUES (:customer_id, 1, :pay_method, :reference_no, 'Booking Fee', 
                       :pay_amount, 'Fully Paid', NOW(), :payment_proof, :booking_id)");

$totalAmount = 0;
foreach ($visiblePets as $pet) {
    $totalAmount += $pet["price"];
}

$stmt->bindParam(":customer_id", $customerId, PDO::PARAM_INT);
$stmt->bindParam(":pay_method", $paymentMethod);
$stmt->bindParam(":reference_no", $referenceNo);
$stmt->bindParam(":pay_amount", $totalAmount);
$stmt->bindParam(":payment_proof", $paymentProofPath);
$stmt->bindParam(":booking_id", $bookingId); // Use the booking ID we just created

$stmt->execute();
$paymentId = $conn->lastInsertId();

// Commit transaction
$conn->commit();
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollBack();
    
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}   

exit;
}
// Return success response with both IDs
echo json_encode([
    "success" => true,
    "message" => "Booking completed successfully",
    "payment_id" => $paymentId,
    "booking_id" => $bookingId
]);
?>
