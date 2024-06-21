<?php
//$host = 'localhost';
//$db = 'student_registration';
//$user = 'zack';
//$pass = 'password'; // Default MySQL password for XAMPP is empty

$host = 'us-cluster-east-01.k8s.cleardb.net';
$db = 'heroku_76effe95fc8ce84';
$user = 'bb5dc6dd6a0ac1';
$pass = 'b7a5f0dc'; // Default MySQL password for XAMPP is empty

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
