<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once('../includes/db.php');
include('../includes/menu.php');

// Get count of clients
$client_count_query = "SELECT COUNT(*) AS total_clients FROM users WHERE role != 'admin'";
$client_count_result = mysqli_query($conn, $client_count_query);
$client_count = mysqli_fetch_assoc($client_count_result)['total_clients'];

// Get count of campaigns
$campaign_count_query = "SELECT COUNT(*) AS total_campaigns FROM products";
$campaign_count_result = mysqli_query($conn, $campaign_count_query);
$campaign_count = mysqli_fetch_assoc($campaign_count_result)['total_campaigns'];

// Get pending invoices
$pending_invoices_query = "SELECT COUNT(*) AS pending_invoices FROM withdrawals WHERE status = 'pending'";
$pending_invoices_result = mysqli_query($conn, $pending_invoices_query);
$pending_invoices = mysqli_fetch_assoc($pending_invoices_result)['pending_invoices'];

// Get pending payments
$pending_payments_query = "SELECT COUNT(*) AS pending_payments FROM recharges WHERE status = 'pending'";
$pending_payments_result = mysqli_query($conn, $pending_payments_query);
$pending_payments = mysqli_fetch_assoc($pending_payments_result)['pending_payments'];

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
    <title>Coom Marketing - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FF5733;
            --primary-hover: #E64A2E;
            --dark-bg: #222831;
            --card-bg: #393E46;
            --card-border: #4A4F57;
            --text-color: #EEEEEE;
            --text-muted: #B2B2B2;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
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
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
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
        
        .dashboard-logo {
            max-height: 60px;
            margin-right: 15px;
        }
        
        .header-row {
            margin-bottom: 30px;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row header-row">
            <div class="col text-center">
                <h1 class="mb-0">
                    <i class="fas fa-bullhorn dashboard-logo"></i>
                    Coom Marketing Dashboard
                </h1>
                <p class="text-muted mt-2">Manage your marketing operations</p>
            </div>
        </div>
        
        <?php include'../includes/Real_Time.php' ?>
        
        <!-- Key Metrics Section -->
        <div class="row g-4 mb-4">
            <!-- Total Clients Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card metric-card shadow-sm border-primary">
                    <div class="card-body text-center">
                        <h5 class="card-title text-primary">Total Clients</h5>
                        <p class="card-text"><?= $client_count; ?></p>
                        <a href="users.php" class="btn btn-outline-primary btn-sm">View All Clients</a>
                    </div>
                </div>
            </div>
            
            <!-- Total Campaigns Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card metric-card shadow-sm border-success">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success">Total Campaigns</h5>
                        <p class="card-text"><?= $campaign_count; ?></p>
                        <a href="products.php" class="btn btn-outline-success btn-sm">View All Campaigns</a>
                    </div>
                </div>
            </div>

            <!-- Pending Invoices Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card metric-card shadow-sm border-warning">
                    <div class="card-body text-center">
                        <h5 class="card-title text-warning">Pending Invoices</h5>
                        <p class="card-text"><?= $pending_invoices; ?></p>
                        <a href="pending_withdrawal.php" class="btn btn-outline-warning btn-sm">View Invoices</a>
                    </div>
                </div>
            </div>

            <!-- Pending Payments Card -->
            <div class="col-xl-3 col-md-6">
                <div class="card metric-card shadow-sm border-info">
                    <div class="card-body text-center">
                        <h5 class="card-title text-info">Pending Payments</h5>
                        <p class="card-text"><?= $pending_payments; ?></p>
                        <a href="pending_recharges.php" class="btn btn-outline-info btn-sm">View Payments</a>
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
            
            <!-- Performance Metrics Card -->
            <div class="col-xl-9 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Marketing Performance Overview</h5>
                        <div class="text-muted mb-3">Connect your analytics to see performance metrics</div>
                        <a href="analytics.php" class="btn btn-outline-primary">Connect Analytics</a>
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
                    <i class="fas fa-user-plus"></i>Create Marketing Agent
                </button>
            </div>
            <div class="col-lg-4 col-md-6">
                <button class="btn btn-info w-100 action-btn" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-bullhorn"></i>Add New Campaign
                </button>
            </div>
        </div>
    </div>

    <?php include('modals.php'); // Import modals ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>