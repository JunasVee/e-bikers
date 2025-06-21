<?php
$pageTitle = 'Manage Bikes';      // set per-page
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
include   __DIR__ . '/includes/header.php';
include   __DIR__ . '/includes/nav.php';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard</title>
    <link href="/e-bikers/assets/admin-lte/css/adminlte.min.css" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <?php include 'includes/nav.php'; ?>
        <div class="content-wrapper p-4">
            <h1>Welcome, <?= htmlspecialchars($_SESSION['user']['username']) ?></h1>
            <p>
                <a href="bikes.php" class="btn btn-primary">Manage Bikes</a>
                <a href="orders.php" class="btn btn-secondary">Manage Orders</a>
            </p>
        </div>
    </div>
    <script src="/e-bikers/assets/admin-lte/js/adminlte.min.js"></script>
</body>

</html>

<?php include __DIR__ . '/includes/footer.php'; ?>