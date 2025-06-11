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

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

// Fetch order & bike
$sql = "SELECT o.*, b.name AS bike_name, b.image AS bike_image, b.price AS bike_hourly_price
        FROM `order` o
        JOIN bike b ON o.bike_id = b.id
        WHERE o.id = ? AND o.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) die("Order tidak ditemukan.");

$final_price = $order['price'];
// If order is still active, calculate price and set end_time
if ($order['status'] == 'active') {
    $start_time = strtotime($order['start_time']);
    $end_time = time();
    $duration_secs = $end_time - $start_time;
    $hours = ceil($duration_secs / 3600);
    if ($hours < 1) $hours = 1;
    $final_price = $order['bike_hourly_price'] * $hours;
    $end_time_str = date('Y-m-d H:i:s', $end_time);
    // Update order with end_time, new price, set status to pending_payment
    $stmt = $conn->prepare("UPDATE `order` SET end_time = ?, price = ?, status = 'pending_payment' WHERE id = ?");
    $stmt->bind_param("sii", $end_time_str, $final_price, $order_id);
    $stmt->execute();
    $stmt->close();
    $order['end_time'] = $end_time_str;
    $order['price'] = $final_price;
    $order['status'] = 'pending_payment';
}

// Handle payment
if (isset($_GET['pay'])) {
    // Set order to paid, bike to available
    $stmt1 = $conn->prepare("UPDATE `order` SET status = 'paid' WHERE id = ?");
    $stmt1->bind_param("i", $order_id);
    $stmt1->execute();
    $stmt1->close();
    $stmt2 = $conn->prepare("UPDATE bike SET status = 'available' WHERE id = ?");
    $stmt2->bind_param("i", $order['bike_id']);
    $stmt2->execute();
    $stmt2->close();
    header("Location: order_history.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment - E-Bikers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .invoice-box {
            max-width: 420px;
            margin: 50px auto;
            background: #fff;
            padding: 32px 26px;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.11);
        }
        .invoice-img {
            width: 120px;
            border-radius: 9px;
        }
    </style>
</head>

<body style="background:#f8f9fa">
    <div class="invoice-box">
        <h4 class="mb-3 fw-bold">Pembayaran Sewa E-Bike</h4>
        <img src="<?= htmlspecialchars($order['bike_image']) ?>" class="invoice-img mb-3" alt="">
        <div><b>Bike:</b> <?= htmlspecialchars($order['bike_name']) ?></div>
        <div><b>Durasi:</b>
            <?php
            if ($order['end_time']) {
                $dur = strtotime($order['end_time']) - strtotime($order['start_time']);
                $h = floor($dur/3600); $m = floor(($dur%3600)/60);
                echo sprintf('%02d:%02d', $h, $m);
            } else {
                echo '-';
            }
            ?>
        </div>
        <div class="mb-2"><b>Total Harga:</b> Rp<?= number_format($order['price'], 0, ',', '.') ?></div>
        <hr>
        <?php if ($order['status'] == 'pending_payment'): ?>
            <a href="payment.php?order_id=<?= $order_id ?>&pay=1" class="btn btn-success w-100 py-2">Bayar</a>
            <div class="text-center mt-3"><a href="order.php">Kembali</a></div>
        <?php elseif ($order['status'] == 'paid'): ?>
            <div class="alert alert-success text-center">Pembayaran berhasil!</div>
            <div class="text-center mt-3"><a href="order_history.php">Lihat Riwayat</a></div>
        <?php else: ?>
            <div class="alert alert-info">Status pesanan: <?= htmlspecialchars($order['status']) ?></div>
            <div class="text-center mt-3"><a href="order.php">Kembali</a></div>
        <?php endif; ?>
    </div>
</body>
</html>
