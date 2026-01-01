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

// Get referral statistics
$referral_stats_query = "SELECT 
                            (SELECT COUNT(*) FROM users WHERE referrer_id = ?) as total_referrals,
                            (SELECT COUNT(*) FROM users WHERE referrer_id = ? AND vip_level > 0) as active_referrals,
                            (SELECT SUM(amount) FROM transactions WHERE user_id IN (SELECT id FROM users WHERE referrer_id = ?) AND type = 'referral_bonus') as total_earnings";
$stmt = $conn->prepare($referral_stats_query);
$stmt->bind_param("iii", $user_id, $user_id, $user_id);
$stmt->execute();
$referral_stats_result = $stmt->get_result();
$referral_stats = $referral_stats_result->fetch_assoc();

// Get direct referrals (level 1)
$direct_referrals_query = "SELECT id, first_name, last_name, email, created_at, balance, vip_level 
                           FROM users 
                           WHERE referrer_id = ? 
                           ORDER BY created_at DESC";
$stmt = $conn->prepare($direct_referrals_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$direct_referrals = $stmt->get_result();

// Get referral earnings
$referral_earnings_query = "SELECT type, amount, created_at FROM transactions WHERE user_id = ? AND type = 'referral_bonus' ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($referral_earnings_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$referral_earnings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COOM-MARKETING - Team</title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            border: 1px solid var(--border-color);
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: var(--accent-color-light);
            margin: 10px 0;
        }
        
        .stat-label {
            font-size: 16px;
            color: var(--text-secondary);
        }
        
        .referral-link {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            text-align: center;
            border: 1px solid var(--border-color);
        }
        
        .referral-input {
            display: flex;
            margin: 20px 0;
        }
        
        .referral-url {
            flex: 1;
            padding: 12px 15px;
            border-radius: 8px 0 0 8px;
            border: 1px solid var(--border-color);
            background-color: var(--secondary-bg);
            color: var(--text-color);
            font-size: 16px;
        }
        
        .copy-btn {
            padding: 12px 20px;
            border-radius: 0 8px 8px 0;
            border: 1px solid var(--border-color);
            background-color: var(--accent-color);
            color: white;
            cursor: pointer;
            font-weight: bold;
        }
        
        .referral-section {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--accent-color-light);
        }
        
        .referral-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .referral-item:last-child {
            border-bottom: none;
        }
        
        .referral-info {
            display: flex;
            flex-direction: column;
        }
        
        .referral-name {
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .referral-date {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .referral-amount {
            font-weight: 600;
        }
        
        .referral-level {
            padding: 4px 8px;
            border-radius: 4px;
            background-color: var(--secondary-bg);
            font-size: 12px;
        }
        
        .no-referrals {
            text-align: center;
            padding: 30px;
            color: var(--text-secondary);
        }
        
        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 20px;
        }
        
        .share-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            font-size: 16px;
        }
        
        .whatsapp {
            background-color: #25D366;
        }
        
        .facebook {
            background-color: #4267B2;
        }
        
        .twitter {
            background-color: #1DA1F2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-users"></i> My Team</h1>
            <p>Manage your referral network and track earnings</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $referral_stats['total_referrals']; ?></div>
                <div class="stat-label">Total Referrals</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $referral_stats['active_referrals']; ?></div>
                <div class="stat-label">Active Referrals</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">RWF <?php echo number_format($referral_stats['total_earnings'] ?? 0, 2); ?></div>
                <div class="stat-label">Total Earnings</div>
            </div>
        </div>
        
        <div class="referral-link">
            <h3 style="margin-bottom: 15px; color: var(--accent-color-light);">Your Referral Link</h3>
            <p style="color: var(--text-secondary); margin-bottom: 15px;">Share this link to earn referral bonuses</p>
            <div class="referral-input">
                <input type="text" class="referral-url" value="https://coom-marketing.com/auth/register?ref=<?php echo $user['referral_code']; ?>" readonly>
                <button class="copy-btn" onclick="copyReferralLink()">Copy</button>
            </div>
            <div class="share-buttons">
                <a href="https://wa.me/?text=<?php echo urlencode('Join COOM-MARKETING and start earning today! ' . 'https://coom-marketing.com/auth/register?ref=' . $user['referral_code']); ?>" class="share-btn whatsapp" target="_blank">
                    <i class="fab fa-whatsapp"></i>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://coom-marketing.com/auth/register?ref=' . $user['referral_code']); ?>" class="share-btn facebook" target="_blank">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode('Join COOM-MARKETING and start earning today! ' . 'https://coom-marketing.com/auth/register?ref=' . $user['referral_code']); ?>" class="share-btn twitter" target="_blank">
                    <i class="fab fa-twitter"></i>
                </a>
            </div>
        </div>
        
        <div class="referral-section">
            <div class="section-header">
                <h3 class="section-title">Direct Referrals (Level 1)</h3>
            </div>
            <?php if ($direct_referrals->num_rows > 0): ?>
                <?php while ($referral = $direct_referrals->fetch_assoc()): ?>
                    <div class="referral-item">
                        <div class="referral-info">
                            <div class="referral-name"><?php echo htmlspecialchars($referral['first_name'] . ' ' . $referral['last_name']); ?></div>
                            <div class="referral-date">Joined: <?php echo date('M j, Y', strtotime($referral['created_at'])); ?></div>
                        </div>
                        <div class="referral-amount">
                            <div>RWF <?php echo number_format($referral['balance'], 2); ?></div>
                            <div class="referral-level">VIP <?php echo $referral['vip_level']; ?></div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-referrals">No direct referrals yet</div>
            <?php endif; ?>
        </div>
        
        <div class="referral-section">
            <div class="section-header">
                <h3 class="section-title">Recent Referral Earnings</h3>
            </div>
            <?php if ($referral_earnings->num_rows > 0): ?>
                <?php while ($earning = $referral_earnings->fetch_assoc()): ?>
                    <div class="referral-item">
                        <div class="referral-info">
                            <div class="referral-name">Referral Bonus</div>
                            <div class="referral-date"><?php echo date('M j, Y', strtotime($earning['created_at'])); ?></div>
                        </div>
                        <div class="referral-amount deposit">+RWF <?php echo number_format($earning['amount'], 2); ?></div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-referrals">No referral earnings yet</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function copyReferralLink() {
            const referralInput = document.querySelector('.referral-url');
            referralInput.select();
            document.execCommand('copy');
            
            // Show feedback
            const copyBtn = document.querySelector('.copy-btn');
            const originalText = copyBtn.textContent;
            copyBtn.textContent = 'Copied!';
            setTimeout(() => {
                copyBtn.textContent = originalText;
            }, 2000);
        }
    </script>
</body>
</html>