<?php
session_start();
include '../includes/db_connection.php';
include 'nav.php'; 
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data from database
$user_id = $_SESSION['user_id'];
$user_query = "SELECT email, balance, vip_level FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Get pending recharge amount
$recharge_query = "SELECT COALESCE(SUM(amount), 0) AS recharge_amount 
                  FROM transactions 
                  WHERE user_id = ? AND type = 'deposit' AND status = 'pending'";
$stmt = $conn->prepare($recharge_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recharge_result = $stmt->get_result();
$recharge_data = $recharge_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coom Marketing Wallet</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body{
            background-color: #1e2430;
        }
        .allcontent{
            /* background-color: #1e2430; */
            color: white;
            max-width: 480px;
            margin: 0 auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #1e2430;
        }
        
        .logo {
            display: flex;
            align-items: center;
        }
        
        .logo-icon {
            width: 24px;
            height: 24px;
            background-color: #f3b71b;
            border-radius: 4px;
            margin-right: 8px;
        }
        
        .logo-text {
            font-size: 18px;
            font-weight: bold;
        }
        
        .language-select {
            display: flex;
            align-items: center;
            background-color: #2a3547;
            border-radius: 20px;
            padding: 5px 10px;
            font-size: 14px;
        }
        
        .language-icon {
            width: 16px;
            height: 16px;
            background-color: #f3b71b;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .user-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
        }
        
        .user-details {
            display: flex;
            flex-direction: column;
        }
        
        .user-greeting {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .user-status {
            background-color: #f3b71b;
            color: #1e2430;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            width: fit-content;
        }
        
        .wallet-icon {
            width: 50px;
            height: 50px;
            background-color: #4CAF50;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .balance-container {
            background-color: #f8e8b0;
            color: #1e2430;
            border-radius: 10px;
            padding: 15px;
            margin: 0 15px 20px;
        }
        
        .balance-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .balance-label {
            font-size: 14px;
            color: #555;
        }
        
        .balance-amount {
            font-size: 22px;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .menu-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #2a3547;
            cursor: pointer;
        }
        
        .menu-item:hover {
            background-color: #2a3547;
        }
        
        .menu-left {
            display: flex;
            align-items: center;
        }
        
        .menu-icon {
            width: 24px;
            height: 24px;
            background-color: #2a3547;
            border-radius: 6px;
            margin-right: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .arrow-icon {
            color: #6e7c8c;
            font-size: 18px;
        }
        a{
        text-decoration: none;
        color: aliceblue;
         }

        .menu-container{
            margin-bottom: 100px;
         }
    </style>
</head>
<body>
    <div class="allcontent">

        <div class="header">
            <div class="logo">
                <div class="logo-icon"></div>
                <div class="logo-text">Coom Marketing</div>
            </div>
            <div class="language-select">
                <div class="language-icon"></div>
                <span>English</span>
            </div>
        </div>
        
        <!-- User Info Section -->
        <div class="user-info">
            <div class="user-details">
                <div class="user-greeting">Hi, <?php echo htmlspecialchars($user['email']); ?></div>
                <div class="user-status">VIP<?php echo htmlspecialchars($user['vip_level']); ?></div>
            </div>
            <div class="wallet-icon">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 6H21M3 12H21M3 18H21" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    <path d="M12 3V21" stroke="white" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
        </div>
        
        <!-- Balance Section -->
        <div class="balance-container">
            <div class="balance-row">
                <div>
                    <div class="balance-label">Total balance (USD)</div>
                    <div class="balance-amount"><?php echo number_format($user['balance'], 2); ?></div>
                </div>
                <div>
                    <div class="balance-label">Recharge amount (USD)</div>
                    <div class="balance-amount"><?php echo number_format($recharge_data['recharge_amount'], 2); ?></div>
                </div>
            </div>
        </div>
        
        <div class="menu-container">
            <div class="menu-item">
                <div class="menu-left">
                    <div class="menu-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 21V19C20 16.7909 18.2091 15 16 15H8C5.79086 15 4 16.7909 4 19V21" stroke="white" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="12" cy="7" r="4" stroke="white" stroke-width="2"/>
                        </svg>
                    </div>
                    <div><a href="account.php">Account</a></div>
                </div>
                <div class="arrow-icon">›</div>
            </div>
            
            <div class="menu-item">
                <div class="menu-left">
                    <div class="menu-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div><a href="recharge.php">Recharge</a></div>
                </div>
                <div class="arrow-icon">›</div>
            </div>
            
            <div class="menu-item">
                <div class="menu-left">
                    <div class="menu-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2V6M12 22V18M4.93 4.93L7.76 7.76M19.07 19.07L16.24 16.24M2 12H6M22 12H18M4.93 19.07L7.76 16.24M19.07 4.93L16.24 7.76" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div><a href="withdraw.php">Withdraw</a></div>
                </div>
                <div class="arrow-icon">›</div>
            </div>
            
            <div class="menu-item">
                <div class="menu-left">
                    <div class="menu-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M14 2H6C5.46957 2 4.96086 2.21071 4.58579 2.58579C4.21071 2.96086 4 3.46957 4 4V20C4 20.5304 4.21071 21.0391 4.58579 21.4142C4.96086 21.7893 5.46957 22 6 22H18C18.5304 22 19.0391 21.7893 19.4142 21.4142C19.7893 21.0391 20 20.5304 20 20V8L14 2Z" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        <path d="M14 2V8H20" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        <line x1="8" y1="13" x2="16" y2="13" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        <line x1="8" y1="17" x2="16" y2="17" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div><a href="records.php">Financial records</a></div>
            </div>
            <div class="arrow-icon">›</div>
        </div>
        
        <div class="menu-item">
            <div class="menu-left">
                <div class="menu-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22 12H2M17 7L22 12L17 17M7 17L2 12L7 7" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div><a href="transfer.php">Transfe</a>r</div>
            </div>
            <div class="arrow-icon">›</div>
        </div>
        
        <div class="menu-item">
            <div class="menu-left">
                <div class="menu-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="11" width="18" height="11" rx="2" stroke="white" stroke-width="2"/>
                        <path d="M7 11V7C7 4.23858 9.23858 2 12 2C14.7614 2 17 4.23858 17 7V11" stroke="white" stroke-width="2"/>
                    </svg>
                </div>
                <div><a href="change_password.php">Change Password</a></div>
            </div>
            <div class="arrow-icon">›</div>
        </div>
        
        <div class="menu-item">
            <div class="menu-left">
                <div class="menu-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H9" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        <path d="M16 17L21 12L16 7" stroke="white" stroke-width="2" stroke-linecap="round"/>
                        <path d="M21 12H9" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>
                <div><a href="../auth/logout.php">Sign out</a></div>
            </div>
            <div class="arrow-icon">›</div>
        </div>
    </div>
</div>
    
</body>
</html>