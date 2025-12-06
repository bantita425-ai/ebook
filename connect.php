<?php
$host = "localhost";
$username = "u299560388_661037";
$password = "VT3142Ql@";
$dbname = "u299560388_661037";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
