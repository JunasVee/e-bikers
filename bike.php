<?php
// Dummy bikes data
$bikes = [
    1 => [
        'name' => 'EcoRide S-1',
        'img' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?fit=crop&w=400&q=80',
        'desc' => 'Ringan, hemat listrik, cocok untuk perjalanan pendek.',
        'price' => 15000
    ],
    2 => [
        'name' => 'VoltBike Ultra',
        'img' => 'https://images.unsplash.com/photo-1465101162946-4377e57745c3?fit=crop&w=400&q=80',
        'desc' => 'Kecepatan tinggi, daya tahan baterai hingga 50km.',
        'price' => 20000
    ],
    3 => [
        'name' => 'Green Motion Lite',
        'img' => 'https://images.unsplash.com/photo-1518655048521-f130df041f66?fit=crop&w=400&q=80',
        'desc' => 'Desain minimalis, ramah lingkungan, charging cepat.',
        'price' => 12000
    ]
];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$bike = isset($bikes[$id]) ? $bikes[$id] : null;
if (!$bike) { die("Bike not found."); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($bike['name']) ?> - E-Bikers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .bike-img { width: 100%; max-width: 400px; border-radius: 14px; object-fit: cover; }
    </style>
</head>
<body style="background: #f8f9fa;">
    <div class="container py-5">
        <div class="text-center mb-4">
            <img src="<?= htmlspecialchars($bike['img']) ?>" class="bike-img mb-3" alt="<?= htmlspecialchars($bike['name']) ?>">
            <h2 class="fw-bold"><?= htmlspecialchars($bike['name']) ?></h2>
            <p class="lead"><?= htmlspecialchars($bike['desc']) ?></p>
            <div class="fw-bold text-primary mb-3" style="font-size:1.5rem;">
                Rp<?= number_format($bike['price'],0,',','.') ?>/hour
            </div>
            <a href="payment.php?id=<?= $id ?>" class="btn btn-success btn-lg">Order &amp; Pay</a>
        </div>
    </div>
</body>
</html>
