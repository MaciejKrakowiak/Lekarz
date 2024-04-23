<?php
// db.php
$servername = "localhost";
$username = "admin";
$password = "1234";
$dbname = "database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
