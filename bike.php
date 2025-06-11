<?php
$servername = "localhost";
$username = "e-bikers";
$password = "0a9s455r";
$dbname = "e-bikers";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM bike WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$bike = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$bike) die("Bike not found.");
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
            <img src="<?= htmlspecialchars($bike['image']) ?>" class="bike-img mb-3" alt="<?= htmlspecialchars($bike['name']) ?>">
            <h2 class="fw-bold"><?= htmlspecialchars($bike['name']) ?></h2>
            <p class="lead"><?= htmlspecialchars($bike['desc']) ?></p>
            <div class="fw-bold text-primary mb-3" style="font-size:1.5rem;">
                Rp<?= number_format($bike['price'],0,',','.') ?>/jam
            </div>
            <?php if ($bike['status'] == 'available'): ?>
                <form method="POST" action="order.php">
                    <input type="hidden" name="bike_id" value="<?= $bike['id'] ?>">
                    <button class="btn btn-success btn-lg" type="submit">Order &amp; Pay</button>
                </form>
            <?php else: ?>
                <div class="alert alert-warning">Sepeda ini sedang tidak tersedia.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
