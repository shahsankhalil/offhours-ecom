<?php
// PHP Database Configuration for OffHour Watch Store
// Connects to MySQL (Default XAMPP settings) using PDO

$host = 'localhost';
$db_name = 'offhour_db';
$username = 'root';
$password = ''; // Default XAMPP has no password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>
