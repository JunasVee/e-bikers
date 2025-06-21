<?php
$pageTitle = 'Manage Bikes';      // set per-page
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
include   __DIR__ . '/includes/header.php';
include   __DIR__ . '/includes/nav.php';

// handle deletion
if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM bike WHERE id = ?");
    $stmt->bind_param("i", $_GET['delete_id']);
    $stmt->execute();
    header('Location: bikes.php');
    exit();
}

// fetch all bikes
$result = $conn->query("SELECT * FROM bike ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Manage Bikes</title>
    <link href="/e-bikers/assets/admin-lte/css/adminlte.min.css" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <?php include 'includes/nav.php'; ?>
        <div class="content-wrapper p-4">
            <h2>Bikes <a href="bike-form.php" class="btn btn-success btn-sm">+ New</a></h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($bike = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $bike['id'] ?></td>
                            <td><?= htmlspecialchars($bike['name']) ?></td>
                            <td><?= $bike['status'] ?></td>
                            <td>Rp<?= number_format($bike['price'], 0, ',', '.') ?></td>
                            <td>
                                <a href="bike-form.php?id=<?= $bike['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                <a href="?delete_id=<?= $bike['id'] ?>"
                                    onclick="return confirm('Delete this bike?')"
                                    class="btn btn-sm btn-danger">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="/e-bikers/assets/admin-lte/js/adminlte.min.js"></script>
</body>

</html>

<?php include __DIR__ . '/includes/footer.php'; ?>