<?php
// admin/includes/auth.php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Fetch user record if not in session
if (empty($_SESSION['user'])) {
    require __DIR__ . '/db.php';
    $stmt = $conn->prepare("SELECT id, username, is_admin FROM account WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $_SESSION['user'] = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (! $_SESSION['user']['is_admin']) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied – you are not an admin.');
}
