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
            
            // 1. Create booking record
            $stmt = $conn->prepare("INSERT INTO booking (customer_id, check_in_date, check_in_time, check_out_date, check_out_time, payment_method, reference_no, payment_proof, status, created_at) 
                                   VALUES (:customer_id, :check_in_date, :check_in_time, :check_out_date, :check_out_time, :payment_method, :reference_no, :payment_proof, 'confirmed', NOW())");
            
            $checkInDate = date("Y-m-d", strtotime($bookingData["checkInDate"]));
            $checkOutDate = date("Y-m-d", strtotime($bookingData["checkOutDate"]));
            
            $stmt->bindParam(":customer_id", $customerId, PDO::PARAM_INT);
            $stmt->bindParam(":check_in_date", $checkInDate);
            $stmt->bindParam(":check_in_time", $bookingData["checkInTime"]);
            $stmt->bindParam(":check_out_date", $checkOutDate);
            $stmt->bindParam(":check_out_time", $bookingData["checkOutTime"]);
            $stmt->bindParam(":payment_method", $paymentMethod);
            $stmt->bindParam(":reference_no", $referenceNo);
            $stmt->bindParam(":payment_proof", $paymentProofPath);
            
            $stmt->execute();
            $bookingId = $conn->lastInsertId();
            
            // 2. Add booking details for each pet
            if (!empty($bookingData["pets"])) {
                $stmt = $conn->prepare("INSERT INTO booking_details (booking_id, pet_id, pet_name, pet_size, price) 
                                       VALUES (:booking_id, :pet_id, :pet_name, :pet_size, :price)");
                
                foreach ($bookingData["pets"] as $pet) {
                    // Get pet ID from name
                    $petStmt = $conn->prepare("SELECT pet_id FROM pet WHERE pet_name = :pet_name AND customer_id = :customer_id LIMIT 1");
                    $petStmt->bindParam(":pet_name", $pet["name"]);
                    $petStmt->bindParam(":customer_id", $customerId, PDO::PARAM_INT);
                    $petStmt->execute();
                    $petResult = $petStmt->fetch(PDO::FETCH_ASSOC);
                    
                    $petId = $petResult ? $petResult["pet_id"] : 0;
                    
                    $stmt->bindParam(":booking_id", $bookingId, PDO::PARAM_INT);
                    $stmt->bindParam(":pet_id", $petId, PDO::PARAM_INT);
                    $stmt->bindParam(":pet_name", $pet["name"]);
                    $stmt->bindParam(":pet_size", $pet["size"]);
                    $stmt->bindParam(":price", $pet["price"]);
                    
                    $stmt->execute();
                }
            }
            
            // Commit transaction
            $conn->commit();
            
            // Return success response
            echo json_encode([
                "success" => true,
                "message" => "Booking completed successfully",
                "booking_id" => $bookingId
            ]);
            
        } catch (PDOException $e) {
            // Rollback transaction on error
            $conn->rollBack();
            
            echo json_encode([
                "success" => false,
                "message" => "Database error: " . $e->getMessage()
            ]);
        }   
        
        exit;
    }

    // Default response for invalid requests
    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);
    ?>