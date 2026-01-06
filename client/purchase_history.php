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

// Get user's investment history
$investments_query = "SELECT ip.*, p.name as product_name, p.daily_earning, p.cycle, p.profit_rate
                      FROM user_products ip
                      JOIN products p ON ip.product_id = p.id
                      WHERE ip.user_id = ?
                      ORDER BY ip.purchase_date DESC";
$stmt = $conn->prepare($investments_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$investments_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COOM-MARKETING - Purchase History</title>
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
        
        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: var(--accent-color-light);
        }
        
        .page-header p {
            color: var(--text-secondary);
            font-size: 16px;
        }
        
        .investments-list {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid var(--border-color);
        }
        
        .investment-item {
            display: flex;
            justify-content: space-between;
            padding: 20px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .investment-item:last-child {
            border-bottom: none;
        }
        
        .investment-info {
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        
        .investment-name {
            font-weight: 600;
            font-size: 18px;
            margin-bottom: 5px;
            color: var(--accent-color-light);
        }
        
        .investment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
        }
        
        .detail-label {
            font-size: 12px;
            color: var(--text-secondary);
            margin-bottom: 3px;
        }
        
        .detail-value {
            font-weight: 600;
        }
        
        .investment-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            align-self: flex-start;
        }
        
        .status-active {
            background-color: rgba(35, 165, 89, 0.2);
            color: var(--positive);
        }
        
        .status-inactive {
            background-color: rgba(227, 76, 38, 0.2);
            color: var(--negative);
        }
        
        .investment-amount {
            font-weight: bold;
            font-size: 18px;
            align-self: center;
        }
        
        .no-investments {
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
        }
        
        .investment-date {
            font-size: 14px;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-history"></i> Purchase History</h1>
            <p>Track your investment history and performance</p>
        </div>
        
        <div class="investments-list">
            <h3 style="margin-bottom: 20px; color: var(--accent-color-light);">Investment History</h3>
            <?php if ($investments_result->num_rows > 0): ?>
                <?php while ($investment = $investments_result->fetch_assoc()): ?>
                    <div class="investment-item">
                        <div class="investment-info">
                            <div class="investment-name"><?php echo htmlspecialchars($investment['product_name']); ?></div>
                            <div class="investment-date">Purchased: <?php echo date('M j, Y', strtotime($investment['purchase_date'])); ?></div>
                            <div class="investment-details">
                                <div class="detail-item">
                                    <span class="detail-label">Daily Earning</span>
                                    <span class="detail-value">$ <?php echo number_format($investment['daily_earning'], 2); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Profit Rate</span>
                                    <span class="detail-value"><?php echo $investment['profit_rate']; ?>%</span>
                                </div>
                                <div class="detail-item">
                                    <span class="detail-label">Cycle</span>
                                    <span class="detail-value"><?php echo $investment['cycle']; ?> days</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="investment-status status-<?php echo $investment['status']; ?>">
                                <?php echo ucfirst($investment['status']); ?>
                            </div>
                            <div class="investment-amount">$ <?php echo number_format($investment['daily_earning'] * $investment['cycle'], 2); ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-investments">
                    <i class="fas fa-shopping-cart" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>No investment history yet</h3>
                    <p>Start investing to see your purchase history here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>