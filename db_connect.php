<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "melody_masters"; 


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    
    die("error: " . $conn->connect_error);
}


$conn->set_charset("utf8mb4"); 
?>