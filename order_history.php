<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "e-bikers";
$password = "0a9s455r";
$dbname = "e-bikers";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Ambil semua order user
$sql = "SELECT o.*, b.name AS bike_name, b.image AS bike_image 
        FROM `order` o
        JOIN bike b ON o.bike_id = b.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order History | E-Bikers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .history-table { background: #fff; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.07); }
        .bike-thumb { width: 64px; border-radius: 7px; }
        .status-paid { color: #27ae60; font-weight: 500; }
        .status-active { color: #2980b9; font-weight: 500; }
        .status-pending_payment { color: #e67e22; font-weight: 500; }
        .status-pending { color: #888; font-weight: 500; }
        .status-cancelled { color: #c0392b; font-weight: 500; }
        .status-finished { color: #7f8c8d; font-weight: 500; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary fw-bold mb-0">Order History</h2>
            <a href="order.php" class="btn btn-outline-primary">Order E-Bike</a>
        </div>
        <div class="table-responsive history-table p-3">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Bike</th>
                        <th>Status</th>
                        <th>Order Time</th>
                        <th>End Time</th>
                        <th>Duration</th>
                        <th>Total Price</th>
                        <th>QR</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($orders) == 0): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">No order history yet.</td>
                    </tr>
                <?php else: foreach ($orders as $i => $order): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td>
                            <img src="<?= htmlspecialchars($order['bike_image']) ?>" class="bike-thumb me-2" alt="">
                            <?= htmlspecialchars($order['bike_name']) ?>
                        </td>
                        <td class="status-<?= htmlspecialchars($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></td>
                        <td><?= htmlspecialchars($order['start_time']) ?></td>
                        <td><?= $order['end_time'] ? htmlspecialchars($order['end_time']) : '-' ?></td>
                        <td>
                        <?php
                            if ($order['end_time']) {
                                $dur = strtotime($order['end_time']) - strtotime($order['start_time']);
                                $h = floor($dur/3600); $m = floor(($dur%3600)/60);
                                echo sprintf('%02d:%02d', $h, $m);
                            } else {
                                echo '-';
                            }
                        ?>
                        </td>
                        <td>
                            <?= $order['price'] ? 'Rp'.number_format($order['price'],0,',','.') : '-' ?>
                        </td>
                        <td>
                            <a href="#" data-bs-toggle="modal" data-bs-target="#qrModal<?= $order['id'] ?>">QR</a>
                            <!-- Modal -->
                            <div class="modal fade" id="qrModal<?= $order['id'] ?>" tabindex="-1" aria-labelledby="qrModalLabel<?= $order['id'] ?>" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                  <div class="modal-header">
                                    <h5 class="modal-title" id="qrModalLabel<?= $order['id'] ?>">QR Code for Bike <?= htmlspecialchars($order['bike_name']) ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body text-center">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode('EBIKE-' . $order['bike_id']) ?>" alt="QR Code" />
                                  </div>
                                </div>
                              </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
