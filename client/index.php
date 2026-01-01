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

// Get tasks
$tasks_query = "SELECT task_name, unlock_amount, required_level, is_locked FROM tasks ORDER BY required_level ASC";
$tasks_result = $conn->query($tasks_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COOM-MARKETING Dashboard</title>
    <style>
        :root {
            --primary-bg: #1a1b20;
            --secondary-bg: #242529;
            --accent-color: #ffc107;
            --text-color: #ffffff;
            --secondary-text: #aaaaaa;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: var(--primary-bg);
            color: var(--text-color);
            min-height: 100vh;
        }
        
        /* Header section */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: var(--primary-bg);
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: bold;
            font-size: 18px;
        }
        
        .logo-icon {
            background-color: var(--accent-color);
            width: 20px;
            height: 30px;
            border-radius: 3px;
        }
        
        .language-selector {
            background-color: #2d2f33;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        /* Notification banner */
        .notification-banner {
            padding: 12px 15px;
            background-color: var(--secondary-bg);
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .notification-icon {
            font-size: 18px;
        }
        
        /* User section */
        .user-section {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background-color: var(--primary-bg);
        }
        
        .user-email {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }
        
        .vip-tag {
            background-color: var(--accent-color);
            color: black;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .copy-icon {
            background-color: #2d2f33;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Balance section */
        .balance-section {
            margin: 10px 15px;
            background-color: var(--secondary-bg);
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }
        
        .balance-text {
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .balance-amount {
            font-size: 20px;
            font-weight: bold;
        }
        
        .balance-amount span {
            color: var(--accent-color);
        }
        
        /* Quick actions */
        .quick-actions {
            display: flex;
            justify-content: space-around;
            padding: 15px;
        }
        
        .action-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        
        .action-icon {
            width: 50px;
            height: 50px;
            background-color: var(--secondary-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--accent-color);
        }
        
        .action-text {
            font-size: 12px;
        }
        
        /* Banner image */
        .banner-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        
        /* Countdown section */
        .countdown-section {
            text-align: center;
            padding: 10px;
        }
        
        .countdown-time {
            font-size: 24px;
            font-weight: bold;
            color: var(--accent-color);
        }
        
        .countdown-text {
            font-size: 14px;
            margin-top: 5px;
        }
        
        /* Task section */
        .task-header {
            padding: 15px;
            font-weight: bold;
        }
        
        .task-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            background-color: var(--secondary-bg);
            margin-bottom: 1px;
        }
        
        .task-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .task-logo {
            width: 40px;
            height: 40px;
            background-color: var(--accent-color);
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: black;
        }
        
        .task-lock {
            font-size: 20px;
        }
        
        .task-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .task-amount {
            font-weight: bold;
        }
        
        .task-amount span {
            color: var(--accent-color);
        }
        
        .task-level {
            font-size: 12px;
            color: var(--secondary-text);
        }
        
        .task-arrow {
            font-size: 22px;
        }
        .content{
            margin-bottom: 100px;
        }
    </style>
</head>


<body>
    <!-- Header -->
  <div class="content">    
      <div class="header">
          <div class="logo">
              <div class="logo-icon"></div>
              COOM-MARKETING
            </div>
            <div class="language-selector">
                🌐 English
        </div>
    </div>
    
    <!-- Notification banner -->
    <div class="notification-banner">
        <div class="notification-icon">🔔</div>
        <div><?php echo htmlspecialchars($notification['message'] ?? 'New Member Registration. Free 10RWF. 2 Invite friends L1 level rebate 12% L2 level rebate 6%'); ?></div>
    </div>
    
    <!-- User section -->
    <div class="user-section">
        <div class="user-email">
            <?php echo htmlspecialchars($user['email']); ?>
            <div class="vip-tag">VIP<?php echo $user['vip_level']; ?></div>
        </div>
        <div class="copy-icon">📋</div>
    </div>
    
    <!-- Balance section -->
    <div class="balance-section">
        <div class="balance-text">Balance</div>
        <div class="balance-amount">RWF <span><?php echo number_format($user['balance'], 2); ?></span></div>
    </div>
    
    <!-- Quick actions -->
    <div class="quick-actions">
        <a href="recharge.php">
        <div class="action-item">
            <div class="action-icon">💰</div>
            <div class="action-text">Recharge</div>
        </div></a>
        <a href="withdraw.php">
        <div class="action-item">
            <div class="action-icon">💸</div>
            <div class="action-text">Withdraw</div>
        </div></a>
        <a href="profile.php">
        <div class="action-item">
            <div class="action-icon">📱</div>
            <div class="action-text">Profile</div>
        </div></a>
        <a href="#">
        <div class="action-item">
            <div class="action-icon">🏢</div>
            <div class="action-text">Company Profile</div>
        </div></a>
    </div>
    
    <!-- Banner image -->
    <?php
    // $banner_query = "SELECT image_url FROM banners WHERE position = 'main' AND is_active = 1 LIMIT 1";
    // $banner_result = $conn->query($banner_query);
    // $banner = $banner_result->fetch_assoc();
    ?>
    <!-- <img src="<?php echo $banner['image_url'] ?? '/api/placeholder/500/120'; ?>" alt="COOM-MARKETING Brand Banner" class="banner-image"> -->
    
    <?php include 'slider.php' ?>
    <!-- Countdown section -->
    <div class="countdown-section">
        <div class="countdown-time">15:02:42</div>
        <div class="countdown-text">Task Reset Countdown</div>
    </div>
    
    <!-- Task section -->
    <div class="task-header">Task Hall</div>
    
    <?php while ($task = $tasks_result->fetch_assoc()): ?>
        <div class="task-item">
            <div class="task-left">
                <div class="task-logo">N</div>
                <div class="task-lock"><?php echo $task['is_locked'] ? '🔒' : '🔓'; ?></div>
                <div class="task-info">
                    <div class="task-amount">Unlock amount: <span>RWF <?php echo number_format($task['unlock_amount'], 2); ?></span></div>
                    <div class="task-level"><?php echo $task['required_level'] === 0 ? 'Junior' : 'VIP' . $task['required_level']; ?></div>
                </div>
            </div>
            <div class="task-arrow">≫</div>
        </div>
        <?php endwhile; ?>
    </div>   
        
        <script>
            // Countdown timer
            function updateCountdown() {
                const countdownElement = document.querySelector('.countdown-time');
                let time = countdownElement.textContent;
                let [hours, minutes, seconds] = time.split(':').map(Number);
                
                if (seconds > 0) {
                    seconds--;
                } else {
                    seconds = 59;
                if (minutes > 0) {
                    minutes--;
                } else {
                    minutes = 59;
                    if (hours > 0) {
                        hours--;
                    } else {
                        hours = 15; // Reset to initial time
                        minutes = 0;
                        seconds = 0;
                    }
                }
            }
            
            countdownElement.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }
        
        // Update countdown every second
        setInterval(updateCountdown, 1000);
        </script>
</body>
</html>