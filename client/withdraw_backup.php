<?php
session_start();
include '../includes/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$user_query = "SELECT first_name, last_name, email, balance, vip_level FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Get withdrawal methods from database
$methods_query = "SELECT * FROM withdrawal_methods WHERE is_active = 1";
$methods_result = $conn->query($methods_query);

// Process withdrawal request
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $method_id = intval($_POST['method']);
    $wallet_address = trim($_POST['wallet_address']);
    
    // Validate inputs
    if ($amount <= 0) {
        $error = 'Amount must be greater than 0';
    } elseif ($amount > $user['balance']) {
        $error = 'Insufficient balance';
    } elseif (empty($wallet_address)) {
        $error = 'Wallet address is required';
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Create withdrawal record
            $insert_query = "INSERT INTO withdrawals (user_id, method_id, amount, wallet_address, status) 
                            VALUES (?, ?, ?, ?, 'pending')";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iids", $user_id, $method_id, $amount, $wallet_address);
            $stmt->execute();
            
            // Update user balance
            $update_query = "UPDATE users SET balance = balance - ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("di", $amount, $user_id);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            $success = 'Withdrawal request submitted successfully!';
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Withdrawal failed: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw Funds | Coom Marketing Wallet</title>
    <style>
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
        
        .balance-info {
            background-color: #2a3547;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .balance-text {
            font-size: 14px;
            color: #7a8599;
            margin-bottom: 5px;
        }
        
        .balance-amount {
            font-size: 22px;
            font-weight: bold;
        }
        
        .withdraw-form {
            background-color: #2a3547;
            border-radius: 10px;
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #ccc;
            font-size: 14px;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #383f4e;
            background-color: #1e2430;
            color: white;
            font-size: 16px;
        }
        
        .submit-btn {
            background-color: #f3b71b;
            color: #1e2430;
            border: none;
            padding: 14px 20px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .submit-btn:hover {
            background-color: #e0a800;
        }
        
        .error-message {
            color: #ff6b6b;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .success-message {
            color: #4CAF50;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="myaccount.php" class="back-btn">‹</a>
        <div class="logo">
            <div class="logo-icon"></div>
            <div>Withdraw Funds</div>
        </div>
        <div style="width: 24px;"></div>
    </div>
    
    <div class="content-container">
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="balance-info">
            <div class="balance-text">Available Balance</div>
            <div class="balance-amount"><?php echo number_format($user['balance'], 2); ?> RWF</div>
        </div>
        
        <div class="withdraw-form">
            <form action="withdraw.php" method="post">
                <div class="form-group">
                    <label for="amount">Withdrawal Amount (RWF)</label>
                    <input type="number" id="amount" name="amount" min="10" step="0.01" required>
                </div>
                
                <div class="form-group">
                    <label for="method">Withdrawal Method</label>
                    <select id="method" name="method" required>
                        <?php while ($method = $methods_result->fetch_assoc()): ?>
                            <option value="<?php echo $method['id']; ?>">
                                <?php echo htmlspecialchars($method['name']); ?> 
                                (Fee: <?php echo $method['fee_percent']; ?>%)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="wallet_address">Wallet Address/ Phone Number</label>
                    <input type="text" id="wallet_address" name="wallet_address" required>
                </div>
                
                <button type="submit" class="submit-btn">Submit Withdrawal</button>
            </form>
        </div>
    </div>
</body>
</html>