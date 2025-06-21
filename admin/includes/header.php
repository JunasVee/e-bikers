<?php
// admin/includes/header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard</title>
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
  >
  <style>
    .sidebar {
      width: 200px;
      height: 100vh;
      position: fixed;
      top: 56px;
      left: 0;
      background: #343a40;
      overflow-y: auto;
    }
    .sidebar a {
      color: #adb5bd;
      display: block;
      padding: 10px 15px;
      text-decoration: none;
    }
    .sidebar a.active,
    .sidebar a:hover {
      background: #495057;
      color: #fff;
    }
    .content-wrapper {
      margin-top: 56px;
      margin-left: 200px;
      padding: 20px;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">E-Bikers Admin</a>
      <div class="d-flex">
        <span class="navbar-text text-light me-3">
          <?= htmlspecialchars($_SESSION['user']['username']) ?>
        </span>
        <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
      </div>
    </div>
  </nav>
