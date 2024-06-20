<?php
$host = 'localhost';
$db = 'student_registration';
$user = 'zack';
$pass = 'password'; // Default MySQL password for XAMPP is empty

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
