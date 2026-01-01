<?php
session_start();

// Check if the user is an admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Database connection
require_once('../includes/db.php');

// Fetch all users
$users_query = "SELECT * FROM users WHERE role != 'admin'";
$users_result = mysqli_query($conn, $users_query);

// Fetch all transactions
$transactions_query = "SELECT t.*, u.phone_number FROM transactions t JOIN users u ON t.client_id = u.id";
$transactions_result = mysqli_query($conn, $transactions_query);

// Fetch all products
$products_query = "SELECT * FROM products";
$products_result = mysqli_query($conn, $products_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Bootstrap CSS (CDN via jsDelivr) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- jQuery (CDN via Google) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Bootstrap JS Bundle (CDN via jsDelivr) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <!-- Logout button -->
        <div class="d-flex justify-content-end">
            <a href="../actions/logout.php" class="btn btn-danger mt-4 position-fixed top-0 end-0 m-3">Logout</a>
        </div>

        <h1>Admin Dashboard</h1>

        <!-- Buttons to trigger modals -->
        <div class="d-flex justify-content-between">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">Change Password</button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createAgentModal">Create New Agent</button>
            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addProductModal">Add New Product</button>
        </div>

        <!-- Users Table -->
        <h2 class="mt-5">All Users</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Phone Number</th>
                    <th>Referral Code</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($user = mysqli_fetch_assoc($users_result)) { ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['phone_number']; ?></td>
                        <td><?php echo $user['referral_code']; ?></td>
                        <td><?php echo $user['role']; ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm edit-btn" data-id="<?php echo $user['id']; ?>" data-phone="<?php echo $user['phone_number']; ?>" data-role="<?php echo $user['role']; ?>">Edit</button>
                            <a href="../actions/delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Transactions Table -->
        <h2 class="mt-5">All Transactions</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Phone</th>
                    <th>Amount</th>
                    <th>Transaction Type</th>
                    <th>Transaction Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while($transaction = mysqli_fetch_assoc($transactions_result)) { ?>
                    <tr>
                        <td><?php echo $transaction['id']; ?></td>
                        <td><?php echo $transaction['phone_number']; ?></td>
                        <td><?php echo $transaction['amount']; ?></td>
                        <td><?php echo $transaction['transaction_type']; ?></td>
                        <td><?php echo $transaction['date']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Products Table -->
        <h2 class="mt-5">All Products</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Image</th>
                    <th>Daily Earning</th>
                    <th>Cycle</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($product = mysqli_fetch_assoc($products_result)) { ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><?php echo $product['name']; ?></td>
                        <td><img src="../uploads/<?php echo $product['image']; ?>" width="50" height="50"></td>
                        <td><?php echo $product['daily_earning']; ?></td>
                        <td><?php echo $product['cycle']; ?></td>
                        <td><?php echo $product['price']; ?></td>
                        <td>
                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="../actions/delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

    </div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../actions/change_password.php" method="POST">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="new_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmNewPassword" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmNewPassword" name="confirm_new_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Create Agent Modal -->
<div class="modal fade" id="createAgentModal" tabindex="-1" aria-labelledby="createAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createAgentModalLabel">Create New Agent</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../actions/create_agent.php" method="POST">
                    <div class="mb-3">
                        <label for="agentPhoneNumber" class="form-label">Agent Phone Number</label>
                        <input type="text" class="form-control" id="agentPhoneNumber" name="phone_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="agentPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" id="agentPassword" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="agentReferralCode" class="form-label">Referral Code</label>
                        <input type="text" class="form-control" id="agentReferralCode" name="referral_code">
                    </div>
                    <button type="submit" class="btn btn-success">Create Agent</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../actions/add_product.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="productName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" id="productName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="productImage" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="productImage" name="image" required>
                    </div>
                    <div class="mb-3">
                        <label for="dailyEarning" class="form-label">Daily Earning</label>
                        <input type="number" class="form-control" id="dailyEarning" name="daily_earning" required>
                    </div>
                    <div class="mb-3">
                        <label for="productCycle" class="form-label">Cycle</label>
                        <input type="number" class="form-control" id="productCycle" name="cycle" required>
                    </div>
                    <div class="mb-3">
                        <label for="productPrice" class="form-label">Price</label>
                        <input type="number" class="form-control" id="productPrice" name="price" required>
                    </div>
                    <button type="submit" class="btn btn-info">Add Product</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="../actions/update_user.php" method="POST">
                    <input type="hidden" name="id" value="">
                    <div class="mb-3">
                        <label for="editPhoneNumber" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="editPhoneNumber" name="phone_number" required>
                    </div>
                    <div class="mb-3">
                        <label for="editRole" class="form-label">Role</label>
                        <select class="form-select" id="editRole" name="role" required>
                            <option value="client">Client</option>
                            <option value="agent">Agent</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).on('click', '.edit-btn', function() {
    var id = $(this).data('id');
    var phone = $(this).data('phone');
    var role = $(this).data('role');
    
    $('#editUserModal input[name="id"]').val(id);
    $('#editUserModal input[name="phone_number"]').val(phone);
    $('#editUserModal select[name="role"]').val(role);
    
    $('#editUserModal').modal('show');
});


</script>

</body>
</html>
