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
    $paymentType = $_POST["payment_type"] ?? "full"; // Get payment type (full or down)
    
    // Debug information
    error_log("Transaction ID: " . print_r($transactionId, true));
    error_log("POST data: " . print_r($_POST, true));
    
    // Get visible pets data
    $visiblePets = isset($_POST["visible_pets"]) ? json_decode($_POST["visible_pets"], true) : [];
    
    // If visible_pets is not set, try to use the booking_data pets
    if (empty($visiblePets) && isset($bookingData["pets"])) {
        $visiblePets = $bookingData["pets"];
    }

    if (empty($visiblePets) || !is_array($visiblePets) || count($visiblePets) < 1) {
        echo json_encode([
            "success" => false,
            "message" => "No pet selected for booking"
        ]);
        exit;
    }
    
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

        // Determine service type and payment category based on the data structure
        $serviceName = "Pet Hotel"; // Default to Pet Hotel
        $payCategory = "Booking Fee"; // Default payment category that works with your DB constraint
        
        // Check if this is a daycare booking based on the data structure
        if (isset($bookingData["date"]) && isset($bookingData["dropOffTime"]) && isset($bookingData["pickUpTime"])) {
            $serviceName = "Pet Daycare";
            // Keep the payment category as "Booking Fee" to avoid constraint violation
        }

        // Calculate number of days between check-in and check-out
        $numberOfDays = 1; // Default to 1 day
        if (isset($bookingData["checkInDate"]) && isset($bookingData["checkOutDate"])) {
            $checkIn = new DateTime($bookingData["checkInDate"]);
            $checkOut = new DateTime($bookingData["checkOutDate"]);
            $interval = $checkIn->diff($checkOut);
            $numberOfDays = $interval->days + 1; // Add 1 because the check-out day is counted
            if ($numberOfDays < 1) $numberOfDays = 1;
        }

        // Process each pet in the booking
        foreach ($visiblePets as $pet) {
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
            $serviceStmt = $conn->prepare("SELECT service_id FROM service WHERE service_name = :service_name AND service_variant = :service_variant LIMIT 1");
            $serviceStmt->bindParam(":service_name", $serviceName);
                    
            // Map pet size to service variant
            $serviceVariant = "";
            switch($pet["size"]) {
                case 'Cat':
                    $serviceVariant = "Cat";
                    break;
                case 'Small':
                    $serviceVariant = "Small";
                    break;
                case 'Regular':
                    $serviceVariant = "Regular";
                    break;
                case 'Large':
                    $serviceVariant = "Large";
                    break;
                default:
                    $serviceVariant = "Regular"; // Default if not matched
            }
            
            $serviceStmt->bindParam(":service_variant", $serviceVariant);
            $serviceStmt->execute();
            $serviceResult = $serviceStmt->fetch(PDO::FETCH_ASSOC);
                    
            if (!$serviceResult) {
                throw new Exception("Service not found for: " . $serviceVariant);
            }
                    
            $serviceId = $serviceResult["service_id"];
            
            // Format date and times based on the service type
            if ($serviceName == "Pet Daycare") {
                // For daycare, check-in and check-out are on the same day
                $bookingDate = date("Y-m-d", strtotime($bookingData["date"]));
                $checkInDateTime = $bookingDate . " " . date("H:i:s", strtotime($bookingData["dropOffTime"]));
                $checkOutDateTime = $bookingDate . " " . date("H:i:s", strtotime($bookingData["pickUpTime"]));
            } else {
                // For hotel, check-in and check-out can be on different days
                $checkInDateTime = date("Y-m-d", strtotime($bookingData["checkInDate"])) . " " . 
                                date("H:i:s", strtotime($bookingData["checkInTime"]));
                $checkOutDateTime = date("Y-m-d", strtotime($bookingData["checkOutDate"])) . " " . 
                                date("H:i:s", strtotime($bookingData["checkOutTime"]));
            }
            
            // Calculate total amount for this pet (price * number of days)
            $petTotalAmount = $pet["price"] * $numberOfDays;
                    
            $bookingStmt = $conn->prepare("INSERT INTO bookings 
                            (pet_id, service_id, admin_id, booking_status, booking_check_in, booking_check_out, 
                            booking_total_amount, booking_balance, transaction_id) 
                            VALUES 
                            (:pet_id, :service_id, 1, 'Pending', :check_in, :check_out, 
                            :booking_amount, :booking_balance, :transaction_id)");
       
            // Calculate booking balance based on payment type
            $bookingBalance = ($paymentType === "down") ? ($petTotalAmount * 0.5) : 0;
            
            $bookingStmt->bindParam(":pet_id", $petId, PDO::PARAM_INT);
            $bookingStmt->bindParam(":service_id", $serviceId, PDO::PARAM_INT);
            $bookingStmt->bindParam(":check_in", $checkInDateTime);
            $bookingStmt->bindParam(":check_out", $checkOutDateTime);
            $bookingStmt->bindParam(":booking_amount", $petTotalAmount);
            $bookingStmt->bindParam(":booking_balance", $bookingBalance);
            $bookingStmt->bindParam(":transaction_id", $transactionId);
                       
            $bookingStmt->execute();
            $bookingId = $conn->lastInsertId();
            
            // Record the booking ID for the first pet (for payment association)
            if (!isset($firstBookingId)) {
                $firstBookingId = $bookingId;
            }
        }

        // Create a single payment record for all pets
        $stmt = $conn->prepare("INSERT INTO payment (customer_id, admin_id, pay_method, pay_reference_number, pay_category, 
                pay_amount, pay_status, pay_date, proof_of_payment, booking_id) 
                VALUES (:customer_id, 1, :pay_method, :reference_no, :pay_category, 
                :pay_amount, :pay_status, NOW(), :payment_proof, :booking_id)");

        // Calculate total amount for all pets
        $totalAmount = 0;
        foreach ($visiblePets as $pet) {
            $totalAmount += $pet["price"] * $numberOfDays;
        }

        // Calculate payment amount and status based on payment type
        $payAmount = $totalAmount;
        $payStatus = "Fully Paid";
        
        if ($paymentType === "down") {
            $payAmount = $totalAmount * 0.5;
            $payStatus = "Down Payment"; // Use "Down Payment" instead of "Partially Paid"
        }

        $stmt->bindParam(":customer_id", $customerId, PDO::PARAM_INT);
        $stmt->bindParam(":pay_method", $paymentMethod);
        $stmt->bindParam(":reference_no", $referenceNo);
        $stmt->bindParam(":pay_category", $payCategory);
        $stmt->bindParam(":pay_amount", $payAmount);
        $stmt->bindParam(":pay_status", $payStatus);
        $stmt->bindParam(":payment_proof", $paymentProofPath);
        $stmt->bindParam(":booking_id", $firstBookingId); // Use the first booking ID for payment

        $stmt->execute();
        $paymentId = $conn->lastInsertId();

        // Commit transaction
        $conn->commit();
        
        // Return success response with both IDs
        echo json_encode([
            "success" => true,
            "message" => "Booking completed successfully",
            "payment_id" => $paymentId,
            "booking_id" => $firstBookingId
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        
        echo json_encode([
            "success" => false,
            "message" => "Error: " . $e->getMessage()
        ]);
    }
    
    exit; // Exit after sending the response
}

// If we get here, it means the request was not for booking completion
echo json_encode([
    "success" => false,
    "message" => "Invalid request"
]);
?>
