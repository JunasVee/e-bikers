<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Koneksi DB
$servername = "localhost";
$username = "e-bikers";
$password = "0a9s455r";
$dbname = "e-bikers";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Proses order baru
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['bike_id'])) {
    $bike_id = intval($_POST['bike_id']);
    $user_id = $_SESSION['user_id'];

    // Ambil info sepeda untuk validasi harga & ketersediaan
    $sql_bike = "SELECT price, status FROM bike WHERE id = ?";
    $stmt_bike = $conn->prepare($sql_bike);
    $stmt_bike->bind_param("i", $bike_id);
    $stmt_bike->execute();
    $result_bike = $stmt_bike->get_result();
    $bike_data = $result_bike->fetch_assoc();
    $stmt_bike->close();

    if (!$bike_data) {
        die("Bike tidak ditemukan.");
    }
    if ($bike_data['status'] != 'available') {
        die("Sepeda sedang tidak tersedia.");
    }

    $price = $bike_data['price'];
    $order_code = "ORDER" . rand(100000, 999999);
    $start_time = date('Y-m-d H:i:s');

    // Insert order ke DB dengan status 'pending' (tidak langsung mulai sewa)
    $sql = "INSERT INTO `order` (user_id, bike_id, start_time, price, status, order_code) 
            VALUES (?, ?, ?, ?, 'pending', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisis", $user_id, $bike_id, $start_time, $price, $order_code);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Redirect ke halaman myorder.php
    header("Location: myorder.php");
    exit();
}

// Ambil semua sepeda yang tersedia dan juga koordinatnya
$sql = "SELECT * FROM bike WHERE status = 'available'";
$result = $conn->query($sql);

// Ambil semua sepeda ke array untuk map
$bikes = [];
while ($row = $result->fetch_assoc()) {
    $bikes[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>E-Bikers Order</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map {
            height: 300px;
            border-radius: 12px;
        }

        .bike-card img {
            object-fit: cover;
            height: 140px;
            width: 100%;
        }

        .bike-card {
            transition: box-shadow .2s;
            cursor: pointer;
        }

        .bike-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .bike-location {
            font-size: 0.98em;
            color: #444;
            margin-bottom: 4px;
        }

        .bike-distance {
            font-size: 0.93em;
            color: #888;
            margin-bottom: 10px;
        }
    </style>
</head>

<body style="background: #f8f9fa;">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary fw-bold mb-0">Order E-Bike</h2>
            <a href="myorder.php" class="btn btn-outline-primary">My Order</a>
        </div>
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
            <?php if (count($bikes) > 0): ?>
                <?php foreach ($bikes as $bike): ?>
                    <div class="col-md-4">
                        <div class="card bike-card h-100">
                            <img src="<?= htmlspecialchars($bike['image']) ?>" alt="<?= htmlspecialchars($bike['name']) ?>">
                            <div class="card-body">
                                <div class="bike-location"><b><?= htmlspecialchars($bike['location']) ?></b></div>
                                <h5 class="card-title"><?= htmlspecialchars($bike['name']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($bike['desc']) ?></p>
                                <div class="fw-bold text-primary mb-2">Rp<?= number_format($bike['price'], 0, ',', '.') ?>/jam</div>
                                <form method="POST" action="order.php">
                                    <input type="hidden" name="bike_id" value="<?= $bike['id'] ?>">
                                    <button class="btn btn-primary w-100" type="submit">Order this Bike</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Semua sepeda sedang dipinjam. Silakan cek kembali nanti!
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        // Pass PHP bikes array to JS
        var availableBikes = <?= json_encode($bikes) ?>;

        var map = L.map('map').setView([-7.265757, 112.734146], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18
        }).addTo(map);

        // Place markers for each available bike
        availableBikes.forEach(function(bike) {
            if (bike.latitude && bike.longitude) {
                L.marker([bike.latitude, bike.longitude]).addTo(map)
                    .bindPopup('<b>' + bike.name + '</b><br>' + bike.location);
            }
        });
    </script>
</body>

</html>
