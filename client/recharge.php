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
$user_query = "SELECT first_name, last_name, email, balance, phone_number FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Get payment methods from database
$methods_query = "SELECT * FROM payment_methods WHERE is_active = 1";
$methods_result = $conn->query($methods_query);

// Get available agents from database
$agents_query = "SELECT id, first_name, last_name, phone_number FROM users WHERE role = 'agent' AND status = 'active'";
$agents_result = $conn->query($agents_query);
$has_agents = $agents_result && $agents_result->num_rows > 0;

// Process recharge request
$error = '';
$success = '';
$selected_agent = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);
    $method_id = intval($_POST['method']);
    $source_phone = isset($_POST['source_phone']) ? trim($_POST['source_phone']) : '';
    $use_random_agent = isset($_POST['use_random_agent']) && $_POST['use_random_agent'] == 1;
    
    // Validate inputs
    if ($amount < 2000) {
        $error = 'Minimum recharge amount is 2000 RWF';
    } else {
        // Additional validation for mobile money methods
        $mobile_money_methods = [4, 5]; // Mobile money method IDs
        if (in_array($method_id, $mobile_money_methods) && empty($source_phone)) {
            $error = 'Please enter your mobile money phone number';
        } elseif (in_array($method_id, $mobile_money_methods) && !preg_match('/^\+?\d{8,15}$/', $source_phone)) {
            $error = 'Please enter a valid phone number';
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                $agent_id = null;
                
                // Assign random agent if requested and available
                if ($use_random_agent && $has_agents) {
                    $agents_result->data_seek(0);
                    $agent_ids = [];
                    $agent_details = [];
                    while ($agent = $agents_result->fetch_assoc()) {
                        $agent_ids[] = $agent['id'];
                        $agent_details[$agent['id']] = $agent;
                    }
                    
                    if (count($agent_ids) > 0) {
                        $random_index = array_rand($agent_ids);
                        $agent_id = $agent_ids[$random_index];
                        $selected_agent = $agent_details[$agent_id];
                    }
                }
                
                // Calculate fee based on payment method
                $fee_query = "SELECT fee_percent, name FROM payment_methods WHERE id = ?";
                $stmt = $conn->prepare($fee_query);
                $stmt->bind_param("i", $method_id);
                $stmt->execute();
                $fee_result = $stmt->get_result();
                $method = $fee_result->fetch_assoc();
                $fee = ($amount * $method['fee_percent']) / 100;
                $total_amount = $amount + $fee;
                
                // Generate transaction reference
                $reference = 'DEP' . time() . rand(100, 999);
                
                // Create transaction record
                $insert_transaction = "INSERT INTO transactions (
                    user_id, 
                    amount, 
                    fee, 
                    type, 
                    method_id, 
                    description, 
                    status, 
                    reference
                ) VALUES (?, ?, ?, 'deposit', ?, ?, 'pending', ?)";
                
                $stmt = $conn->prepare($insert_transaction);
                $description = "Deposit via {$method['name']}";
                if (in_array($method_id, $mobile_money_methods)) {
                    $description .= " from $source_phone";
                }
                if ($agent_id) {
                    $description .= " (Assigned to agent #$agent_id)";
                }
                
                $stmt->bind_param(
                    "idddss", 
                    $user_id,
                    $amount, 
                    $fee, 
                    $method_id, 
                    $description, 
                    $reference
                );
                
                $stmt->execute();
                $transaction_id = $conn->insert_id;
                
                // Create recharge record
                $insert_recharge = "INSERT INTO recharges (
                    client_id,
                    agent_id,
                    amount,
                    status,
                    source_phone
                ) VALUES (?, ?, ?, 'pending', ?)";
                
                $stmt = $conn->prepare($insert_recharge);
                $stmt->bind_param("iids", 
                    $user_id,
                    $agent_id,
                    $amount,
                    $source_phone
                );
                $stmt->execute();
                
                // Commit transaction
                $conn->commit();
                
                // Build success message
                $success = '<div class="payment-instructions">';
                $success .= '<h3>Payment Instructions</h3>';
                $success .= '<p>Please send <strong>' . number_format($total_amount, 2) . ' RWF</strong> (Amount + Fee)</p>';
                
                if ($agent_id && $selected_agent) {
                    $success .= '<div class="agent-info">';
                    $success .= '<p><strong>Send to Agent:</strong> ' . htmlspecialchars($selected_agent['first_name'] . ' ' . $selected_agent['last_name']) . '</p>';
                    $success .= '<p><strong>Agent Number:</strong> ' . htmlspecialchars($selected_agent['phone_number']) . '</p>';
                    $success .= '</div>';
                    
                    // JavaScript alert as requested
                    $success .= '<script>alert("Recharge request successful! Please send money to agent number: ' . htmlspecialchars($selected_agent['phone_number']) . '");</script>';
                } else {
                    $success .= '<p>Send to our system payment number: <strong>12345678</strong></p>';
                }
                
                $success .= '<p class="note">After payment, your balance will be updated once we confirm receipt.</p>';
                $success .= '<p class="transaction-id">Transaction ID: ' . $transaction_id . '</p>';
                $success .= '</div>';
                
                // Notify agent if assigned
                if ($agent_id) {
                    $notify_query = "INSERT INTO notifications (user_id, message, type, related_id) 
                                    VALUES (?, ?, 'recharge', ?)";
                    $message = "New recharge request #$transaction_id for " . number_format($amount, 2) . " RWF";
                    $stmt = $conn->prepare($notify_query);
                    $stmt->bind_param("isi", $agent_id, $message, $transaction_id);
                    $stmt->execute();
                }
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = 'Failed to process recharge request: ' . $e->getMessage();
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
    <title>Recharge Account | Coom Marketing Wallet</title>
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
        
        .recharge-form {
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
        .large-checkbox {

            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
        
        .checkbox-label {
            font-size: 16px;
            display: flex;
            align-items: center;
        }
        .submit-btn {
            background-color: #4CAF50;
            color: white;
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
            background-color: #45a049;
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
        
        .method-info {
            background-color: #1e2430;
            border-radius: 6px;
            padding: 10px;
            margin-top: 10px;
            font-size: 13px;
            color: #7a8599;
        }
        
        .quick-amounts {
            margin-top: 10px;
        }
        
        .amount-row {
            display: flex;
            gap: 5px;
            margin-bottom: 5px;
        }
        
        .amount-btn {
            flex: 1;
            background-color: #1e2430;
            color: white;
            border: 1px solid #383f4e;
            border-radius: 4px;
            padding: 8px 0;
            cursor: pointer;
            font-size: 14px;
        }
        
        .amount-btn:hover {
            background-color: #2c3546;
        }
        
        .random-agent-option {
            margin-top: 20px;
            display: flex;
            flex-direction: column;
        }
        
        .random-agent-option input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
        }
        
        .related_id-option label {
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .phone-input-container {
            display: none;
        }
        
        .phone-input-container.visible {
            display: block;
            margin-top: 15px;
        }
        
        .payment-instructions {
            background-color: #2a3547;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .payment-instructions h3 {
            margin-bottom: 15px;
            color: #f3b71b;
        }
        
        .agent-info {
            background-color: #1e2430;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .note {
            font-size: 14px;
            color: #7a8599;
            margin-top: 10px;
        }
        
        .transaction-id {
            font-weight: bold;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="index.php" class="back-btn">‹</a>
        <div class="logo">
            <div class="logo-icon"></div>
            <div>Recharge Account</div>
        </div>
        <div style="width: 24px;"></div>
    </div>
    
    <div class="content-container">
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <?php echo $success; ?>
        <?php endif; ?>
        
        <div class="balance-info">
            <div class="balance-text">Available Balance</div>
            <div class="balance-amount"><?php echo number_format($user['balance'], 2); ?> RWF</div>
        </div>
        
        <div class="recharge-form">
            <form action="recharge.php" method="post">
                <div class="form-group">
                    <label for="amount">Recharge Amount (RWF)</label>
                    <input type="number" id="amount" name="amount" min="10" step="0.01" required>
                    
                    <div class="quick-amounts">
                        <div class="amount-row">
                            <button type="button" class="amount-btn" data-amount="2000">2000</button>
                            <button type="button" class="amount-btn" data-amount="5000">5000</button>
                            <button type="button" class="amount-btn" data-amount="10000">10000</button>
                            <button type="button" class="amount-btn" data-amount="20000">20000</button>
                        </div>
                        <div class="amount-row">
                            <button type="button" class="amount-btn" data-amount="30000">30000</button>
                            <button type="button" class="amount-btn" data-amount="50000">50000</button>
                            <button type="button" class="amount-btn" data-amount="60000">60000</button>
                            <button type="button" class="amount-btn" data-amount="80000">80000</button>
                        </div>
                        <div class="amount-row">
                            <button type="button" class="amount-btn" data-amount="100000">100000</button>
                            <button type="button" class="amount-btn" data-amount="200000">200000</button>
                            <button type="button" class="amount-btn" data-amount="500000">500000</button>
                            <button type="button" class="amount-btn" data-amount="1000000">1000000</button>
                        </div>
                    </div>
                    
                    <div class="method-info">
                        Minimum: 10 RWF | Maximum: 5,000,000 RWF
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="method">Payment Method</label>
                    <select id="method" name="method" required>
                        <?php 
                        $methods_result->data_seek(0);
                        while ($method = $methods_result->fetch_assoc()): ?>
                            <option value="<?php echo $method['id']; ?>">
                                <?php echo htmlspecialchars($method['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <div class="method-info">
                        <?php 
                        $methods_result->data_seek(0);
                        $first_method = $methods_result->fetch_assoc(); 
                        ?>
                        Processing time: <?php echo $first_method['processing_time']; ?> | 
                        Fee: <?php echo $first_method['fee_percent']; ?>%
                    </div>
                    
                    <!-- Phone number input for mobile money -->
                    <div id="phoneInputContainer" class="phone-input-container">
                        <label for="source_phone">Your Mobile Money Number</label>
                        <input type="tel" id="source_phone" name="source_phone" 
                               placeholder="e.g. +256XXXXXXXX" value="<?php echo htmlspecialchars($user['phone_number'] ?? ''); ?>">
                        <div class="method-info">Enter the phone number registered with your mobile money account</div>
                    </div>
                </div>
                
                <?php if ($has_agents): ?>
                    <div class="form-group">

                        <div class="random-agent-option">
                            <input type="checkbox" 
                            id="use_random_agent" 
                            name="use_random_agent" 
                            value="1" 
                            checked
                            hidden
                            class="large-checkbox">
                            <label for="use_random_agent" class="checkbox-label">
                                Assign a random agent to handle this transaction
                            </label>
                            <div class="method-info">
                                An agent will be randomly selected to process your transaction
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <button type="submit" class="submit-btn">Proceed to Payment</button>
            </form>
        </div>
    </div>

    <script>
        // Store method data
        const methods = {
            <?php 
            $methods_result->data_seek(0);
            while ($method = $methods_result->fetch_assoc()): ?>
                <?php echo $method['id']; ?>: {
                    processing_time: '<?php echo $method['processing_time']; ?>',
                    fee: '<?php echo $method['fee_percent']; ?>%',
                    requires_phone: <?php echo in_array($method['id'], [4, 5]) ? 'true' : 'false'; ?>
                },
            <?php endwhile; ?>
        };
        
        const methodSelect = document.getElementById('method');
        const methodInfo = document.querySelector('.method-info');
        const phoneInputContainer = document.getElementById('phoneInputContainer');
        
        function updateMethodInfo() {
            const selectedMethod = methods[methodSelect.value];
            methodInfo.textContent = `Processing time: ${selectedMethod.processing_time} | Fee: ${selectedMethod.fee}`;
            
            // Show/hide phone input based on method
            if (selectedMethod.requires_phone) {
                phoneInputContainer.classList.add('visible');
            } else {
                phoneInputContainer.classList.remove('visible');
            }
        }
        
        methodSelect.addEventListener('change', updateMethodInfo);
        
        // Initialize on page load
        updateMethodInfo();
        
        // Quick amount buttons
        document.querySelectorAll('.amount-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('amount').value = btn.dataset.amount;
            });
        });
    </script>
</body>
</html>