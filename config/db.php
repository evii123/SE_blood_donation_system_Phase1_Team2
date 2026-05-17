<?php
$host = 'localhost';
$db_name = 'blood_donation_system';
$db_user = 'root';
$db_pass = '';

try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo 'Connection Error: ' . $e->getMessage();
}
?>