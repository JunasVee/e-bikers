<?php
$pageTitle = 'Manage Bikes';      // set per-page
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';
include   __DIR__ . '/includes/header.php';
include   __DIR__ . '/includes/nav.php';

$id = intval($_GET['id'] ?? 0);
$name = $status = $desc = $location = '';
$price = 0;

// on POST, insert or update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = $_POST['name'];
    $status   = $_POST['status'];
    $price    = intval($_POST['price']);
    $location = $_POST['location'];
    $desc     = $_POST['desc'];

    if ($id) {
        $stmt = $conn->prepare(
            "UPDATE bike SET name=?, status=?, price=?, location=?, `desc`=? WHERE id=?"
        );
        $stmt->bind_param("ssissi", $name, $status, $price, $location, $desc, $id);
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO bike(name,status,price,location,`desc`) VALUES(?,?,?,?,?)"
        );
        $stmt->bind_param("ssiss", $name, $status, $price, $location, $desc);
    }
    $stmt->execute();
    header('Location: bikes.php');
    exit();
}

// if editing, load existing
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM bike WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $bike = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($bike) {
        extract($bike);
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title><?= $id ? 'Edit' : 'New' ?> Bike</title>
</head>

<body>
    <h2><?= $id ? 'Edit' : 'New' ?> Bike</h2>
    <form method="POST">
        <label>Name</label>
        <input name="name" value="<?= htmlspecialchars($name) ?>" /><br>
        <label>Status</label>
        <select name="status">
            <?php foreach (['available', 'rented', 'broken'] as $s): ?>
                <option value="<?= $s ?>" <?= $s === $status ? 'selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select><br>
        <label>Price (per hour)</label>
        <input name="price" value="<?= $price ?>" type="number" /><br>
        <label>Location</label>
        <input name="location" value="<?= htmlspecialchars($location) ?>" /><br>
        <label>Description</label>
        <textarea name="desc"><?= htmlspecialchars($desc) ?></textarea><br>
        <button><?= $id ? 'Update' : 'Create' ?></button>
    </form>
    <p><a href="bikes.php">← Back to list</a></p>
</body>

</html>
<?php include __DIR__ . '/includes/footer.php'; ?>
