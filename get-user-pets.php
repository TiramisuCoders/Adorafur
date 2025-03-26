<?php
    require_once "connect.php"; // Include database connection

    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    $isLoggedIn = isset($_SESSION["c_id"]);
    if (!$isLoggedIn && !isset($_POST["c_id"])) {
        echo json_encode([
            "success" => false,
            "message" => "User not logged in"
        ]);
        exit;
    }

    // Get client ID from session or POST
    $clientId = isset($_POST["c_id"]) ? $_POST["c_id"] : $_SESSION["c_id"];

    try {
        $stmt = $conn->prepare("SELECT pet_id, pet_name, pet_breed, pet_age, pet_gender, pet_size 
                               FROM pet 
                               WHERE customer_id = :customer_id");
        $stmt->bindParam(":customer_id", $clientId, PDO::PARAM_INT);
        $stmt->execute();
        
        $pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            "success" => true,
            "pets" => $pets
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
    ?>