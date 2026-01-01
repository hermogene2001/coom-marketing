<?php
// Database connection file

// Database configuration
$host = 'localhost';
$username = 'root'; // Replace with your database username
$password = '';    // Replace with your database password
$dbname = 'coom';

ini_set('display_errors', 2);
ini_set('display_startup_errors', 2);
error_reporting(E_ALL);

try {
    // Create a new PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Set the default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Connection successful message (for testing purposes, remove in production)
    // echo "Database connection successful!";
    $conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

} catch (PDOException $e) {
    // Handle connection errors
    die("Database connection failed: " . $e->getMessage());
}

?>
