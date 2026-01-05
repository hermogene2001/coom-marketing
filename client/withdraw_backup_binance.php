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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $payment_method = $_POST['payment_method'];
    $account_number = $_POST['account_number'];
    
    // Validate amount
    if ($amount <= 0) {
        $error = "Amount must be greater than 0";
    } elseif ($amount > $user['balance']) {
        $error = "Insufficient balance for withdrawal";
    } else {
        // Create withdrawal request
        $stmt = $conn->prepare("INSERT INTO withdrawals (user_id, amount, payment_method, account_number, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("issss", $user_id, $amount, $payment_method, $account_number);
        
        if ($stmt->execute()) {
            $success = "Withdrawal request submitted successfully. It will be processed within 24 hours.";
        } else {
            $error = "Failed to submit withdrawal request: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COOM-MARKETING - Withdraw</title>
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
        
        .withdraw-container {
            max-width: 800px;
            margin: 0 auto;
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
        
        .user-balance {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }
        
        .balance-label {
            color: var(--text-secondary);
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .balance-amount {
            font-size: 32px;
            font-weight: bold;
            color: var(--accent-color-light);
        }
        
        .form-container {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        input, select {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--secondary-bg);
            color: var(--text-color);
            font-size: 16px;
        }
        
        .submit-btn {
            background-color: var(--negative);
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: #ff6b6b;
        }
        
        .error-message {
            color: var(--negative);
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background-color: rgba(227, 76, 38, 0.1);
            border-radius: 8px;
        }
        
        .success-message {
            color: var(--positive);
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background-color: rgba(35, 165, 89, 0.1);
            border-radius: 8px;
        }
        
        .withdrawal-info {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: var(--text-secondary);
        }
        
        .info-value {
            font-weight: bold;
        }
        
        .withdrawal-fee {
            color: var(--negative);
        }
        
        .withdrawal-limit {
            color: var(--accent-color-light);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="withdraw-container">
            <div class="page-header">
                <h1><i class="fas fa-money-bill-wave"></i> Withdraw Funds</h1>
                <p>Withdraw your earnings to your preferred payment method</p>
            </div>
            
            <div class="user-balance">
                <div class="balance-label">Available Balance</div>
                <div class="balance-amount">RWF <?php echo number_format($user['balance'], 2); ?></div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="withdrawal-info">
                <h3 style="margin-bottom: 20px; color: var(--accent-color-light);">Withdrawal Information</h3>
                <div class="info-item">
                    <span class="info-label">Minimum Withdrawal</span>
                    <span class="info-value withdraw-limit">RWF 5,000</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Withdrawal Fee</span>
                    <span class="info-value withdrawal-fee">RWF 200</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Processing Time</span>
                    <span class="info-value">24 hours</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Daily Limit</span>
                    <span class="info-value">RWF 1,000,000</span>
                </div>
            </div>
            
            <div class="form-container">
                <h3 style="margin-bottom: 20px; color: var(--accent-color-light);">Withdrawal Details</h3>
                <form action="withdraw.php" method="post">
                    <div class="form-group">
                        <label for="amount">Amount (RWF)</label>
                        <input type="number" id="amount" name="amount" min="5000" max="<?php echo $user['balance']; ?>" step="0.01" placeholder="Enter amount" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">Select payment method</option>
                            <option value="mobile_money">Mobile Money (MTN, Airtel)</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="paypal">PayPal</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="account_number">Account Number</label>
                        <input type="text" id="account_number" name="account_number" placeholder="Enter your account number" required>
                    </div>
                    
                    <button type="submit" class="submit-btn">Submit Withdrawal Request</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>