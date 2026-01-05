<?php
session_start();
include '../includes/db_connection.php';
include 'nav.php';

// Get user data
$user_id = $_SESSION['user_id'];
$user_query = "SELECT email, balance, vip_level FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Get notification
$notification_query = "SELECT message FROM notifications WHERE is_active = 1 ORDER BY created_at DESC LIMIT 1";
$notification_result = $conn->query($notification_query);
$notification = $notification_result->fetch_assoc();

// Get user's active investments
$investments_query = "SELECT p.name as product_name, p.daily_earning, p.cycle, ip.purchase_date, ip.status
                      FROM user_products ip
                      JOIN products p ON ip.product_id = p.id
                      WHERE ip.user_id = ? AND ip.status = 'active'";
$stmt = $conn->prepare($investments_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$investments_result = $stmt->get_result();

// Get recent transactions
$transactions_query = "SELECT type, amount, created_at FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($transactions_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions_result = $stmt->get_result();

// Get market data for trading view
$market_data = [
    ['name' => 'COOM-USD', 'price' => 12.45, 'change' => 2.35, 'change_type' => 'positive'],
    ['name' => 'COOM-EUR', 'price' => 11.02, 'change' => -0.85, 'change_type' => 'negative'],
    ['name' => 'COOM-BTC', 'price' => 0.000234, 'change' => 1.25, 'change_type' => 'positive'],
    ['name' => 'COOM-ETH', 'price' => 0.00156, 'change' => 0.75, 'change_type' => 'positive']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COOM Trading - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-bg: #0d1117;
            --secondary-bg: #161b22;
            --card-bg: #1a2029;
            --accent-color: #23a559;
            --accent-color-light: #37c070;
            --text-color: #e6edf3;
            --text-secondary: #7d8590;
            --border-color: #303841;
            --positive: #23a559;
            --negative: #e34c26;
            --header-bg: #0d1117;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--primary-bg);
            color: var(--text-color);
            min-height: 100vh;
            padding-top: 80px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Header styles */
        .header {
            background-color: var(--header-bg);
            border-bottom: 1px solid var(--border-color);
            padding: 15px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: var(--accent-color-light);
        }
        
        .logo i {
            margin-right: 8px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-balance {
            background-color: var(--card-bg);
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: 600;
        }
        
        .user-balance .amount {
            color: var(--accent-color-light);
        }
        
        /* Main dashboard layout */
        .dashboard {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 20px;
            padding: 20px 0;
        }
        
        /* Market data section */
        .market-data {
            background-color: var(--card-bg);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .section-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
        }
        
        .market-list {
            padding: 0;
        }
        
        .market-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .market-item:last-child {
            border-bottom: none;
        }
        
        .market-info {
            display: flex;
            flex-direction: column;
        }
        
        .market-name {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .market-price {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .market-change {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }
        
        .price {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .change {
            font-size: 14px;
            padding: 3px 8px;
            border-radius: 4px;
        }
        
        .positive {
            color: var(--positive);
            background-color: rgba(35, 165, 89, 0.15);
        }
        
        .negative {
            color: var(--negative);
            background-color: rgba(227, 76, 38, 0.15);
        }
        
        /* Trading chart section */
        .trading-chart {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .chart-container {
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(0,0,0,0.2);
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .chart-placeholder {
            text-align: center;
            color: var(--text-secondary);
        }
        
        .chart-placeholder i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        /* Portfolio section */
        .portfolio-section {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
        }
        
        .portfolio-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .portfolio-title {
            font-size: 18px;
            font-weight: 600;
        }
        
        .portfolio-value {
            font-size: 24px;
            font-weight: 700;
            color: var(--accent-color-light);
        }
        
        .investments-list {
            margin-top: 15px;
        }
        
        .investment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .investment-item:last-child {
            border-bottom: none;
        }
        
        .investment-info {
            display: flex;
            flex-direction: column;
        }
        
        .investment-name {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .investment-date {
            font-size: 13px;
            color: var(--text-secondary);
        }
        
        .investment-amount {
            font-weight: 600;
        }
        
        /* Quick actions sidebar */
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .quick-actions-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 15px;
        }
        
        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 15px 10px;
            background-color: var(--secondary-bg);
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-color);
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            background-color: var(--accent-color-light);
            color: white;
        }
        
        .action-icon {
            font-size: 24px;
            margin-bottom: 8px;
        }
        
        .action-text {
            font-size: 12px;
            font-weight: 500;
        }
        
        .recent-transactions {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
        }
        
        .transaction-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .transaction-item:last-child {
            border-bottom: none;
        }
        
        .transaction-type {
            font-weight: 500;
        }
        
        .transaction-amount {
            font-weight: 600;
        }
        
        .deposit {
            color: var(--positive);
        }
        
        .withdrawal {
            color: var(--negative);
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                order: -1;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo-container">
                    <div class="logo">
                        <i class="fas fa-chart-line"></i>COOM Trading
                    </div>
                </div>
                <div class="user-info">
                    <div class="user-balance">
                        Balance: <span class="amount">RWF <?php echo number_format($user['balance'], 2); ?></span>
                    </div>
                    <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="dashboard">
            <!-- Main content area -->
            <div class="main-content">
                <!-- Market Data Section -->
                <div class="market-data">
                    <div class="section-header">
                        <h2 class="section-title">Market Overview</h2>
                        <div class="last-updated">Last updated: Just now</div>
                    </div>
                    <div class="market-list">
                        <?php foreach ($market_data as $item): ?>
                        <div class="market-item">
                            <div class="market-info">
                                <div class="market-name"><?php echo $item['name']; ?></div>
                                <div class="market-price"><?php echo number_format($item['price'], 4); ?></div>
                            </div>
                            <div class="market-change">
                                <div class="price"><?php echo number_format($item['price'], 4); ?></div>
                                <div class="change <?php echo $item['change_type']; ?>"><?php echo ($item['change'] >= 0 ? '+' : '') . number_format($item['change'], 2); ?>%</div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Trading Chart Section -->
                <div class="trading-chart">
                    <div class="section-header">
                        <h2 class="section-title">Trading Chart</h2>
                        <div class="chart-controls">
                            <button class="timeframe-btn active">1D</button>
                            <button class="timeframe-btn">1W</button>
                            <button class="timeframe-btn">1M</button>
                            <button class="timeframe-btn">3M</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <div class="chart-placeholder">
                            <i class="fas fa-chart-line"></i>
                            <p>Trading chart visualization</p>
                            <p class="text-secondary">Real-time market data and trading tools</p>
                        </div>
                    </div>
                </div>
                
                <!-- Portfolio Section -->
                <div class="portfolio-section">
                    <div class="portfolio-header">
                        <div class="portfolio-title">Your Portfolio</div>
                        <div class="portfolio-value">RWF <?php echo number_format($user['balance'], 2); ?></div>
                    </div>
                    <div class="investments-list">
                        <?php if ($investments_result->num_rows > 0): ?>
                            <?php while ($investment = $investments_result->fetch_assoc()): ?>
                            <div class="investment-item">
                                <div class="investment-info">
                                    <div class="investment-name"><?php echo htmlspecialchars($investment['product_name']); ?></div>
                                    <div class="investment-date">Started: <?php echo date('M j, Y', strtotime($investment['purchase_date'])); ?></div>
                                </div>
                                <div class="investment-amount"><?php echo number_format($investment['daily_earning'], 2); ?>/day</div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="investment-item">
                                <div class="investment-info">
                                    <div class="investment-name">No active investments</div>
                                    <div class="investment-date">Start trading to see your portfolio</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar with quick actions and recent transactions -->
            <div class="sidebar">
                <!-- Quick Actions Card -->
                <div class="quick-actions-card">
                    <h3>Quick Actions</h3>
                    <div class="actions-grid">
                        <a href="recharge.php" class="action-btn">
                            <div class="action-icon"><i class="fas fa-plus"></i></div>
                            <div class="action-text">Deposit</div>
                        </a>
                        <a href="withdraw.php" class="action-btn">
                            <div class="action-icon"><i class="fas fa-minus"></i></div>
                            <div class="action-text">Withdraw</div>
                        </a>
                        <a href="products.php" class="action-btn">
                            <div class="action-icon"><i class="fas fa-chart-pie"></i></div>
                            <div class="action-text">Invest</div>
                        </a>
                        <a href="profile.php" class="action-btn">
                            <div class="action-icon"><i class="fas fa-user"></i></div>
                            <div class="action-text">Profile</div>
                        </a>
                    </div>
                </div>
                
                <!-- Recent Transactions Card -->
                <div class="recent-transactions">
                    <h3>Recent Transactions</h3>
                    <?php if ($transactions_result->num_rows > 0): ?>
                        <?php while ($transaction = $transactions_result->fetch_assoc()): ?>
                        <div class="transaction-item">
                            <div class="transaction-type"><?php echo ucfirst($transaction['type']); ?></div>
                            <div class="transaction-amount <?php echo $transaction['type']; ?>"><?php echo number_format($transaction['amount'], 2); ?></div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="transaction-item">
                            <div class="transaction-type">No recent transactions</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update market data periodically
        setInterval(function() {
            // In a real implementation, this would fetch updated market data
            // For now, we'll just update the 'last updated' time
            document.querySelector('.last-updated').textContent = 'Last updated: Just now';
        }, 30000); // Update every 30 seconds

        // Timeframe button interaction
        const timeframeButtons = document.querySelectorAll('.timeframe-btn');
        timeframeButtons.forEach(button => {
            button.addEventListener('click', function() {
                timeframeButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>