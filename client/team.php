<?php
session_start();
include '../includes/db_connection.php';
include 'nav.php'; 

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data and invitation code
$sql = "SELECT first_name, invitation_code, referral_code FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

$referral_code = $user_data['referral_code'];
$invitation_code = $user_data['referral_code'];

// Generate referral link
$referral_link = "http://localhost/COOM-MARKETING/signup.php?referral_code=" . $referral_code;

// Fetch team statistics using the correct tables
// First level referrals (direct)
$sql = "SELECT COUNT(*) as team_size, 
               (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id IN 
                (SELECT id FROM users WHERE invitation_code = ?) AND type = 'deposit' AND status = 'completed') as team_recharge,
               (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id IN 
                (SELECT id FROM users WHERE invitation_code = ?) AND type = 'withdrawal' AND status = 'completed') as team_withdrawal,
               (SELECT COUNT(*) FROM users WHERE invitation_code = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)) as new_team
        FROM users WHERE invitation_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $referral_code, $referral_code, $referral_code, $referral_code);
$stmt->execute();
$result = $stmt->get_result();
$team_stats = $result->fetch_assoc();

// Fetch level 1 referral details
$sql = "SELECT COUNT(*) as valid_count, 
               (SELECT COALESCE(SUM(amount), 0) FROM transactions 
                WHERE type = 'referral_bonus' AND user_id = ?) as total_income
        FROM users WHERE invitation_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $referral_code);
$stmt->execute();
$result = $stmt->get_result();
$level1_stats = $result->fetch_assoc();

// Fetch level 2 referral details
$sql = "SELECT COUNT(*) as valid_count, 
               (SELECT COALESCE(SUM(amount), 0) FROM transactions 
                WHERE type = 'referral_bonus_l2' AND user_id = ?) as total_income
        FROM users WHERE invitation_code IN (SELECT referral_code FROM users WHERE invitation_code = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $referral_code);
$stmt->execute();
$result = $stmt->get_result();
$level2_stats = $result->fetch_assoc();

// Fetch level 3 referral details
$sql = "SELECT COUNT(*) as valid_count, 
               (SELECT COALESCE(SUM(amount), 0) FROM transactions 
                WHERE type = 'referral_bonus_l3' AND user_id = ?) as total_income
        FROM users WHERE invitation_code IN 
        (SELECT referral_code FROM users WHERE invitation_code IN 
        (SELECT referral_code FROM users WHERE invitation_code = ?))";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $referral_code);
$stmt->execute();
$result = $stmt->get_result();
$level3_stats = $result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coom Marketing - Referral Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a1d24;
            --secondary-color: #222831;
            --accent-color: #ffd700;
            --text-color: #ffffff;
            --level1-color: #f8ad4e;
            --level2-color: #e95a89;
            --level3-color: #5bbcd6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: var(--primary-color);
            color: var(--text-color);
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 15px;
            margin-bottom: 100px;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            margin-bottom: 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
        }
        
        .logo i {
            color: var(--accent-color);
            margin-right: 10px;
        }
        
        .language-selector {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 5px 10px;
            border-radius: 15px;
            display: flex;
            align-items: center;
        }
        
        .invitation-code {
            background-color: var(--secondary-color);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .code-label {
            color: #ccc;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .code-display {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .code {
            font-size: 24px;
            font-weight: bold;
        }
        
        .copy-btn {
            background-color: #000;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 15px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .link-display {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
        }
        
        .referral-link {
            color: var(--accent-color);
            max-width: 70%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .social-icons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
        }
        
        .social-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        .stats-container {
            background-color: var(--secondary-color);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .stats-header {
            color: #ccc;
            margin-bottom: 15px;
            font-size: 14px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            text-align: center;
        }
        
        .stat-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .stat-value {
            font-size: 20px;
            font-weight: bold;
        }
        
        .stat-label {
            font-size: 12px;
            color: #ccc;
        }
        
        .level-card {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .level-1 {
            background: linear-gradient(to right, var(--level1-color), #acb6e5);
        }
        
        .level-2 {
            background: linear-gradient(to right, var(--level2-color), #f67280);
        }
        
        .level-3 {
            background: linear-gradient(to right, var(--level3-color), #86a8e7);
        }
        
        .level-icon {
            width: 50px;
            height: 50px;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .level-info {
            flex-grow: 1;
        }
        
        .level-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .level-stats {
            display: flex;
            gap: 20px;
        }
        
        .level-stat {
            display: flex;
            flex-direction: column;
        }
        
        .level-stat-label {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
        }
        
        .level-stat-value {
            font-size: 16px;
            font-weight: bold;
        }
        
        .details-btn {
            background-color: #000;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 15px;
            cursor: pointer;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <i class="fas fa-cubes"></i>
                Coom Marketing
            </div>
            <div class="language-selector">
                <i class="fas fa-globe"></i>
                English
            </div>
        </header>
        
        <div class="invitation-code">
            <div class="code-label">Invitation code:</div>
            <div class="code-display">
                <div class="code"><?php echo $invitation_code; ?></div>
                <button class="copy-btn" onclick="copyToClipboard('<?php echo $invitation_code; ?>')">Copy</button>
            </div>
            <div class="code-label">Share your referral link and start earning</div>
            <div class="link-display">
                <div class="referral-link"><?php echo $referral_link; ?></div>
                <button class="copy-btn" onclick="copyToClipboard('<?php echo $referral_link; ?>')">Copy</button>
            </div>
        </div>
        
        <div class="social-icons">
            <div class="social-icon"><i class="fab fa-x-twitter"></i></div>
            <div class="social-icon"><i class="fab fa-facebook-f"></i></div>
            <div class="social-icon"><i class="fab fa-telegram"></i></div>
            <div class="social-icon"><i class="fab fa-linkedin-in"></i></div>
            <div class="social-icon"><i class="fab fa-whatsapp"></i></div>
            <div class="social-icon"><i class="fab fa-instagram"></i></div>
            <div class="social-icon"><i class="fab fa-tiktok"></i></div>
            <div class="social-icon"><i class="far fa-copy"></i></div>
        </div>
        
        <div class="stats-container">
            <div class="stats-header">Selection period</div>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value"><?php echo number_format($team_stats['team_size']); ?></div>
                    <div class="stat-label">Team size</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">$<?php echo number_format($team_stats['team_recharge'], 2); ?></div>
                    <div class="stat-label">Team recharge</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">$<?php echo number_format($team_stats['team_withdrawal'], 2); ?></div>
                    <div class="stat-label">Team Withdrawal</div>
                </div>
            </div>
            <div class="stats-grid" style="margin-top: 15px;">
                <div class="stat-item">
                    <div class="stat-value"><?php echo number_format($team_stats['new_team']); ?></div>
                    <div class="stat-label">New team</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo isset($team_stats['first_recharge']) ? $team_stats['first_recharge'] : 0; ?></div>
                    <div class="stat-label">First time recharge</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo isset($team_stats['first_withdrawal']) ? $team_stats['first_withdrawal'] : 0; ?></div>
                    <div class="stat-label">First withdrawal</div>
                </div>
            </div>
        </div>
        
        <div class="level-card level-1">
            <div class="level-icon">
                <i class="fas fa-medal"></i>
            </div>
            <div class="level-info">
                <div class="level-title">LEV 1</div>
                <div class="level-stats">
                    <div class="level-stat">
                        <div class="level-stat-label">Register/Valid</div>
                        <div class="level-stat-value"><?php echo $level1_stats['valid_count']; ?>/0</div>
                    </div>
                    <div class="level-stat">
                        <div class="level-stat-label">Commission Percentage</div>
                        <div class="level-stat-value">12%</div>
                    </div>
                </div>
                <div class="level-stat" style="margin-top: 5px;">
                    <div class="level-stat-label">Total Income</div>
                    <div class="level-stat-value"><?php echo number_format($level1_stats['total_income'], 2); ?></div>
                </div>
            </div>
            <button class="details-btn">Details</button>
        </div>
        
        <div class="level-card level-2">
            <div class="level-icon">
                <i class="fas fa-gem"></i>
            </div>
            <div class="level-info">
                <div class="level-title">LEV 2</div>
                <div class="level-stats">
                    <div class="level-stat">
                        <div class="level-stat-label">Register/Valid</div>
                        <div class="level-stat-value"><?php echo $level2_stats['valid_count']; ?>/0</div>
                    </div>
                    <div class="level-stat">
                        <div class="level-stat-label">Commission Percentage</div>
                        <div class="level-stat-value">6%</div>
                    </div>
                </div>
                <div class="level-stat" style="margin-top: 5px;">
                    <div class="level-stat-label">Total Income</div>
                    <div class="level-stat-value"><?php echo number_format($level2_stats['total_income'], 2); ?></div>
                </div>
            </div>
            <button class="details-btn">Details</button>
        </div>
        
        <div class="level-card level-3">
            <div class="level-icon">
                <i class="fas fa-crown"></i>
            </div>
            <div class="level-info">
                <div class="level-title">LEV 3</div>
                <div class="level-stats">
                    <div class="level-stat">
                        <div class="level-stat-label">Register/Valid</div>
                        <div class="level-stat-value"><?php echo $level3_stats['valid_count']; ?>/0</div>
                    </div>
                </div>
                <div class="level-stat" style="margin-top: 5px;">
                    <div class="level-stat-label">Total Income</div>
                    <div class="level-stat-value"><?php echo number_format($level3_stats['total_income'], 2); ?></div>
                </div>
            </div>
            <button class="details-btn">Details</button>
        </div>
    </div>
    
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Copied to clipboard!');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
</body>
</html>