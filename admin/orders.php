<?php
// admin/orders.php
$pageTitle = 'Manage Orders';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
include   __DIR__ . '/includes/header.php';
include   __DIR__ . '/includes/nav.php';

// optionally update status
if (isset($_GET['set_status'], $_GET['order_id'])) {
  $stmt = $conn->prepare("UPDATE `order` SET status = ? WHERE id = ?");
  $stmt->bind_param("si", $_GET['set_status'], $_GET['order_id']);
  $stmt->execute();
  header('Location: orders.php');
  exit();
}

// delete
if (isset($_GET['delete_id'])) {
  $stmt = $conn->prepare("DELETE FROM `order` WHERE id = ?");
  $stmt->bind_param("i", $_GET['delete_id']);
  $stmt->execute();
  header('Location: orders.php');
  exit();
}

// fetch orders
$sql = "SELECT o.*, b.name AS bike_name, a.username
        FROM `order` o
        JOIN bike b ON o.bike_id = b.id
        JOIN account a ON o.user_id = a.id
        ORDER BY o.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.min.css">
</head>

<body>
  <div class="wrapper">
    <?php include __DIR__ . '/includes/nav.php'; ?>

    <div class="content-wrapper p-4">
      <h2>
        Orders
        <a href="download_orders.php" class="btn btn-success btn-sm float-end">
          ⬇ Download CSV
        </a>
      </h2>

      <table class="table table-striped">
        <thead>
          <tr>
            <th>ID</th>
            <th>User</th>
            <th>Bike</th>
            <th>Status</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Price</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($o = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $o['id'] ?></td>
              <td><?= htmlspecialchars($o['username']) ?></td>
              <td><?= htmlspecialchars($o['bike_name']) ?></td>
              <td><?= htmlspecialchars($o['status']) ?></td>
              <td><?= $o['start_time'] ?: '-' ?></td>
              <td><?= $o['end_time'] ?: '-' ?></td>
              <td>
                <?= is_numeric($o['price'])
                  ? 'Rp' . number_format($o['price'], 0, ',', '.')
                  : '-' ?>
              </td>
              <td><?= $o['created_at'] ?></td>
              <td>
                <?php foreach (['pending', 'active', 'finished', 'cancelled'] as $st): ?>
                  <?php if ($st !== $o['status']): ?>
                    <a href="?order_id=<?= $o['id'] ?>&set_status=<?= $st ?>"
                      class="btn btn-sm btn-outline-primary mb-1">
                      <?= ucfirst($st) ?>
                    </a>
                  <?php endif; ?>
                <?php endforeach; ?>

                <!-- DELETE button with confirmation -->
                <a
                  href="?delete_id=<?= $o['id'] ?>"
                  class="btn btn-sm btn-outline-danger mb-1 delete-btn"
                  data-id="<?= $o['id'] ?>">
                  Delete
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>

      <p>
        <a href="index.php" class="btn btn-secondary btn-sm">
          ← Dashboard
        </a>
      </p>
    </div><!-- /.content-wrapper -->
  </div><!-- /.wrapper -->

  <?php include __DIR__ . '/includes/footer.php'; ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', e => {
          e.preventDefault();
          const url = btn.getAttribute('href');
          const id = btn.dataset.id;

          Swal.fire({
            title: 'Are you sure?',
            text: `This will permanently delete order #${id}.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
            reverseButtons: true,
            customClass: {
              confirmButton: 'btn btn-danger',
              cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false
          }).then(result => {
            if (result.isConfirmed) {
              // proceed to your delete handler
              window.location = url;
            }
          });
        });
      });
    });
  </script>

</body>

</html>