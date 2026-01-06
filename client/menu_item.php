<?php
session_start();
include '../includes/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get menu item ID from URL
$menu_item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get menu item details
$menu_query = "SELECT * FROM menu_items WHERE id = ?";
$stmt = $conn->prepare($menu_query);
$stmt->bind_param("i", $menu_item_id);
$stmt->execute();
$menu_result = $stmt->get_result();

if ($menu_result->num_rows === 0) {
    // Menu item not found
    header("Location: index.php");
    exit();
}

$menu_item = $menu_result->fetch_assoc();

// Page title based on menu item
$page_title = $menu_item['name'] . " | Coom Marketing Wallet";

// Get user data for header
$user_query = "SELECT first_name, last_name, vip_level FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$user_name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
$vip_level = $user['vip_level'] ?? 0;

// Function to get content based on menu item
function getMenuItemContent($menu_item_id, $conn) {
    switch ($menu_item_id) {
        case 1: // Account
            return getAccountContent($conn);
        case 2: // Recharge
            return getRechargeContent($conn);
        case 3: // Withdraw
            return getWithdrawContent($conn);
        case 4: // Financial Records
            return getRecordsContent($conn);
        case 5: // Transfer
            return getTransferContent($conn);
        case 6: // Change Password
            return getChangePasswordContent();
        default:
            return "<p>Content not available</p>";
    }
}

// Content functions for each menu item
function getAccountContent($conn) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT email, phone_number, created_at FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    return '
    <div class="account-details">
        <h2>Account Information</h2>
        <div class="detail-row">
            <span class="detail-label">Email:</span>
            <span class="detail-value">'.htmlspecialchars($user['email']).'</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Phone:</span>
            <span class="detail-value">'.htmlspecialchars($user['phone_number'] ?? 'Not set').'</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Member Since:</span>
            <span class="detail-value">'.date('F j, Y', strtotime($user['created_at'])).'</span>
        </div>
    </div>';
}

function getRechargeContent($conn) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT balance FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    return '
    <div class="recharge-form">
        <h2>Recharge Your Account</h2>
        <p>Current Balance: $'.number_format($user['balance'], 2).'</p>
        <form action="process_recharge.php" method="post">
            <div class="form-group">
                <label for="amount">Amount (USD)</label>
                <input type="number" id="amount" name="amount" min="10" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="method">Payment Method</label>
                <select id="method" name="method" required>
                    <option value="crypto">Crypto Wallet</option>
                    <option value="bank">Bank Transfer</option>
                </select>
            </div>
            <button type="submit" class="submit-btn">Proceed to Payment</button>
        </form>
    </div>';
}

function getWithdrawContent($conn) {
    // Similar implementation to recharge but for withdrawals
    return '<h2>Withdraw Funds</h2><p>Withdrawal form goes here</p>';
}

function getRecordsContent($conn) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $html = '<div class="transaction-history"><h2>Transaction History</h2><table>';
    $html .= '<tr><th>Date</th><th>Type</th><th>Amount</th><th>Status</th></tr>';
    
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>'.date('M j, Y', strtotime($row['created_at'])).'</td>';
        $html .= '<td>'.ucfirst($row['type']).'</td>';
        $html .= '<td>$'.number_format($row['amount'], 2).'</td>';
        $html .= '<td>'.ucfirst($row['status']).'</td>';
        $html .= '</tr>';
    }
    
    $html .= '</table></div>';
    return $html;
}

function getTransferContent($conn) {
    return '<h2>Transfer Funds</h2><p>Transfer form goes here</p>';
}

function getChangePasswordContent() {
    return '
    <div class="password-form">
        <h2>Change Password</h2>
        <form action="process_password_change.php" method="post">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required minlength="8">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
            </div>
            <button type="submit" class="submit-btn">Change Password</button>
        </form>
    </div>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        /* Base styles from index.php */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #1e2430;
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
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-size: 18px;
            font-weight: bold;
        }
        
        .logo-icon {
            width: 24px;
            height: 24px;
            background-color: #f3b71b;
            border-radius: 4px;
            margin-right: 8px;
        }
        
        .back-btn {
            color: white;
            text-decoration: none;
            font-size: 24px;
            margin-right: 10px;
        }
        
        .content-container {
            padding: 20px;
        }
        
        /* Form styles */
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #ccc;
        }
        
        input, select {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #2a3547;
            background-color: #2a3547;
            color: white;
        }
        
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 10px;
        }
        
        /* Transaction table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #2a3547;
        }
        
        th {
            color: #f3b71b;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="index.php" class="back-btn">‹</a>
        <div class="logo">
            <div class="logo-icon"></div>
            <div><?php echo $menu_item['name']; ?></div>
        </div>
        <div style="width: 24px;"></div> <!-- Spacer for balance -->
    </div>
    
    <div class="content-container">
        <?php echo getMenuItemContent($menu_item_id, $conn); ?>
    </div>
</body>
</html>