<?php
// admin/includes/db.php
$servername = "localhost";
$username   = "e-bikers";
$password   = "0a9s455r";
$dbname     = "e-bikers";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
