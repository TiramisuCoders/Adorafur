<?php
$host = "localhost";
$dbname = "fur_a_paw_intments";
$username = "root";
$password = "";

$conn = mysqli_connect($host, $username, $password, $dbname);

// Add error handling
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

