<?php
$servername = "localhost";
$dbusername = "e-bikers";
$dbpassword = "0a9s455r"; // MySQL database password
$dbname = "e-bikers";

$conn = mysqli_connect($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
echo "Koneksi berhasil!";
?>
