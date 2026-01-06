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
        if ($payment_method === 'binance') {
            // For Binance, find an agent with a Binance address
            $agent_query = "SELECT id, binance_address FROM users WHERE role = 'agent' AND binance_address IS NOT NULL AND binance_address != '' ORDER BY RAND() LIMIT 1";
            $agent_result = $conn->query($agent_query);
            
            if ($agent_result->num_rows > 0) {
                $agent = $agent_result->fetch_assoc();
                $agent_id = $agent['id'];
                $assigned_binance_address = $agent['binance_address'];
                
                // Create recharge request with source phone and status 'pending_agent_assignment'
                $stmt = $conn->prepare("INSERT INTO recharges (user_id, amount, payment_method, source_phone, status, created_at) VALUES (?, ?, ?, ?, 'pending_agent_assignment', NOW())");
                $stmt->bind_param("idss", $user_id, $amount, $payment_method, $user['phone_number']);
                
                if ($stmt->execute()) {
                    $recharge_id = $conn->insert_id;
                    
                    // Assign the recharge to the selected agent
                    $assign_stmt = $conn->prepare("INSERT INTO recharge_agent_assignments (recharge_id, agent_id) VALUES (?, ?)");
                    $assign_stmt->bind_param("ii", $recharge_id, $agent_id);
                    $assign_stmt->execute();
                    $assign_stmt->close();
                    
                    $success = "Recharge request submitted successfully. Please send the exact amount to the Binance address below.";
                    $binance_deposit_address = $assigned_binance_address;
                } else {
                    $error = "Failed to submit recharge request: " . $conn->error;
                }
            } else {
                $error = "No agents with Binance addresses available at this time. Please try again later or select another payment method.";
            }
        } else {
            // For non-Binance methods, proceed as before
            $stmt = $conn->prepare("INSERT INTO recharges (user_id, amount, payment_method, source_phone, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
            $stmt->bind_param("idss", $user_id, $amount, $payment_method, $user['phone_number']);
            
            if ($stmt->execute()) {
                $success = "Recharge request submitted successfully. Please complete the payment using the details below.";
                $recharge_id = $conn->insert_id;
            } else {
                $error = "Failed to submit recharge request: " . $conn->error;
            }
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
        
        .binance-info {
            background-color: #f0b90b;
            color: #000;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .binance-info h4 {
            color: #000;
            margin-bottom: 10px;
        }
        
        .deposit-address {
            background-color: var(--secondary-bg);
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            word-break: break-all;
            font-family: monospace;
        }
        
        .copy-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        
        .copy-btn:hover {
            background-color: var(--accent-color-light);
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
                            <option value="binance">Binance</option>
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
                
                <?php if (isset($binance_deposit_address) && $binance_deposit_address): ?>
                <div id="binanceInfo" class="binance-info">
                    <h4><i class="fab fa-btc"></i> Binance Deposit Details</h4>
                    <p><strong>Send to this address:</strong></p>
                    <div class="deposit-address">
                        <?php echo htmlspecialchars($binance_deposit_address); ?>
                    </div>
                    <button class="copy-btn" onclick="copyToClipboard('<?php echo $binance_deposit_address; ?>')">Copy Address</button>
                    <p><strong>Amount to Send:</strong> <?php echo number_format($amount, 2); ?> USD (or equivalent in USDT)</p>
                    <p><strong>Important:</strong> Send the exact amount to the address above. After sending, your recharge will be pending agent approval.</p>
                    <p>Do not send from an exchange wallet that requires KYC verification as this may prevent your deposit from being credited.</p>
                </div>
                <?php else: ?>
                <div id="binanceInfo" class="binance-info" style="display: none;">
                    <h4><i class="fab fa-btc"></i> Binance Payment Instructions</h4>
                    <p><strong>Step 1:</strong> Select Binance as your payment method</p>
                    <p><strong>Step 2:</strong> Submit your recharge request</p>
                    <p><strong>Step 3:</strong> You will receive a Binance address assigned to one of our agents</p>
                    <p><strong>Step 4:</strong> Send the exact amount to that address</p>
                    <p><strong>Step 5:</strong> Wait for agent to approve your recharge after receiving the funds</p>
                    <p><strong>Important:</strong> Only send supported cryptocurrencies. We will convert to USD at current market rates.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Show/hide Binance instructions based on selection
        document.getElementById('payment_method').addEventListener('change', function() {
            const binanceInfo = document.getElementById('binanceInfo');
            if (this.value === 'binance') {
                binanceInfo.style.display = 'block';
            } else {
                binanceInfo.style.display = 'none';
            }
        });
        
        // Function to copy address to clipboard
        function copyToClipboard(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            
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