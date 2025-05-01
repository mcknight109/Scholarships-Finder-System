<?php
$servername = "localhost";
$username = "root";
$password = ""; // leave empty if no password
$database = "scholarship_system"; // change to your actual DB name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
