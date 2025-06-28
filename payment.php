<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// ─── DB CONFIG ───────────────────────────────────────────────────────────
$servername = "localhost";
$dbUsername = "e-bikers";
$dbPassword = "0a9s455r";
$dbName     = "e-bikers";

$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ─── PRODUCTION MIDTRANS CONFIG ─────────────────────────────────────────
$serverKey = 'Mid-server-GuOM2YGOFG0Wkd_KjstvC6Jt'; // your Production Server Key
$clientKey = 'Mid-client-OOqWUyQQVDBsXiqH';         // your Production Client Key
$apiUrl    = 'https://api.midtrans.com/snap/v1/transactions';
$snapJsUrl = 'https://app.midtrans.com/snap/snap.js';

// ─── LOAD ORDER & BIKE ───────────────────────────────────────────────────
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$sql = "SELECT o.*, b.name AS bike_name, b.image AS bike_image, b.price AS bike_hourly_price
          FROM `order` o
          JOIN bike b ON o.bike_id = b.id
         WHERE o.id = ? AND o.user_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$order) {
    die("Order not found.");
}

// ─── CALCULATE FINAL PRICE & UPDATE STATUS ─────────────────────────────
$final_price = $order['price'];
if ($order['status'] === 'active') {
    $start_time   = strtotime($order['start_time']);
    $end_time     = time();
    $hours        = max(1, ceil(($end_time - $start_time) / 3600));
    $final_price  = $order['bike_hourly_price'] * $hours;
    $end_time_str = date('Y-m-d H:i:s', $end_time);

    $u = $conn->prepare("
        UPDATE `order`
           SET end_time = ?, price = ?, status = 'pending_payment'
         WHERE id = ?
    ");
    if (!$u) die("Prepare failed: " . $conn->error);
    $u->bind_param("sii", $end_time_str, $final_price, $order_id);
    $u->execute();
    $u->close();

    $order['end_time'] = $end_time_str;
    $order['price']    = $final_price;
    $order['status']   = 'pending_payment';
}

// ─── FETCH CUSTOMER DETAILS (email only) ───────────────────────────────
$stmt = $conn->prepare("SELECT email FROM account WHERE id = ?");
if (!$stmt) die("Prepare failed: " . $conn->error);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();

// derive a simple first name from email
$firstName = explode('@', $email, 2)[0];
$lastName  = '';
$phone     = '';  // add phone column & fetch if you need it

// ─── GENERATE SNAP TOKEN (PRODUCTION) ───────────────────────────────────
if ($order['status'] === 'pending_payment') {
    $payload = [
        'transaction_details' => [
            'order_id'     => 'EBIKE-' . $order_id . '-' . time(),
            'gross_amount' => $final_price,
        ],
        'customer_details' => [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'phone'      => $phone,
        ],
        // no enabled_payments → show all methods you’ve enabled in Prod dashboard
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_USERPWD        => $serverKey . ':',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
    ]);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        die("Midtrans cURL error: " . curl_error($ch));
    }
    curl_close($ch);

    $resp = json_decode($result, true);
    if (empty($resp['token'])) {
        die("Midtrans error: " . ($resp['status_message'] ?? 'unknown'));
    }
    $snapToken = $resp['token'];
}

// ─── HANDLE PRODUCTION PAYMENT CALLBACK ─────────────────────────────────
if (isset($_GET['pay'])) {
    $p = $conn->prepare("UPDATE `order` SET status = 'paid' WHERE id = ?");
    if (!$p) die("Prepare failed: " . $conn->error);
    $p->bind_param("i", $order_id);
    $p->execute();
    $p->close();

    $b = $conn->prepare("UPDATE bike SET status = 'available' WHERE id = ?");
    if (!$b) die("Prepare failed: " . $conn->error);
    $b->bind_param("i", $order['bike_id']);
    $b->execute();
    $b->close();

    header("Location: order_history.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment – E-Bikers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .invoice-box {
            max-width: 420px;
            margin: 50px auto;
            padding: 32px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.1);
        }

        .invoice-img {
            max-width: 120px;
            border-radius: 8px;
        }
    </style>
</head>

<body style="background:#f8f9fa">
    <div class="invoice-box">
        <h4 class="mb-3">Pembayaran Sewa E-Bike</h4>
        <img src="<?= htmlspecialchars($order['bike_image']) ?>" class="invoice-img mb-3" alt="">
        <div><b>Bike:</b> <?= htmlspecialchars($order['bike_name']) ?></div>
        <div><b>Durasi:</b>
            <?php
            if (!empty($order['end_time'])) {
                $d = strtotime($order['end_time']) - strtotime($order['start_time']);
                echo sprintf('%02d:%02d', floor($d / 3600), floor(($d % 3600) / 60));
            } else {
                echo '-';
            }
            ?>
        </div>
        <div class="mb-2"><b>Total Harga:</b> Rp<?= number_format($order['price'], 0, ',', '.') ?></div>
        <hr>
        <?php if ($order['status'] === 'pending_payment'): ?>
            <button id="pay-button" class="btn btn-success w-100 py-2">Bayar Sekarang</button>
            <script src="<?= $snapJsUrl ?>" data-client-key="<?= $clientKey ?>"></script>
            <script>
                document.getElementById('pay-button').addEventListener('click', function() {
                    snap.pay('<?= $snapToken ?>', {
                        onSuccess: function() {
                            window.location = 'payment.php?order_id=<?= $order_id ?>&pay=1';
                        },
                        onPending: function() {
                            alert('Menunggu pembayaran.');
                        },
                        onError: function() {
                            alert('Pembayaran gagal.');
                        },
                        onClose: function() {
                            console.log('Popup ditutup.');
                        }
                    });
                });
            </script>
            <div class="text-center mt-3"><a href="order.php">Kembali</a></div>
        <?php elseif ($order['status'] === 'paid'): ?>
            <div class="alert alert-success text-center">🎉 Pembayaran berhasil!</div>
            <div class="text-center mt-3"><a href="order_history.php">Lihat Riwayat</a></div>
        <?php else: ?>
            <div class="alert alert-info text-center">Status: <?= htmlspecialchars($order['status']) ?></div>
            <div class="text-center mt-3"><a href="order.php">Kembali</a></div>
        <?php endif; ?>
    </div>
</body>

</html>