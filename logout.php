<?php
session_start();

// Destroy the session
session_unset();
session_destroy();

// Delete cookies by setting their expiration date to a past time
setcookie('user_id', '', time() - 3600, "/");
setcookie('user_email', '', time() - 3600, "/");

// Redirect to login page
header("Location: login.php");
exit();
?>
