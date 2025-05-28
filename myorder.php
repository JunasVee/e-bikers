<?php
session_start();

// Load orders from JSON API
$api_url = 'https://mocki.io/v1/c6fac614-d402-4cf3-8d72-40a7aa539ebd';
$orders = json_decode(file_get_contents($api_url), true);

// Pisahkan menjadi aktif dan pending
$active_order = null;
$pending_order = null;
foreach ($orders as $order) {
    if ($order['status'] === 'paid' && !$active_order) {
        $active_order = $order;
    }
    if ($order['status'] === 'pending' && !$pending_order) {
        $pending_order = $order;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Order | E-Bikers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .order-card { border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.07);}
        .bike-img { width: 68px; height: 68px; object-fit:cover; border-radius:7px; margin-right:18px;}
        .qr-img {width: 130px; display: block; margin: 0 auto;}
    </style>
</head>
<body style="background:#f8f9fa">
    <div class="container py-4">
        <h3 class="fw-bold mb-4 text-primary">My Order</h3>
        <!-- Active Orders -->
        <h5 class="mb-2">Active Order</h5>
        <?php if($active_order): ?>
        <div class="order-card bg-white p-3 mb-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <img src="<?= htmlspecialchars($active_order['img']) ?>" class="bike-img" alt="">
                <div>
                    <div class="fw-semibold"><?= htmlspecialchars($active_order['name']) ?></div>
                    <div class="text-muted mb-1">Order Code: <?= htmlspecialchars($active_order['order_code']) ?></div>
                    <div class="text-success fw-bold">Paid</div>
                </div>
            </div>
            <button class="btn btn-outline-dark" onclick="showQR('<?= htmlspecialchars($active_order['order_code']) ?>', '<?= htmlspecialchars($active_order['id']) ?>')">Tampilkan QR</button>
        </div>
        <?php else: ?>
            <div class="alert alert-info">Belum ada order aktif.</div>
        <?php endif; ?>

        <!-- Pending Orders -->
        <h5 class="mt-4 mb-2">Pending Order</h5>
        <?php if($pending_order): ?>
        <div class="order-card bg-white p-3 mb-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <img src="<?= htmlspecialchars($pending_order['img']) ?>" class="bike-img" alt="">
                <div>
                    <div class="fw-semibold"><?= htmlspecialchars($pending_order['name']) ?></div>
                    <div class="text-muted mb-1">Order Code: <?= htmlspecialchars($pending_order['order_code']) ?></div>
                    <div class="text-warning fw-bold">Pending Payment</div>
                </div>
            </div>
            <a href="payment.php?id=<?= $pending_order['id'] ?>" class="btn btn-success">Bayar Sekarang</a>
        </div>
        <?php else: ?>
            <div class="alert alert-info">Tidak ada order pending.</div>
        <?php endif; ?>
    </div>

    <!-- Modal QR -->
    <div class="modal" tabindex="-1" id="qrModal">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
          <div class="modal-header">
            <h5 class="modal-title">QR Kunci Sepeda</h5>
            <button type="button" class="btn-close" onclick="closeQR()" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="qrArea"></div>
            <div class="text-muted mt-2">Scan QR ini untuk membuka kunci sepeda E-Bikers.</div>
          </div>
        </div>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrious@4.0.2/dist/qrious.min.js"></script>
    <script>
        function showQR(orderCode, bikeId) {
            var qrValue = "E-Bikers|" + orderCode + "|BIKE" + bikeId;
            var modal = new bootstrap.Modal(document.getElementById('qrModal'));
            var qr = new QRious({
                value: qrValue,
                size: 160
            });
            document.getElementById('qrArea').innerHTML = '';
            document.getElementById('qrArea').appendChild(qr.image);
            modal.show();
        }
        function closeQR() {
            var modal = bootstrap.Modal.getInstance(document.getElementById('qrModal'));
            modal.hide();
        }
    </script>
</body>
</html>
