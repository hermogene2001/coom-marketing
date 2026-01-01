<?php
include '../includes/db.php';

// Fetch active products from the database
$query = "SELECT id, name, daily_earning, cycle, price, image FROM products WHERE status = 'active'";
$result = mysqli_query($conn, $query);

$products = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($products);
