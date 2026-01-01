<?php
session_start();
if ($_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

// Database connection
require_once('../includes/db.php');

// Fetch active purchases
$client_id = $_SESSION['user_id'];
$active_query = "
    SELECT products.name, products.price, products.daily_earning, products.cycle, purchases.purchase_date 
    FROM purchases 
    JOIN products ON purchases.product_id = products.id 
    WHERE purchases.client_id = ? 
    AND (CURDATE() <= DATE_ADD(purchases.purchase_date, INTERVAL products.cycle DAY))
    ORDER BY purchases.purchase_date DESC
";
$active_stmt = $conn->prepare($active_query);
$active_stmt->bind_param("i", $client_id);
$active_stmt->execute();
$active_result = $active_stmt->get_result();

// Fetch completed purchases
$completed_query = "
    SELECT products.name, products.price, products.daily_earning, products.cycle, purchases.purchase_date 
    FROM purchases 
    JOIN products ON purchases.product_id = products.id 
    WHERE purchases.client_id = ? 
    AND (CURDATE() > DATE_ADD(purchases.purchase_date, INTERVAL products.cycle DAY))
    ORDER BY purchases.purchase_date DESC
";
$completed_stmt = $conn->prepare($completed_query);
$completed_stmt->bind_param("i", $client_id);
$completed_stmt->execute();
$completed_result = $completed_stmt->get_result();

// Close the database connection
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Investments - Harbor Investment</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

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
            --warning-color: #f59e0b;
            --nav-bg: #1a2a3a;
            --accent-color: #38bdf8;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 80px;
        }
        
        .container {
            padding-top: 2rem;
        }
        
        .welcome-title {
            color: var(--accent-color);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .welcome-subtitle {
            color: var(--text-muted);
            margin-bottom: 2rem;
        }
        
        .section-title {
            color: var(--accent-color);
            margin: 2rem 0 1.5rem;
            font-weight: 600;
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 0.5rem;
        }
        
        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            border-color: var(--primary-color);
        }
        
        .card-title {
            color: var(--accent-color);
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .card-text {
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            color: var(--text-color)
        }
        
        .highlight {
            color: var(--warning-color);
            font-weight: 600;
        }
        
        .navbar-bottom {
            background-color: var(--nav-bg);
            border-top: 1px solid var(--card-border);
            padding: 0.5rem 0;
        }
        
        .nav-btn {
            color: var(--text-color);
            text-align: center;
            padding: 0.5rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .nav-btn:hover {
            color: var(--accent-color);
        }
        
        .nav-btn i {
            display: block;
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }
        
        .no-investments {
            color: var(--text-muted);
            text-align: center;
            padding: 2rem;
            background-color: var(--card-bg);
            border-radius: 8px;
            border: 1px dashed var(--card-border);
            margin-bottom: 2rem;
        }
        
        @media (min-width: 768px) {
            .nav-btn {
                font-size: 1rem;
            }
            
            .nav-btn i {
                display: inline-block;
                margin-right: 0.5rem;
                margin-bottom: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="welcome-title"><i class="fas fa-chart-line me-2"></i>My Investment Portfolio</h1>
        <p class="welcome-subtitle">Track your active and completed investments</p>

        <!-- Active Investments Section -->
        <h3 class="section-title"><i class="fas fa-clock me-2"></i>Active Investments</h3>
        <div class="row">
            <?php if ($active_result->num_rows > 0) { ?>
                <?php while($purchase = $active_result->fetch_assoc()) { 
                    $end_date = date('Y-m-d', strtotime($purchase['purchase_date'] . ' + ' . $purchase['cycle'] . ' days'));
                    $days_remaining = floor((strtotime($end_date) - strtotime('today')) / (60 * 60 * 24));
                ?>
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($purchase['name']); ?></h5>
                                <p class="card-text"><i class="fas fa-tag me-2"></i>Investment: <span class="highlight"><?php echo number_format($purchase['price'], 2); ?> RWF</span></p>
                                <p class="card-text"><i class="fas fa-coins me-2"></i>Daily Earnings: <span class="highlight"><?php echo number_format($purchase['daily_earning'], 2); ?> RWF</span></p>
                                <p class="card-text"><i class="fas fa-calendar-alt me-2"></i>Cycle: <span class="highlight"><?php echo htmlspecialchars($purchase['cycle']); ?> days</span></p>
                                <p class="card-text"><i class="fas fa-calendar-check me-2"></i>Ends in: <span class="highlight"><?php echo $days_remaining > 0 ? $days_remaining . ' days' : 'Today'; ?></span></p>
                                <p class="card-text"><i class="fas fa-calendar-day me-2"></i>Started: <span class="highlight"><?php echo date("M j, Y", strtotime($purchase['purchase_date'])); ?></span></p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="col-12">
                    <div class="no-investments">
                        <i class="fas fa-box-open fa-2x mb-3" style="color: var(--primary-color);"></i>
                        <p>You don't have any active investments yet.</p>
                        <a href="client_dashboard.php" class="btn btn-primary mt-2">
                            <i class="fas fa-shopping-cart me-2"></i>Browse Investments
                        </a>
                    </div>
                </div>
            <?php } ?>
        </div>

        <!-- Completed Investments Section -->
        <h3 class="section-title"><i class="fas fa-check-circle me-2"></i>Completed Investments</h3>
        <div class="row">
            <?php if ($completed_result->num_rows > 0) { ?>
                <?php while($purchase = $completed_result->fetch_assoc()) { 
                    $total_earnings = $purchase['daily_earning'] * $purchase['cycle'];
                ?>
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($purchase['name']); ?></h5>
                                <p class="card-text"><i class="fas fa-tag me-2"></i>Investment: <span class="highlight"><?php echo number_format($purchase['price'], 2); ?> RWF</span></p>
                                <p class="card-text"><i class="fas fa-coins me-2"></i>Daily Earnings: <span class="highlight"><?php echo number_format($purchase['daily_earning'], 2); ?> RWF</span></p>
                                <p class="card-text"><i class="fas fa-chart-line me-2"></i>Total Earnings: <span class="highlight"><?php echo number_format($total_earnings, 2); ?> RWF</span></p>
                                <p class="card-text"><i class="fas fa-calendar-alt me-2"></i>Cycle: <span class="highlight"><?php echo htmlspecialchars($purchase['cycle']); ?> days</span></p>
                                <p class="card-text"><i class="fas fa-calendar-day me-2"></i>Completed: <span class="highlight"><?php echo date("M j, Y", strtotime($purchase['purchase_date'] . ' + ' . $purchase['cycle'] . ' days')); ?></span></p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="col-12">
                    <div class="no-investments">
                        <i class="fas fa-check-circle fa-2x mb-3" style="color: var(--success-color);"></i>
                        <p>No completed investments yet.</p>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Fixed Bottom Navbar -->
    <nav class="navbar navbar-bottom fixed-bottom">
        <div class="container-fluid">
            <div class="row w-100">
                <div class="col-3">
                    <a href="client_dashboard.php" class="nav-btn">
                        <i class="fas fa-box"></i>
                        <span>Invest</span>
                    </a>
                </div>
                <div class="col-3">
                    <a href="purchased.php" class="nav-btn active">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Portfolio</span>
                    </a>
                </div>
                <div class="col-3">
                    <a href="invite.php" class="nav-btn">
                        <i class="fas fa-user-friends"></i>
                        <span>Invite</span>
                    </a>
                </div>
                <div class="col-3">
                    <a href="account.php" class="nav-btn">
                        <i class="fas fa-user-circle"></i>
                        <span>Account</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>