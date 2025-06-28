<?php
$pageTitle = 'Manage Bikes';      // set per-page
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
include  __DIR__ . '/includes/header.php';
include  __DIR__ . '/includes/nav.php';

// ————————————————————————————————
// 1) HANDLE CSV IMPORT with header‐validation
// ————————————————————————————————
$importMsg   = '';
$importError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    if ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $importMsg   = 'No CSV file uploaded or upload error.';
        $importError = true;
    } elseif ($_FILES['csv_file']['size'] === 0) {
        $importMsg   = 'Cannot import: the CSV file is empty.';
        $importError = true;
    } else {
        $fh     = fopen($_FILES['csv_file']['tmp_name'], 'r');
        $header = fgetcsv($fh, 1000, ",");

        if (!$header) {
            $importMsg   = 'Cannot read header row.';
            $importError = true;
            fclose($fh);
        } else {
            // normalize & validate header names
            $header   = array_map(fn($h) => strtolower(trim($h)), $header);
            $expected = [
                'name',
                'location',
                'status',
                'desc',
                'image',
                'price',
                'latitude',
                'longitude'
            ];
            $missing = array_diff($expected, $header);

            if ($missing) {
                $importMsg   = 'Missing column(s): ' . implode(', ', $missing);
                $importError = true;
                fclose($fh);
            } else {
                // build header→index map
                $map = array_flip($header);

                // prepare INSERT
                $stmt = $conn->prepare(
                    "INSERT INTO bike
                    (name, location, status, `desc`,
                     image, price, latitude, longitude)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $row    = 0;
                $errors = 0;
                while (($data = fgetcsv($fh, 1000, ",")) !== false) {
                    $row++;
                    // pull by header name
                    $name     = trim($data[$map['name']]     ?? '');
                    $location = trim($data[$map['location']] ?? '');
                    $status   = trim($data[$map['status']]   ?? '');
                    $desc     = trim($data[$map['desc']]     ?? '');
                    $image    = trim($data[$map['image']]    ?? '');
                    $price    = trim($data[$map['price']]    ?? '');
                    $lat      = trim($data[$map['latitude']] ?? '');
                    $lng      = trim($data[$map['longitude']] ?? '');
                    // per‐row validation
                    $validStatus = in_array($status, ['available', 'rented', 'broken']);
                    if (
                        $name === ''
                        || $location === ''
                        || ! $validStatus
                        || ! is_numeric($price)
                        || ! is_numeric($lat)
                        || ! is_numeric($lng)
                    ) {
                        $errors++;
                        continue;
                    }
                    // … inside import loop, after validation …

                    // cast into real variables
                    $price_i = (int)$price;
                    $lat_d   = (float)$lat;
                    $lng_d   = (float)$lng;

                    // now bind
                    $stmt->bind_param(
                        "sssssidd",
                        $name,
                        $location,
                        $status,
                        $desc,
                        $image,
                        $price_i,
                        $lat_d,
                        $lng_d
                    );
                    $stmt->execute();
                }
                fclose($fh);
                $imported    = $row - $errors;
                $importMsg   = "Imported: {$imported} row(s); Skipped: {$errors}";
                $importError = false;
            }
        }
    }
}

// ————————————————————————————————
// 2) HANDLE DELETION
// ————————————————————————————————
if (isset($_GET['delete_id'])) {
    $stmt = $conn->prepare("DELETE FROM bike WHERE id = ?");
    $stmt->bind_param("i", $_GET['delete_id']);
    $stmt->execute();
    header('Location: bikes.php');
    exit();
}

// ————————————————————————————————
// 3) FETCH ALL BIKES
// ————————————————————————————————
$result = $conn->query("SELECT * FROM bike ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="/e-bikers/assets/admin-lte/css/adminlte.min.css" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <?php include __DIR__ . '/includes/nav.php'; ?>

        <div class="content-wrapper p-4">
            <h2 class="d-flex align-items-center">
                Bikes
                <a href="bike-form.php" class="btn btn-success btn-sm ms-3">+ New</a>

                <!-- Import CSV button -->
                <form method="post"
                    enctype="multipart/form-data"
                    class="d-inline-block ms-2">
                    <label class="btn btn-info btn-sm mb-0">
                        Import CSV
                        <input type="file"
                            name="csv_file"
                            accept=".csv"
                            onchange="this.form.submit()"
                            hidden>
                    </label>
                </form>
            </h2>

            <?php if ($importMsg): ?>
                <div class="alert <?= $importError ? 'alert-danger' : 'alert-info' ?> mt-2">
                    <?= htmlspecialchars($importMsg) ?>
                </div>
            <?php endif; ?>

            <table class="table table-striped mt-3">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Description</th>
                        <th>Latitude</th>
                        <th>Longitude</th>
                        <th>Status</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($bike = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $bike['id'] ?></td>
                            <td><?= htmlspecialchars($bike['name']) ?></td>
                            <td><?= htmlspecialchars($bike['location']) ?></td>
                            <td>
                                <?php
                                // optionally truncate long descriptions
                                $d = htmlspecialchars($bike['desc']);
                                echo strlen($d) > 50 ? substr($d, 0, 47) . '…' : $d;
                                ?>
                            </td>
                            <td><?= htmlspecialchars($bike['latitude']) ?></td>
                            <td><?= htmlspecialchars($bike['longitude']) ?></td>
                            <td><?= htmlspecialchars($bike['status']) ?></td>
                            <td>Rp<?= number_format($bike['price'], 0, ',', '.') ?></td>
                            <td>
                                <a href="bike-form.php?id=<?= $bike['id'] ?>"
                                    class="btn btn-sm btn-secondary">Edit</a>
                                <a href="?delete_id=<?= $bike['id'] ?>"
                                    onclick="return confirm('Delete this bike?')"
                                    class="btn btn-sm btn-danger">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="/e-bikers/assets/admin-lte/js/adminlte.min.js"></script>
</body>

</html>

<?php include __DIR__ . '/includes/footer.php'; ?>