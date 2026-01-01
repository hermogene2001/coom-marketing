<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once('../includes/db.php');
include('../includes/menu.php');

// Get count of users
$user_count_query = "SELECT COUNT(*) AS total_users FROM users WHERE role != 'admin'";
$user_count_result = mysqli_query($conn, $user_count_query);
$user_count = mysqli_fetch_assoc($user_count_result)['total_users'];

// Get count of products
$product_count_query = "SELECT COUNT(*) AS total_products FROM products";
$product_count_result = mysqli_query($conn, $product_count_query);
$product_count = mysqli_fetch_assoc($product_count_result)['total_products'];

// Get pending withdrawals
$pending_actions_query = "SELECT COUNT(*) AS pending_actions FROM withdrawals WHERE status = 'pending'";
$pending_actions_result = mysqli_query($conn, $pending_actions_query);
$pending_actions = mysqli_fetch_assoc($pending_actions_result)['pending_actions'];

// Get pending recharges
$pending_recharges_query = "SELECT COUNT(*) AS pending_recharges FROM recharges WHERE status = 'pending'";
$pending_recharges_result = mysqli_query($conn, $pending_recharges_query);
$pending_recharges = mysqli_fetch_assoc($pending_recharges_result)['pending_recharges'];

// Get count of transactions
$transaction_count_query = "SELECT COUNT(*) AS total_transactions FROM transactions";
$transaction_count_result = mysqli_query($conn, $transaction_count_query);
$total_transactions = mysqli_fetch_assoc($transaction_count_result)['total_transactions'];

// Fetch social media links from the database
$social_links_query = "SELECT * FROM social_links WHERE id = 1";
$social_links_result = mysqli_query($conn, $social_links_query);
$social_links = mysqli_fetch_assoc($social_links_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harbor Investment - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
            --card-border: #334155;
            --text-color: #e2e8f0;
            --text-muted: #94a3b8;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
        }
        
        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-title {
            color: var(--text-color);
            font-weight: 600;
        }
        
        .card-text {
            font-size: 1.75rem;
            font-weight: 700;
        }
        
        .border-primary {
            border-color: var(--primary-color) !important;
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        
        h1 {
            color: var(--text-color);
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .metric-card {
            height: 100%;
        }
        
        .action-btn {
            padding: 12px 0;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .action-btn i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col text-center">
                <h1 class="mb-4">Harbor Investment Admin Dashboard</h1>
            </div>
        </div>
        
        <?php include'../includes/Real_Time.php' ?>
        
        <!-- Key Metrics Section -->
        <div class="row g-4 mb-4">
            <!-- Total Users Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card metric-card shadow-sm border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary">Total Users</h5>
                        <p class="card-text"><?= $user_count; ?></p>
                        <a href="users.php" class="btn btn-outline-primary btn-sm">View All Users</a>
                    </div>
                </div>
            </div>
            
            <!-- Total Products Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card metric-card shadow-sm border-success">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success">Total Products</h5>
                        <p class="card-text"><?= $product_count; ?></p>
                        <a href="products.php" class="btn btn-outline-success btn-sm">View All Products</a>
                    </div>
                </div>
            </div>

            <!-- Pending Withdrawals Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card metric-card shadow-sm border-warning">
                    <div class="card-body text-center">
                        <h5 class="card-title text-warning">Pending Withdrawals</h5>
                        <p class="card-text"><?= $pending_actions; ?></p>
                        <a href="pending_withdrawals.php" class="btn btn-outline-warning btn-sm">View Withdrawals</a>
                    </div>
                </div>
            </div>

            <!-- Pending Recharges Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card metric-card shadow-sm border-info">
                    <div class="card-body text-center">
                        <h5 class="card-title text-info">Pending Recharges</h5>
                        <p class="card-text"><?= $pending_recharges; ?></p>
                        <a href="pending_recharges.php" class="btn btn-outline-info btn-sm">View Recharges</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Card -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card metric-card shadow-sm border-secondary">
                    <div class="card-body text-center">
                        <h5 class="card-title text-secondary">Total Transactions</h5>
                        <p class="card-text"><?= $total_transactions; ?></p>
                        <a href="transactions.php" class="btn btn-outline-secondary btn-sm">View All Transactions</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Media Links Section -->
        <?php include 'update_social_links.php'; ?>
        
        <!-- Action Buttons -->
        <div class="row g-4 justify-content-center mt-4">
            <div class="col-lg-4 col-md-6">
                <button class="btn btn-primary w-100 action-btn" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="fas fa-key"></i>Change Password
                </button>
            </div>
            <div class="col-lg-4 col-md-6">
                <button class="btn btn-success w-100 action-btn" data-bs-toggle="modal" data-bs-target="#createAgentModal">
                    <i class="fas fa-user-plus"></i>Create New Agent
                </button>
            </div>
            <div class="col-lg-4 col-md-6">
                <button class="btn btn-info w-100 action-btn" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-box-open"></i>Add New Product
                </button>
            </div>
        </div>
    </div>

    <?php include('modals.php'); // Import modals ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>