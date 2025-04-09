<?php
$host = "aws-0-ap-southeast-1.pooler.supabase.com";
$dbname = "postgres";
$username = "postgres.ygbwanzobuielhttdzsw";
$password = "ad0r4fur-PAW-intments";

try{
    $conn = new PDO("pgsql:host=$host;port=5432;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

?>
