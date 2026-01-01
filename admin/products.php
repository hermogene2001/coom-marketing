<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
require_once('../includes/db.php');
include('../includes/menu.php');

$products_query = "SELECT * FROM products";
$products_result = mysqli_query($conn, $products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Coom Marketing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
            --card-border: #334155;
            --text-color: #e2e8f0;
            --text-muted: #94a3b8;
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
        }
        
        .container {
            padding-top: 2rem;
        }
        
        h2 {
            color: var(--text-color);
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 0.5rem;
        }
        
        .table {
            color: var(--text-color);
            background-color: var(--card-bg);
            border-color: var(--card-border);
        }
        
        .table th {
            background-color: #1a2a3a;
            border-color: var(--card-border);
        }
        
        .table td {
            border-color: var(--card-border);
            vertical-align: middle;
        }
        
        .badge-success {
            background-color: var(--success-color);
        }
        
        .badge-danger {
            background-color: var(--danger-color);
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
            margin: 0.1rem;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
            color: #000;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .btn-info {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid var(--card-border);
        }
        
        .earning-positive {
            color: var(--success-color);
            font-weight: 500;
        }
        
        .price-tag {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .action-buttons {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-box-open me-2"></i>Investment Products</h2>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Image</th>
                        <th>Daily Earning</th>
                        <th>Cycle</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($product = mysqli_fetch_assoc($products_result)) { ?>
                        <tr>
                            <td><?= $product['id']; ?></td>
                            <td><?= $product['name']; ?></td>
                            <td>
                                <img src="../uploads/<?= $product['image']; ?>" class="product-img" alt="<?= $product['name']; ?>">
                            </td>
                            <td class="earning-positive">+<?= $product['daily_earning']; ?>%</td>
                            <td><?= $product['cycle']; ?> days</td>
                            <td class="price-tag">$<?= number_format($product['price'], 2); ?></td>
                            <td>
                                <span class="badge rounded-pill bg-<?= $product['status'] === 'active' ? 'success' : 'danger'; ?>">
                                    <?= ucfirst($product['status']); ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <a href="../views/edit_product.php?id=<?= $product['id']; ?>" class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="../actions/delete_product.php?id=<?= $product['id']; ?>" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this product?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                                <a href="../actions/toggle_product_status.php?id=<?= $product['id']; ?>" class="btn btn-info btn-sm" title="<?= $product['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>">
                                    <i class="fas fa-power-off"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add any additional JavaScript functionality here
    </script>
</body>
</html>