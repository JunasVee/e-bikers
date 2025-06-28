<?php
// admin/download_orders.php

require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/db.php';

// Send CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="orders.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// CSV column headings
fputcsv($output, [
    'ID',
    'User',
    'Bike',
    'Status',
    'Start Time',
    'End Time',
    'Price',
    'Created At'
]);

// Fetch orders including start_time, end_time, and price
$sql = "
    SELECT
        o.id,
        a.username,
        b.name   AS bike_name,
        o.status,
        o.start_time,
        o.end_time,
        o.price,
        o.created_at
    FROM `order` o
    JOIN bike    b ON o.bike_id  = b.id
    JOIN account a ON o.user_id  = a.id
    ORDER BY o.created_at DESC
";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['username'],
            $row['bike_name'],
            $row['status'],
            $row['start_time'],
            $row['end_time'],
            $row['price'],
            $row['created_at'],
        ]);
    }
    $result->free();
}

fclose($output);
exit;
?>
