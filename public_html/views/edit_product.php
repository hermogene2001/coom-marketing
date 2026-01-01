<?php
// Database connection
require_once('../includes/db.php');

// Check if product ID is provided
if (!isset($_GET['id'])) {
    header('Location: ../admin/dashboard.php?message=No product ID provided');
    exit();
}

$product_id = $_GET['id'];

// Fetch product details from the database
$product_query = "SELECT * FROM products WHERE id = '$product_id'";
$product_result = mysqli_query($conn, $product_query);
$product = mysqli_fetch_assoc($product_result);

// Check if form was submitted for editing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $daily_earning = $_POST['daily_earning'];
    $cycle = $_POST['cycle'];
    $price = $_POST['price'];

    // Check if new image was uploaded
    if ($_FILES['image']['name']) {
        $image = $_FILES['image']['name'];
        $target = "../uploads/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
    } else {
        // Keep the existing image if none is uploaded
        $image = $product['image'];
    }

    // Update product in the database
    $update_query = "UPDATE products SET name = '$name', image = '$image', daily_earning = '$daily_earning', cycle = '$cycle', price = '$price' WHERE id = '$product_id'";
    mysqli_query($conn, $update_query);

    // Redirect back to dashboard with success message
    header('Location: ../admin/products.php?message=Product updated successfully');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>

    <!-- Bootstrap CSS (CDN via jsDelivr) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        body {
            background-color: #f7f7f7;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        .form-label {
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }
        .img-thumbnail {
            border-radius: 8px;
            margin-top: 10px;
        }
        .heading {
            text-align: center;
            margin-bottom: 30px;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="heading">Edit Product</h2>

        <form action="edit_product.php?id=<?php echo $product['id']; ?>" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="productName" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="productName" name="name" value="<?php echo $product['name']; ?>" required>
            </div>
            <div class="mb-4">
                <label for="productImage" class="form-label">Product Image</label>
                <input type="file" class="form-control" id="productImage" name="image">
                <img src="../uploads/<?php echo $product['image']; ?>" class="img-thumbnail" width="100" height="100">
            </div>
            <div class="mb-4">
                <label for="dailyEarning" class="form-label">Daily Earning</label>
                <input type="number" step="0.01" class="form-control" id="dailyEarning" name="daily_earning" value="<?php echo $product['daily_earning']; ?>" required>
            </div>
            <div class="mb-4">
                <label for="cycle" class="form-label">Cycle (days)</label>
                <input type="number" class="form-control" id="cycle" name="cycle" value="<?php echo $product['cycle']; ?>" required>
            </div>
            <div class="mb-4">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo $product['price']; ?>" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Update Product</button>
        </form>
    </div>

    <!-- Bootstrap JS (Bundle with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
