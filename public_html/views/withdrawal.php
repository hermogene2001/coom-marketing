<?php
session_start();
include('../includes/db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];

date_default_timezone_set("Africa/Kigali");
$current_time = date('h:i A');

// Fetch user balance with prepared statement
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$balance_result = $result->fetch_assoc();
$balance = $balance_result['balance'];

// Withdrawal settings
$min_withdrawal = 2000;
$max_withdrawal = 200000;
$withdrawal_fee_percent = 8; // 8% withdrawal fee (deducted from amount)
$allowed_withdrawal_time_start = '07:00';
$allowed_withdrawal_time_end = '12:00'; // Changed to 7 PM
$current_time = date('H:i');

// Check if user has made any purchases
$purchase_check = $conn->prepare("SELECT COUNT(*) as purchase_count FROM transactions WHERE client_id = ? AND transaction_type = 'purchase'");
$purchase_check->bind_param("i", $user_id);
$purchase_check->execute();
$purchase_result = $purchase_check->get_result();
$purchase_data = $purchase_result->fetch_assoc();
$has_purchases = $purchase_data['purchase_count'] > 0;

// Check if user has made any Deposit 
$deposit_check = $conn->prepare("SELECT COUNT(*) as deposit_count FROM transactions WHERE client_id = ? AND transaction_type = 'deposit'");
$deposit_check->bind_param("i", $user_id);
$deposit_check->execute();
$deposit_result = $deposit_check->get_result();
$deposit_data = $deposit_result->fetch_assoc();
$has_deposit = $deposit_data['deposit_count'] > 0;

// Process the withdrawal request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // First check if user has made any purchases
    if (!$has_purchases) {
        echo "<script>alert('You must make at least one purchase before withdrawing.'); window.location.href = 'withdrawal.php';</script>";
        exit;
    }
    if (!$has_deposit) {
        echo "<script>alert('You must make at least one Recharge before withdrawing.'); window.location.href = 'recharge.php';</script>";
        exit;
    }

    $requested_amount = (int)$_POST['withdraw_amount'];
    $withdrawal_fee = ($requested_amount * $withdrawal_fee_percent) / 100;
    $amount_after_fee = $requested_amount - $withdrawal_fee;

    // Check if the requested withdrawal amount is valid
    if ($requested_amount < $min_withdrawal || $requested_amount > $max_withdrawal) {
        echo "<script>alert('The minimum withdrawal amount is 2,000 RWF and maximum withdrawal amount is 200,000 RWF.'); window.location.href = 'withdrawal.php';</script>";
        exit;
    }

    // Check if user has bank details
    $bank_check_query = "SELECT * FROM user_banks WHERE user_id = $user_id";
    $bank_check_result = mysqli_query($conn, $bank_check_query);

    if (mysqli_num_rows($bank_check_result) == 0) {
        echo "<script>alert('Please add your bank details before making a withdrawal.'); window.location.href = 'binding_bank.php';</script>";
        exit;
    }

    // Check if the user has enough balance
    if ($balance < $requested_amount) {
        echo "<script>alert('Insufficient balance.'); window.location.href = 'withdrawal.php';</script>";
        exit;
    }

    // Check if the withdrawal request is within the allowed time frame
    if ($current_time >= $allowed_withdrawal_time_start && $current_time <= $allowed_withdrawal_time_end) {
        
        // Check if the user has already withdrawn today
        $check_withdrawal_query = "
            SELECT * FROM transactions 
            WHERE client_id = $user_id 
            AND transaction_type = 'withdrawal' 
            AND DATE(date) = CURDATE()
        ";
        $check_withdrawal_result = mysqli_query($conn, $check_withdrawal_query);
        
        if (mysqli_num_rows($check_withdrawal_result) > 0) {
            echo "<script>alert('You have already made a withdrawal today. Please try again tomorrow.'); window.location.href = 'withdrawal.php';</script>";
            exit;
        }

        // Select a random agent
        $agent_query = "SELECT id, phone_number FROM users WHERE role = 'agent' ORDER BY RAND() LIMIT 1";
        $agent_result = mysqli_fetch_assoc(mysqli_query($conn, $agent_query));
        $agent_id = $agent_result['id'];
        $agent_email = $agent_result['phone_number'];

        // Notification message including fee details
        $subject = "Withdrawal Approval Request";
        $message = "User ID: $user_id has requested a withdrawal of $requested_amount RWF (After 8% fee: $amount_after_fee RWF). Please approve the transaction.";
        mail($agent_email, $subject, $message);

        // Update the user's balance (deduct full requested amount)
        $new_balance = $balance - $requested_amount;
        $update_balance_query = "UPDATE users SET balance = $new_balance WHERE id = $user_id";
        if (!mysqli_query($conn, $update_balance_query)) {
            echo "<script>alert('Error updating balance.'); window.location.href = 'withdrawal.php';</script>";
            exit;
        }

        // Log the withdrawal in the transactions table (store both amounts)
        $log_withdrawal_query = "
            INSERT INTO transactions (client_id, amount, transaction_type, date) 
            VALUES ($user_id, $requested_amount, 'withdrawal', NOW())
        ";
        mysqli_query($conn, $log_withdrawal_query);

        // Insert into withdrawals table
        $withdrawal_query = "
            INSERT INTO withdrawals (client_id, amount, fee, net_amount, transaction_type, status, date) 
            VALUES ($user_id, $requested_amount, $withdrawal_fee, $amount_after_fee, 'withdrawal', 'pending', NOW())
        ";
        mysqli_query($conn, $withdrawal_query);

        echo "<script>
            alert('Withdrawal request submitted!\\n\\nRequested: {$requested_amount} RWF\\nFee (8%): {$withdrawal_fee} RWF\\nYou will receive: {$amount_after_fee} RWF');
            window.location.href = 'withdrawal.php';
        </script>";
    } else {
        echo "<script>alert('Withdrawals are only allowed between 7:00 AM and 7:00 PM.'); window.location.href = 'withdrawal.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw Funds | Harbor Investment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a2a3a;
            --secondary-color: #2c3e50;
            --accent-color: #3498db;
            --text-color: #ecf0f1;
            --fee-color: #f39c12;
        }
        
        body {
            background-color: var(--primary-color);
            color: var(--text-color);
        }
        .container {
            background-color: var(--secondary-color);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            margin-bottom: 100px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .form-control, .form-control:focus {
            background-color: rgba(0, 0, 0, 0.2);
            color: var(--text-color);
            border-color: rgba(255, 255, 255, 0.1);
        }
        .btn-primary {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .alert-info {
            background-color: rgba(23, 162, 184, 0.2);
            border-color: rgba(23, 162, 184, 0.3);
            color: var(--text-color);
        }
        hr {
            border-color: rgba(255, 255, 255, 0.1);
        }
        .table {
            color: var(--text-color);
        }
        .table th, .table td {
            border-color: rgba(255, 255, 255, 0.1);
        }
        .balance-display {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--accent-color);
            margin: 15px 0;
        }
        .fixed-bottom {
            background-color: var(--secondary-color) !important;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .nav-link {
            color: var(--text-color);
            padding: 10px 5px;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--accent-color);
        }
        .nav-link i {
            display: block;
            margin: 0 auto 5px;
            font-size: 1.2rem;
        }
        .status-pending {
            color: #ffc107;
        }
        .status-completed {
            color: #28a745;
        }
        .status-failed {
            color: #dc3545;
        }
        .fee-card {
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid var(--fee-color);
        }
        .fee-highlight {
            color: var(--fee-color);
            font-weight: bold;
        }
        .fee-notice {
            font-size: 0.9rem;
            color: var(--fee-color);
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4"><i class="fas fa-money-bill-wave"></i> Withdraw Funds</h2>
        <?php include'../includes/Real_Time.php' ?>

        <!-- Information section -->
        <div class="alert alert-info" role="alert">
            <h5><i class="fas fa-info-circle"></i> Withdrawal Information</h5>
            <ul class="mb-0">
                <li>Minimum withdrawal: <strong>2,000 RWF</strong></li>
                <li>Maximum withdrawal: <strong>200,000 RWF</strong></li>
                <li><strong class="fee-highlight">8% withdrawal fee</strong> (deducted from amount)</li>
                <li>Example: 10,000 RWF request = <strong>800 RWF fee</strong>, you receive <strong>9,200 RWF</strong></li>
                <li>Allowed time: <strong>7:00 AM - 12:00 PM</strong></li>
                <li>Limit: <strong>1 withdrawal per day</strong></li>
            </ul>
        </div>

        <div class="balance-display">
            <i class="fas fa-wallet"></i> Available Balance: <span>RWF <?php echo number_format($balance); ?></span>
        </div>

        <form action="" method="POST" class="mb-4">
            <div class="mb-3">
                <label for="withdraw_amount" class="form-label">Amount to Withdraw (RWF)</label>
                <input type="number" name="withdraw_amount" id="withdraw_amount" class="form-control form-control-lg" 
                       required min="<?php echo $min_withdrawal; ?>" placeholder="Minimum 2,000 RWF">
                <div class="fee-notice">
                    <i class="fas fa-exclamation-circle"></i> 8% fee will be deducted from this amount
                </div>
            </div>
            
            <div class="fee-card">
                <h6><i class="fas fa-calculator"></i> Withdrawal Calculation</h6>
                <div id="feeCalculation">
                    <div class="d-flex justify-content-between">
                        <span>Amount requested:</span>
                        <span id="displayAmount">0 RWF</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Fee (8%):</span>
                        <span id="displayFee" class="fee-highlight">0 RWF</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fw-bold">
                        <span>You will receive:</span>
                        <span id="displayNetAmount">0 RWF</span>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-start">
                <button type="submit" class="btn btn-primary btn-lg me-md-2">
                    <i class="fas fa-paper-plane"></i> Request Withdrawal
                </button>
                <a href="binding_bank.php" class="btn btn-outline-secondary btn-lg me-md-2">
                    <i class="fas fa-university"></i> Bank Details
                </a>
                <a href="account.php" class="btn btn-success btn-lg">
                    <i class="fas fa-arrow-left"></i> Back to Account
                </a>
            </div>
        </form>

        <h4 class="mt-5 mb-3"><i class="fas fa-history"></i> Recent Withdrawals</h4>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Requested</th>
                        <th>Fee</th>
                        <th>Received</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $withdrawals_query = "SELECT * FROM withdrawals WHERE client_id = $user_id ORDER BY date DESC LIMIT 5";
                    $withdrawals_result = mysqli_query($conn, $withdrawals_query);

                    if (mysqli_num_rows($withdrawals_result) > 0) {
                        while ($withdrawal = mysqli_fetch_assoc($withdrawals_result)) {
                            $status_class = 'status-' . strtolower($withdrawal['status']);
                            echo "<tr>
                                <td>{$withdrawal['id']}</td>
                                <td>" . number_format($withdrawal['amount']) . " RWF</td>
                                <td class='fee-highlight'>" . number_format($withdrawal['fee']) . " RWF</td>
                                <td>" . number_format($withdrawal['net_amount']) . " RWF</td>
                                <td>" . date('M j, g:i A', strtotime($withdrawal['date'])) . "</td>
                                <td class='{$status_class}'>{$withdrawal['status']}</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>No withdrawal history found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Fixed Bottom Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-bottom">
        <div class="container-fluid">
            <div class="row w-100 text-center">
                <div class="col-3 text-center">
                    <a href="client_dashboard.php" class="nav-link">
                        <i class="fas fa-box"></i>
                        <span>Products</span>
                    </a>
                </div>
                <div class="col-3 text-center">
                    <a href="purchased.php" class="nav-link">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Investments</span>
                    </a>
                </div>
                <div class="col-3 text-center">
                    <a href="invite.php" class="nav-link">
                        <i class="fas fa-user-plus"></i>
                        <span>Invite</span>
                    </a>
                </div>
                <div class="col-3 text-center">
                    <a href="account.php" class="nav-link active">
                        <i class="fas fa-user"></i>
                        <span>Account</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Calculate and display withdrawal details in real-time
        document.getElementById('withdraw_amount').addEventListener('input', function() {
            const amount = parseInt(this.value) || 0;
            const feePercent = 8;
            const fee = Math.round((amount * feePercent) / 100);
            const netAmount = amount - fee;
            
            document.getElementById('displayAmount').textContent = amount.toLocaleString() + ' RWF';
            document.getElementById('displayFee').textContent = fee.toLocaleString() + ' RWF';
            document.getElementById('displayNetAmount').textContent = netAmount.toLocaleString() + ' RWF';
            
            // Update validation
            const minAmount = <?php echo $min_withdrawal; ?>;
            const currentBalance = <?php echo $balance; ?>;
            
            if (amount < minAmount) {
                this.setCustomValidity(`Minimum withdrawal is ${minAmount.toLocaleString()} RWF`);
            } else if (amount > currentBalance) {
                this.setCustomValidity('Insufficient balance for this withdrawal');
            } else {
                this.setCustomValidity('');
            }
        });

        // Initialize calculator on page load
        document.getElementById('withdraw_amount').dispatchEvent(new Event('input'));
    </script>
</body>
</html>