<?php
session_start();

// Untuk demo, ambil detail bike dari URL (id)
$bikes = [
    1 => ['name'=>'EcoRide S-1', 'img'=>'https://images.unsplash.com/photo-1506744038136-46273834b3fb?fit=crop&w=400&q=80', 'price'=>15000],
    2 => ['name'=>'VoltBike Ultra', 'img'=>'https://images.unsplash.com/photo-1465101162946-4377e57745c3?fit=crop&w=400&q=80', 'price'=>20000],
    3 => ['name'=>'Green Motion Lite', 'img'=>'https://images.unsplash.com/photo-1518655048521-f130df041f66?fit=crop&w=400&q=80', 'price'=>12000]
];

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$bike = isset($bikes[$id]) ? $bikes[$id] : null;

// Simulasi status pembayaran
if (isset($_GET['pay'])) {
    // Simpan status order ke session sebagai active (asumsi 1 order saja untuk demo)
    $_SESSION['order'] = [
        'id' => $id,
        'name' => $bike['name'],
        'img' => $bike['img'],
        'price' => $bike['price'],
        'status' => 'paid',
        'order_code' => 'ORDER'.rand(100000,999999)
    ];
    header("Location: myorder.php");
    exit();
}
if (!$bike) { die("Bike not found."); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment - Invoice | E-Bikers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .invoice-box { max-width: 420px; margin: 50px auto; background: #fff; padding: 32px 26px; border-radius: 14px; box-shadow: 0 4px 18px rgba(0,0,0,0.11);}
        .invoice-img { width: 120px; border-radius: 9px; }
    </style>
</head>
<body style="background:#f8f9fa">
    <div class="invoice-box">
        <h4 class="mb-3 fw-bold">Invoice E-Bikers</h4>
        <img src="<?= htmlspecialchars($bike['img']) ?>" class="invoice-img mb-3" alt="">
        <div><b>Bike:</b> <?= htmlspecialchars($bike['name']) ?></div>
        <div><b>Durasi:</b> 1 jam</div>
        <div class="mb-2"><b>Harga:</b> Rp<?= number_format($bike['price'],0,',','.') ?></div>
        <hr>
        <div class="fw-bold text-primary mb-3" style="font-size:1.3rem;">
            Total: Rp<?= number_format($bike['price'],0,',','.') ?>
        </div>
        <a href="payment.php?id=<?= $id ?>&pay=1" class="btn btn-success w-100 py-2">Bayar</a>
        <div class="text-center mt-3"><a href="order.php">Kembali</a></div>
    </div>
</body>
</html>
