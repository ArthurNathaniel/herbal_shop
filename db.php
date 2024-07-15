<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "herbal_shop";

// $servername = "nathstack.tech";
// $username = "u500921674_pos";
// $password = "OnGod@123";
// $dbname = "u500921674_pos";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
