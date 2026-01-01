<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once('../includes/db.php');

if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Get current status
    $query = "SELECT status FROM products WHERE id = $product_id";
    $result = mysqli_query($conn, $query);
    $product = mysqli_fetch_assoc($result);
    
    $new_status = $product['status'] === 'active' ? 'inactive' : 'active';

    // Update status
    $update_query = "UPDATE products SET status = '$new_status' WHERE id = $product_id";
    if (mysqli_query($conn, $update_query)) {
        header('Location: ../admin/products.php?success=Product status updated');
    } else {
        header('Location: ../admin/products.php?error=Failed to update product status');
    }
} else {
    header('Location: ../admin/products.php?error=Invalid request');
}
?>
