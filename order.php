<?php
// Hardcoded e-bikes for demo (normally from database)
$bikes = [
    [
        'id' => 1,
        'name' => 'EcoRide S-1',
        'img' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?fit=crop&w=400&q=80',
        'desc' => 'Ringan, hemat listrik, cocok untuk perjalanan pendek.',
        'location' => 'ITS Surabaya',
        'price' => 15000 // per hour
    ],
    [
        'id' => 2,
        'name' => 'VoltBike Ultra',
        'img' => 'https://images.unsplash.com/photo-1465101162946-4377e57745c3?fit=crop&w=400&q=80',
        'desc' => 'Kecepatan tinggi, daya tahan baterai hingga 50km.',
        'location' => 'Tunjungan Plaza',
        'price' => 20000
    ],
    [
        'id' => 3,
        'name' => 'Green Motion Lite',
        'img' => 'https://images.unsplash.com/photo-1518655048521-f130df041f66?fit=crop&w=400&q=80',
        'desc' => 'Desain minimalis, ramah lingkungan, charging cepat.',
        'location' => 'Pakuwon Mall',
        'price' => 12000
    ]
];
// Pin locations for Surabaya (fake/random)
$pins = [
    [-7.265757, 112.734146], // Tunjungan Plaza
    [-7.290293, 112.727421], // Gubeng Station
    [-7.257472, 112.752090], // ITS
    [-7.273551, 112.758980], // Galaxy Mall
    [-7.245815, 112.737808], // Pakuwon Mall
];
// Static "distance away from you" value
$distance_text = "500 meters away from you";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Bikers Order</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
    <style>
        #map { height: 300px; border-radius: 12px; }
        .bike-card img { object-fit: cover; height: 140px; width: 100%; }
        .bike-card { transition: box-shadow .2s; cursor: pointer; }
        .bike-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .bike-location { font-size: 0.98em; color: #444; margin-bottom: 4px; }
        .bike-distance { font-size: 0.93em; color: #888; margin-bottom: 10px; }
    </style>
</head>
<body style="background: #f8f9fa;">
    <div class="container py-4">
        <h2 class="mb-4 text-primary fw-bold">Order E-Bike</h2>
        <!-- Map -->
        <div id="map" class="mb-4"></div>

        <div class="row mb-4">
            <div class="col-md-6 mb-2">
                <input type="text" class="form-control" placeholder="Your location (e.g. ITS Surabaya)" id="from">
            </div>
            <div class="col-md-6 mb-2">
                <input type="text" class="form-control" placeholder="Destination (e.g. Tunjungan Plaza)" id="to">
            </div>
        </div>
        <h4 class="fw-semibold mb-3">Available Electric Bikes</h4>
        <div class="row g-3">
            <?php foreach ($bikes as $bike): ?>
            <div class="col-md-4">
                <div class="card bike-card h-100" onclick="window.location='bike.php?id=<?= $bike['id'] ?>'">
                    <img src="<?= htmlspecialchars($bike['img']) ?>" alt="<?= htmlspecialchars($bike['name']) ?>">
                    <div class="card-body">
                        <div class="bike-location"><b><?= htmlspecialchars($bike['location']) ?></b></div>
                        <div class="bike-distance"><?= $distance_text ?></div>
                        <h5 class="card-title"><?= htmlspecialchars($bike['name']) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($bike['desc']) ?></p>
                        <div class="fw-bold text-primary mb-2">Rp<?= number_format($bike['price'],0,',','.') ?>/hour</div>
                        <button class="btn btn-primary w-100" type="button">Order this Bike</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
    // Display Surabaya map
    var map = L.map('map').setView([-7.265757, 112.734146], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18
    }).addTo(map);

    // Add bike pinpoints
    <?php foreach($pins as $pin): ?>
    L.marker([<?= $pin[0] ?>, <?= $pin[1] ?>]).addTo(map)
        .bindPopup("E-Bike Station");
    <?php endforeach; ?>
    </script>
</body>
</html>
