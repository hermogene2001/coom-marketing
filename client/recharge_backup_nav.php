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
    
    // Validate amount
    if ($amount <= 0) {
        $error = "Amount must be greater than 0";
    } else {
        // Create recharge request
        $stmt = $conn->prepare("INSERT INTO recharges (user_id, amount, payment_method, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("ids", $user_id, $amount, $payment_method);
        
        if ($stmt->execute()) {
            $success = "Recharge request submitted successfully. Please complete the payment using the details below.";
            
            // Get the recharge ID for payment details
            $recharge_id = $conn->insert_id;
        } else {
            $error = "Failed to submit recharge request: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COOM-MARKETING - Deposit</title>
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
        
        .recharge-container {
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
            background-color: var(--accent-color);
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
            background-color: var(--accent-color-light);
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
        
        .payment-details {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid var(--border-color);
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .payment-method {
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-method:hover {
            border-color: var(--accent-color-light);
        }
        
        .payment-method.active {
            border-color: var(--accent-color-light);
            background-color: rgba(35, 165, 89, 0.1);
        }
        
        .payment-method i {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--accent-color-light);
        }
        
        .payment-info {
            margin-top: 20px;
            padding: 15px;
            background-color: var(--secondary-bg);
            border-radius: 8px;
        }
        
        .payment-info h4 {
            color: var(--accent-color-light);
            margin-bottom: 10px;
        }
        
        .payment-info p {
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .payment-info .highlight {
            color: var(--accent-color-light);
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="recharge-container">
            <div class="page-header">
                <h1><i class="fas fa-money-bill-wave"></i> Deposit Funds</h1>
                <p>Add money to your account to start investing</p>
            </div>
            
            <div class="user-balance">
                <div class="balance-label">Current Balance</div>
                <div class="balance-amount">$ <?php echo number_format($user['balance'], 2); ?></div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <h3 style="margin-bottom: 20px; color: var(--accent-color-light);">Deposit Amount</h3>
                <form action="recharge.php" method="post">
                    <div class="form-group">
                        <label for="amount">Amount ($)</label>
                        <input type="number" id="amount" name="amount" min="1" step="0.01" placeholder="Enter amount" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">Select payment method</option>
                            <option value="mobile_money">Mobile Money (MTN, Airtel)</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="credit_card">Credit/Debit Card</option>
                            <option value="paypal">PayPal</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="submit-btn">Submit Deposit Request</button>
                </form>
            </div>
            
            <div class="payment-details">
                <h3 style="margin-bottom: 20px; color: var(--accent-color-light);">Payment Instructions</h3>
                <div class="payment-info">
                    <h4><i class="fas fa-info-circle"></i> How to Complete Your Deposit</h4>
                    <p>1. Submit your deposit request using the form above</p>
                    <p>2. You will receive payment details for your selected method</p>
                    <p>3. Complete the payment using the provided details</p>
                    <p>4. Your account will be credited after payment confirmation</p>
                    <p><strong>Note:</strong> Processing time may vary depending on the payment method selected.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>