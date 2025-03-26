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

    // Get client ID from session or POST
    $clientId = isset($_POST["c_id"]) ? $_POST["c_id"] : $_SESSION["c_id"];

    try {
        $stmt = $conn->prepare("SELECT c_first_name, c_last_name, c_email 
                               FROM customer 
                               WHERE c_id = :customer_id");
        $stmt->bindParam(":customer_id", $clientId, PDO::PARAM_INT);
        $stmt->execute();
        
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($customer) {
            echo json_encode([
                "success" => true,
                "client_name" => $customer["c_first_name"] . " " . $customer["c_last_name"],
                "client_email" => $customer["c_email"]
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Customer not found"
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
    ?>