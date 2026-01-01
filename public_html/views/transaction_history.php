<?php
session_start();

// Check if the user is logged in and has a 'client' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

// Get the logged-in user's ID
$clientId = $_SESSION['user_id'];

include '../includes/db.php';

// Query to fetch the transaction history for the logged-in client
$sql = "SELECT transaction_type, amount, date FROM transactions WHERE client_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $clientId);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harbor Investment - Transaction History</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a1a2e;
            --secondary-color: #16213e;
            --accent-color: #0f3460;
            --highlight-color: #2d60b8;
            --text-color: #e1e1e1;
            --card-bg: #222536;
            --input-bg: #2c2f44;
            --success-color: #2e7d32;
            --danger-color: #c62828;
            --table-hover: #2a2d42;
            --table-stripe: #262a3d;
            --border-color: #3a3a5a;
        }
        
        body {
            background-color: var(--primary-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin-bottom: 90px;
        }

        .container {
            margin-top: 30px;
        }

        .transaction-card {
            background-color: var(--card-bg);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-title {
            color: var(--highlight-color);
            font-weight: 600;
            margin-bottom: 0;
        }

        .back-button {
            color: #8e8e9a;
            background-color: var(--secondary-color);
            border: 1px solid var(--border-color);
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .back-button:hover {
            color: var(--text-color);
            background-color: var(--accent-color);
        }

        .table {
            color: var(--text-color);
            border-color: var(--border-color);
        }

        .table thead th {
            background-color: var(--accent-color);
            color: var(--text-color);
            font-weight: 600;
            border-color: var(--border-color);
            padding: 12px 15px;
        }

        .table tbody tr:nth-of-type(odd) {
            background-color: var(--table-stripe);
        }
        
        .table tbody tr:nth-of-type(even) {
            background-color: var(--card-bg);
        }

        .table tbody tr:hover {
            background-color: var(--table-hover);
        }

        .table td {
            padding: 14px 15px;
            border-color: var(--border-color);
            vertical-align: middle;
        }

        .transaction-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
        }

        .badge-deposit {
            background-color: rgba(46, 125, 50, 0.15);
            color: #4caf50;
        }

        .badge-withdrawal {
            background-color: rgba(198, 40, 40, 0.15);
            color: #ef5350;
        }

        .badge-purchase {
            background-color: rgba(33, 150, 243, 0.15);
            color: #42a5f5;
        }

        .badge-dividend {
            background-color: rgba(156, 39, 176, 0.15);
            color: #ab47bc;
        }

        .badge-default {
            background-color: rgba(158, 158, 158, 0.15);
            color: #bdbdbd;
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--highlight-color);
            letter-spacing: 1px;
            margin-bottom: 20px;
            text-align: center;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--border-color);
            margin-bottom: 15px;
            opacity: 0.6;
        }

        .empty-state p {
            color: #8e8e9a;
            font-size: 1.1rem;
        }

        /* Bottom navbar styles */
        .bottom-navbar {
            background-color: var(--secondary-color);
            padding: 12px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.2);
            border-top: 1px solid var(--border-color);
            z-index: 1000;
        }

        .bottom-navbar .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .bottom-navbar .nav-link {
            color: #8e8e9a;
            text-align: center;
            padding: 8px 0;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }

        .bottom-navbar .nav-link i {
            font-size: 1.4rem;
            margin-bottom: 4px;
        }

        .bottom-navbar .nav-link:hover, 
        .bottom-navbar .nav-link.active {
            color: var(--highlight-color);
        }
        
        .amount-positive {
            color: #4caf50;
        }
        
        .amount-negative {
            color: #ef5350;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="text-center mb-3">
        <div class="logo-text">
            <i class="fas fa-landmark me-2"></i> Harbor Investment
        </div>
    </div>
    
    <div class="transaction-card">
        <div class="header-section">
            <h4 class="page-title"><i class="fas fa-history me-2"></i>Transaction History</h4>
            <a href="account.php" class="back-button">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>

        <?php if (count($transactions) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Amount (RWF)</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td>
                                    <?php 
                                    $type = strtolower($transaction['transaction_type']);
                                    $badgeClass = 'badge-default';
                                    $icon = 'fa-circle-info';
                                    
                                    if (strpos($type, 'deposit') !== false) {
                                        $badgeClass = 'badge-deposit';
                                        $icon = 'fa-arrow-down';
                                    } elseif (strpos($type, 'withdrawal') !== false) {
                                        $badgeClass = 'badge-withdrawal';
                                        $icon = 'fa-arrow-up';
                                    } elseif (strpos($type, 'purchase') !== false || strpos($type, 'buy') !== false) {
                                        $badgeClass = 'badge-purchase';
                                        $icon = 'fa-shopping-cart';
                                    } elseif (strpos($type, 'dividend') !== false || strpos($type, 'interest') !== false) {
                                        $badgeClass = 'badge-dividend';
                                        $icon = 'fa-chart-line';
                                    }
                                    ?>
                                    <span class="transaction-badge <?php echo $badgeClass; ?>">
                                        <i class="fas <?php echo $icon; ?> me-1"></i>
                                        <?php echo htmlspecialchars($transaction['transaction_type']); ?>
                                    </span>
                                </td>
                                <td class="<?php echo (strpos($type, 'deposit') !== false || strpos($type, 'dividend') !== false) ? 'amount-positive' : 
                                    ((strpos($type, 'withdrawal') !== false || strpos($type, 'purchase') !== false) ? 'amount-negative' : ''); ?>">
                                    <?php 
                                    $prefix = (strpos($type, 'withdrawal') !== false || strpos($type, 'purchase') !== false) ? '-' : 
                                        ((strpos($type, 'deposit') !== false || strpos($type, 'dividend') !== false) ? '+' : '');
                                    echo $prefix . ' ' . number_format($transaction['amount'], 2); 
                                    ?>
                                </td>
                                <td>
                                    <i class="far fa-calendar-alt me-1"></i>
                                    <?php echo date("d M Y", strtotime($transaction['date'])); ?>
                                    <br>
                                    <small class="text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        <?php echo date("H:i:s", strtotime($transaction['date'])); ?>
                                    </small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-receipt"></i>
                <p>No transactions found in your account history.</p>
                <a href="client_dashboard.php" class="btn btn-outline-primary mt-3">
                    <i class="fas fa-chart-line me-2"></i>Go to Dashboard
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bottom Navbar -->
<nav class="bottom-navbar">
    <!-- <div class="container"> -->
        <div class="row text-center">
            <div class="col-3">
                <div class="nav-item">
                    <a href="client_dashboard.php" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
            </div>
            <div class="col-3">
                <div class="nav-item">
                    <a href="purchased.php" class="nav-link">
                        <i class="fas fa-briefcase"></i>
                        <span>Portfolio</span>
                    </a>
                </div>
            </div>
            <div class="col-3">
                <div class="nav-item">
                    <a href="invite.php" class="nav-link">
                        <i class="fas fa-user-plus"></i>
                        <span>Referral</span>
                    </a>
                </div>
            </div>
            <div class="col-3">
                <div class="nav-item">
                    <a href="account.php" class="nav-link active">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>