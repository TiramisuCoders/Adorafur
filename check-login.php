<?php
    // Start session if not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    $isLoggedIn = isset($_SESSION["c_id"]);
    $response = [
        "isLoggedIn" => $isLoggedIn
    ];

    if ($isLoggedIn) {
        $response["c_id"] = $_SESSION["c_id"];
    }

    // Return JSON response
    header("Content-Type: application/json");
    echo json_encode($response);
    ?>