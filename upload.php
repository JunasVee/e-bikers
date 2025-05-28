<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "e-bikers";
$password = "0a9s455r";
$dbname = "e-bikers";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["profile_picture"])) {
    $user_id = $_SESSION['user_id'];
    $upload_dir = "uploads/";
    $file = $_FILES["profile_picture"];
    $file_name = basename($file["name"]);
    $target_file = $upload_dir . uniqid("pp_", true) . "_" . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $valid_types = ["jpg", "jpeg", "png"];

    // Check if file is actually an image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        // Not an image
        header("Location: signedin.php?error=File%20is%20not%20an%20image.");
        exit();
    }

    // Check file extension
    if (!in_array($imageFileType, $valid_types)) {
        header("Location: signedin.php?error=Only%20JPG,%20JPEG,%20PNG%20allowed.");
        exit();
    }

    // Move the file
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // Update profile_picture path in DB
        $profile_picture = $target_file;

        $sql = "UPDATE account SET profile_picture = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $profile_picture, $user_id);

        if ($stmt->execute()) {
            header("Location: signedin.php?success=Profile%20picture%20updated!");
            exit();
        } else {
            header("Location: signedin.php?error=Failed%20to%20update%20database.");
            exit();
        }
    } else {
        header("Location: signedin.php?error=Failed%20to%20upload%20file.");
        exit();
    }
} else {
    header("Location: signedin.php?error=No%20file%20uploaded.");
    exit();
}
?>
