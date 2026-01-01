<?php
// Database connection
require_once('../includes/db.php');

// Check if product ID is provided
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Delete product from the database
    $delete_query = "DELETE FROM products WHERE id = '$product_id'";
    if (mysqli_query($conn, $delete_query)) {
        // Redirect to dashboard with success message
        header('Location: ../admin/products.php?message=Product deleted successfully');
    } else {
        // Redirect to dashboard with error message
        header('Location: ../admin/products.php?message=Failed to delete product');
    }
} else {
    // Redirect to dashboard with error message
    header('Location: ../admin/products.php?message=No product ID provided');
}
?>
