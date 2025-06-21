<?php
$pageTitle = 'Manage Bikes';      // set per-page
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
include   __DIR__ . '/includes/header.php';
include   __DIR__ . '/includes/nav.php';

// optionally update status
if (isset($_GET['set_status'], $_GET['order_id'])) {
  $stmt = $conn->prepare(
    "UPDATE `order` SET status = ? WHERE id = ?"
  );
  $stmt->bind_param("si", $_GET['set_status'], $_GET['order_id']);
  $stmt->execute();
  header('Location: orders.php');
  exit();
}

$sql = "SELECT o.*, b.name AS bike_name, a.username
        FROM `order` o
        JOIN bike b ON o.bike_id=b.id
        JOIN account a ON o.user_id=a.id
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html><head><title>Manage Orders</title></head>
<body>
  <h2>Orders</h2>
  <table border=1 cellpadding=4>
    <tr><th>ID</th><th>User</th><th>Bike</th><th>Status</th><th>Actions</th></tr>
    <?php while($o = $result->fetch_assoc()): ?>
    <tr>
      <td><?= $o['id'] ?></td>
      <td><?= htmlspecialchars($o['username']) ?></td>
      <td><?= htmlspecialchars($o['bike_name']) ?></td>
      <td><?= $o['status'] ?></td>
      <td>
        <?php foreach(['pending','active','finished','cancelled'] as $st): ?>
          <?php if($st!==$o['status']): ?>
          <a href="?order_id=<?= $o['id'] ?>&set_status=<?= $st ?>">
            <?= ucfirst($st) ?>
          </a>
          <?php endif; ?>
        <?php endforeach; ?>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
  <p><a href="index.php">← Dashboard</a></p>
</body></html>

<?php include __DIR__ . '/includes/footer.php'; ?>