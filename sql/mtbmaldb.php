<?php
$servername = "localhost";
$username = "root";
$password = "";

// Create connection
$conn = new mysqli($servername, $username, $password);


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Create database
$sqldb = "CREATE DATABASE mtbmaldb";
if ($conn->query($sqldb) === TRUE)
    echo "Database created successfully.";
else
    echo "Error creating database: " . $conn->error;

// Close the database connection
$conn->close();
?>
