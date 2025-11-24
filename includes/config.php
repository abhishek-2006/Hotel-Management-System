<?php
// Database credentials
$host = 'localhost';
$username = 'root'; 
$password = '';
$database = 'hotel_booking';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Set character set for proper data handling
$conn->set_charset("utf8");
?>