<?php
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Koneksi ke database
$servername = "localhost";
$username = "e-bikers";
$password = "0a9s455r";
$dbname = "e-bikers";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validasi dan upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["profile_picture"])) {
    $user_id = $_SESSION['user_id'];
    $upload_dir = "uploads/";
    $file = $_FILES["profile_picture"];
    $file_name = basename($file["name"]);
    $target_file = $upload_dir . uniqid("pp_", true) . "_" . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $valid_types = ["jpg", "jpeg", "png"];

    // Cek apakah file adalah gambar
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        echo "File is not an image.";
        exit();
    }

    // Cek ekstensi
    if (!in_array($imageFileType, $valid_types)) {
        echo "Hanya file JPG, JPEG, dan PNG yang diperbolehkan.";
        exit();
    }

    // Upload dan simpan path ke database
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // Set the profile_picture path relative to the 'uploads' folder
        $profile_picture = "uploads/" . basename($target_file);
        
        // Simpan path gambar ke database
        $sql = "UPDATE account SET profile_picture = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $profile_picture, $user_id);
        
        if ($stmt->execute()) {
            header("Location: signedin.php");
            exit();
        } else {
            echo "Gagal menyimpan ke database.";
        }
    } else {
        echo "Gagal mengunggah file.";
    }
} else {
    echo "Tidak ada file yang diunggah.";
}
?>
