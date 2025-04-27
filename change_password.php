<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// DB config
$servername = "localhost";
$username = "e-bikers";
$password = "0a9s455r";
$dbname = "e-bikers";

// Connect to database
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$user_id = $_SESSION['user_id'];

// Validate input
if (empty($current_password) || empty($new_password)) {
    echo "Please fill in both fields.";
    exit();
}

// Fetch current password
$sql = "SELECT password FROM account WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($stored_password);
$stmt->fetch();
$stmt->close();

// Compare plain text passwords
if ($current_password !== $stored_password) {
    echo "Incorrect current password.";
    exit();
}

// Update password directly
$sql = "UPDATE account SET password = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $new_password, $user_id);
if ($stmt->execute()) {
    echo "Password changed successfully.";
} else {
    echo "Error updating password.";
}
$stmt->close();
$conn->close();
?>
