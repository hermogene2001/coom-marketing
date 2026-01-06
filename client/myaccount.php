<?php
session_start();
include '../includes/db_connection.php';
include 'nav.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get user's investment statistics
$investments_query = "SELECT COUNT(*) as total_investments, SUM(p.price) as total_invested 
                      FROM user_products ip
                      JOIN products p ON ip.product_id = p.id
                      WHERE ip.user_id = ? AND ip.status = 'active'";
$stmt = $conn->prepare($investments_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$investments_result = $stmt->get_result();
$investments_stats = $investments_result->fetch_assoc();

// Get recent transactions
$transactions_query = "SELECT type, amount, created_at FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($transactions_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions_result = $stmt->get_result();

// Get referral statistics
$referral_stats_query = "SELECT 
                            (SELECT COUNT(*) FROM users WHERE referrer_id = ?) as total_referrals,
                            (SELECT SUM(amount) FROM transactions WHERE user_id IN (SELECT id FROM users WHERE referrer_id = ?) AND type = 'referral_bonus') as total_earnings";
$stmt = $conn->prepare($referral_stats_query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$referral_stats_result = $stmt->get_result();
$referral_stats = $referral_stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COOM-MARKETING - My Account</title>
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--primary-bg);
            color: var(--text-color);
            min-height: 100vh;
            padding-top: 80px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 15px;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid var(--border-color);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--accent-color-light);
        }
        
        .balance-card {
            grid-column: span 2;
        }
        
        .balance-amount {
            font-size: 36px;
            font-weight: bold;
            color: var(--accent-color-light);
            margin: 10px 0;
        }
        
        .balance-label {
            color: var(--text-secondary);
            font-size: 16px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background-color: var(--secondary-bg);
            border-radius: 8px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: var(--accent-color-light);
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .transaction-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
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
        
        .investment {
            color: var(--accent-color-light);
        }
        
        .no-transactions {
            text-align: center;
            padding: 20px;
            color: var(--text-secondary);
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 20px;
        }
        
        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 10px;
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
        
        .account-info {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .account-pic {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: var(--secondary-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            overflow: hidden;
            border: 2px solid var(--border-color);
        }
        
        .account-info h3 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .account-info p {
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .vip-level {
            padding: 4px 8px;
            border-radius: 4px;
            background-color: gold;
            color: black;
            font-weight: bold;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dashboard-grid">
            <div class="card balance-card">
                <div class="card-header">
                    <h2 class="card-title">Account Overview</h2>
                </div>
                <div class="account-info">
                    <div class="account-pic">
                        <svg width="50" height="50" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 21V19C20 16.7909 18.2091 15 16 15H8C5.79086 15 4 16.7909 4 19V21" stroke="var(--text-color)" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="12" cy="7" r="4" stroke="var(--text-color)" stroke-width="2"/>
                        </svg>
                    </div>
                    <div>
                        <h3><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                        <p>VIP Level: <span class="vip-level"><?php echo $user['vip_level']; ?></span></p>
                    </div>
                </div>
                <div class="balance-amount">$ <?php echo number_format($user['balance'], 2); ?></div>
                <div class="balance-label">Available Balance</div>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $investments_stats['total_investments']; ?></div>
                        <div class="stat-label">Active Investments</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">$ <?php echo number_format($investments_stats['total_invested'], 2); ?></div>
                        <div class="stat-label">Total Invested</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo $referral_stats['total_referrals']; ?></div>
                        <div class="stat-label">Referrals</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">$ <?php echo number_format($referral_stats['total_earnings'] ?? 0, 2); ?></div>
                        <div class="stat-label">Referral Earnings</div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="recharge.php" class="action-btn">
                        <div class="action-icon"><i class="fas fa-plus"></i></div>
                        <div class="action-text">Deposit</div>
                    </a>
                    <a href="withdraw.php" class="action-btn">
                        <div class="action-icon"><i class="fas fa-minus"></i></div>
                        <div class="action-text">Withdraw</div>
                    </a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Transactions</h3>
                </div>
                <?php if ($transactions_result->num_rows > 0): ?>
                    <?php while ($transaction = $transactions_result->fetch_assoc()): ?>
                        <div class="transaction-item">
                            <div class="transaction-type"><?php echo ucfirst($transaction['type']); ?></div>
                            <div class="transaction-amount <?php echo $transaction['type']; ?>">$ <?php echo number_format($transaction['amount'], 2); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-transactions">No recent transactions</div>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="action-buttons">
                    <a href="profile.php" class="action-btn">
                        <div class="action-icon"><i class="fas fa-user"></i></div>
                        <div class="action-text">Profile</div>
                    </a>
                    <a href="products.php" class="action-btn">
                        <div class="action-icon"><i class="fas fa-chart-pie"></i></div>
                        <div class="action-text">Invest</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>