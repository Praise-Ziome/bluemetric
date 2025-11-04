<?php
$host = "localhost";
$user = "root";       // default XAMPP username
$pass = "";           // default XAMPP password (empty)
$dbname = "bluemetric"; // make sure this DB exists in phpMyAdmin

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
