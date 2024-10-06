<?php
// Database connection parameters
$host = "35.212.235.18";    // Host name
$username = "db-sqldev";     // Database username
$password = "root";         // Database password
$database = "F1_Dataset";  // Your database name

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>