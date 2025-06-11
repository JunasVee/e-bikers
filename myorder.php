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

// Handle start rental (user clicks "Start Renting")
if (isset($_POST['start_order_id'])) {
    $start_order_id = intval($_POST['start_order_id']);
    $stmt = $conn->prepare("UPDATE `order` SET status = 'active', start_time = NOW() WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $start_order_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    // Set bike to rented
    $stmt2 = $conn->prepare("UPDATE bike SET status = 'rented' WHERE id = (SELECT bike_id FROM `order` WHERE id = ?)");
    $stmt2->bind_param("i", $start_order_id);
    $stmt2->execute();
    $stmt2->close();
    header("Location: myorder.php");
    exit();
}

// Fetch latest active/pending order for user
$sql = "SELECT o.*, b.name AS bike_name, b.image AS bike_image, b.id AS bike_id 
        FROM `order` o
        JOIN bike b ON o.bike_id = b.id
        WHERE o.user_id = ? AND o.status IN ('pending','active','pending_payment','paid')
        ORDER BY o.created_at DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) die("Belum ada pesanan aktif.");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Order | E-Bikers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .invoice-box {
            max-width: 420px;
            margin: 50px auto;
            background: #fff;
            padding: 32px 26px;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0,0,0,0.11);
        }
        .invoice-img {
            width: 120px;
            border-radius: 9px;
        }
        .qr-modal-img {
            width: 220px; 
            display:block; 
            margin:auto;
        }
    </style>
</head>
<body style="background:#f8f9fa">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0 fw-bold">Order Aktif E-Bikers</h4>
            <a href="order_history.php" class="btn btn-outline-secondary">Order History</a>
        </div>
        <div class="invoice-box">
            <img src="<?= htmlspecialchars($order['bike_image']) ?>" class="invoice-img mb-3" alt="">
            <div><b>Bike:</b> <?= htmlspecialchars($order['bike_name']) ?></div>
            <div><b>Status:</b> <?= htmlspecialchars($order['status']) ?></div>
            <?php if ($order['status'] == 'pending'): ?>
                <form method="POST" class="mt-3">
                    <input type="hidden" name="start_order_id" value="<?= $order['id'] ?>">
                    <button class="btn btn-success w-100 py-2" type="submit">Start Renting</button>
                </form>
                <div class="text-center mt-3"><a href="order.php">Kembali</a></div>
            <?php elseif ($order['status'] == 'active'): ?>
                <div>
                    <b>Waktu Sewa:</b> <span id="timer">00:00:00</span>
                </div>
                <div class="mb-2"><b>Harga sementara:</b> Rp<span id="dynamicPrice"><?= number_format($order['price'],0,',','.') ?></span></div>
                <div class="fw-bold text-info mb-3">Sedang dalam penggunaan.</div>
                <!-- Redirect to payment page with order id -->
                <a href="payment.php?order_id=<?= $order['id'] ?>" class="btn btn-success w-100 py-2 mt-3">Done</a>
                <button class="btn btn-outline-secondary w-100 py-2 mt-2" data-bs-toggle="modal" data-bs-target="#qrModal">Show QR Code</button>
            <?php elseif ($order['status'] == 'pending_payment'): ?>
                <div class="mb-2"><b>Total Harga:</b> Rp<?= number_format($order['price'], 0, ',', '.') ?></div>
                <div class="alert alert-warning mb-3">Silakan bayar untuk menyelesaikan sewa.</div>
                <a href="payment.php?order_id=<?= $order['id'] ?>" class="btn btn-primary w-100 py-2">Bayar Sekarang</a>
            <?php elseif ($order['status'] == 'paid'): ?>
                <div class="mb-2"><b>Total Harga:</b> Rp<?= number_format($order['price'], 0, ',', '.') ?></div>
                <div class="alert alert-success text-center">Order sudah dibayar! Terima kasih.</div>
                <div class="text-center mt-3"><a href="order.php">Pesan Lagi</a></div>
            <?php endif; ?>
            <?php if ($order['status'] != 'pending'): ?>
            <div class="text-center mt-3"><a href="order.php">Pesan Lagi</a></div>
            <?php endif; ?>
        </div>
    </div>
    <!-- QR Modal -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="qrModalLabel">Scan QR Code to Unlock</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?= urlencode('EBIKE-' . $order['bike_id']) ?>" alt="QR Code" class="qr-modal-img mb-2" />
            <div class="mt-2">Kode Sepeda: <b><?= htmlspecialchars($order['bike_id']) ?></b></div>
          </div>
        </div>
      </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($order['status'] == 'active'): ?>
    <script>
        let startTimestamp = <?= strtotime($order['start_time']) ?>;
        let pricePerHour = <?= intval($order['price']) ?>;
        function updateTimer() {
            let now = Math.floor(Date.now() / 1000);
            let elapsed = now - startTimestamp;
            if (elapsed < 0) elapsed = 0;
            let h = Math.floor(elapsed / 3600);
            let m = Math.floor((elapsed % 3600) / 60);
            let s = elapsed % 60;
            let hoursBilled = Math.ceil(elapsed / 3600);
            if (hoursBilled < 1) hoursBilled = 1;
            document.getElementById('timer').textContent =
                String(h).padStart(2, '0') + ':' +
                String(m).padStart(2, '0') + ':' +
                String(s).padStart(2, '0');
            document.getElementById('dynamicPrice').textContent =
                (pricePerHour * hoursBilled).toLocaleString('id-ID');
        }
        updateTimer();
        setInterval(updateTimer, 1000);
    </script>
    <?php endif; ?>
</body>
</html>
