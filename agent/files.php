<?php
session_start();
if ($_SESSION['role'] !== 'agent') {
    header("Location: index.php");
    exit;
}

// Database connection
require_once('../includes/db.php');
// include '../includes/function.php';

date_default_timezone_set('Africa/Kigali');

// Check if there is a message to display
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Referral System Logic
function applyReferralBonus($clientId, $rechargeAmount, $conn) {
    // Fetch the Level 1 referrer
    $sql = "SELECT invitation_code FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $stmt->bind_result($level1ReferrerId);
    $stmt->fetch();
    $stmt->close();

    if ($level1ReferrerId) {
        // Apply Level 1 bonus (12%)
        $level1Bonus = $rechargeAmount * 0.12;

        // Update referral bonus
        $sql = "UPDATE users SET referral_bonus = referral_bonus + ?, balance = balance + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ddi", $level1Bonus, $level1Bonus, $level1ReferrerId);
        if (!$stmt->execute()) {
            die('Error applying Level 1 bonus: ' . $stmt->error);
        }
        $stmt->close();

        // Fetch the Level 2 referrer (referrer of Level 1)
        $sql = "SELECT invitation_code FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $level1ReferrerId);
        $stmt->execute();
        $stmt->bind_result($level2ReferrerId);
        $stmt->fetch();
        $stmt->close();

        if ($level2ReferrerId) {
            // Apply Level 2 bonus (1%)
            $level2Bonus = $rechargeAmount * 0.08;

            // Update referral bonus
            $sql = "UPDATE users SET referral_bonus = referral_bonus + ?, balance = balance + ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ddi", $level2Bonus, $level2Bonus, $level2ReferrerId);
            if (!$stmt->execute()) {
                die('Error applying Level 2 bonus: ' . $stmt->error);
            }
            $stmt->close();
        }
    }
}

// Recharge Approval Logic
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['recharge_id'])) {
        $recharge_id = intval($_POST['recharge_id']);
        $action = $_POST['action'];

        if ($action === 'approve') {
            // Fetch recharge details
            $query = "SELECT client_id, amount FROM recharges WHERE id = ? AND agent_id = ? AND status = 'pending'";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $recharge_id, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->bind_result($clientId, $rechargeAmount);
            $stmt->fetch();
            $stmt->close();

            if ($clientId && $rechargeAmount) {
                // Approve recharge
                $update_query = "UPDATE recharges r 
                                 JOIN users u ON r.client_id = u.id 
                                 SET r.status = 'confirmed', u.balance = u.balance + r.amount, r.recharge_time = NOW() 
                                 WHERE r.id = ? AND r.agent_id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("ii", $recharge_id, $_SESSION['user_id']);
                $stmt->execute();
                $stmt->close();

                // Record the deposit as a transaction
                $transaction_query = "INSERT INTO transactions (user_id, type, amount, created_at) 
                              VALUES (?, 'deposit', ?, NOW())";
                $stmt = $conn->prepare($transaction_query);
                $stmt->bind_param("id", $clientId, $rechargeAmount);
                if (!$stmt->execute()) {
                    die('Error recording transaction: ' . $stmt->error);
                }
               $stmt->close();

               // Apply referral bonuses
               applyReferralBonus($clientId, $rechargeAmount, $conn);

               $_SESSION['message'] = "Recharge approved successfully, transaction recorded, and referral bonuses applied!";

            }
        } elseif ($action === 'reject') {
            // Reject recharge
            $reject_query = "UPDATE recharges SET status = 'rejected' WHERE id = ? AND agent_id = ?";
            $stmt = $conn->prepare($reject_query);
            $stmt->bind_param("ii", $recharge_id, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = "Recharge rejected!";
        }
    } elseif (isset($_POST['withdrawal_id'])) {
        // Withdrawal Approval Logic
        $withdrawal_id = intval($_POST['withdrawal_id']);
        $action = $_POST['action'];

        if ($action === 'approve') {
            // Update withdrawal status to 'processing' and set processed time
            $approve_withdrawal_query = "UPDATE withdrawals SET status = 'processing', processed_at = NOW() WHERE id = ? AND status = 'pending'";
            $stmt = $conn->prepare($approve_withdrawal_query);
            $stmt->bind_param("i", $withdrawal_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                // Get withdrawal details to record transaction
                $get_withdrawal = "SELECT user_id, amount FROM withdrawals WHERE id = ?";
                $stmt2 = $conn->prepare($get_withdrawal);
                $stmt2->bind_param("i", $withdrawal_id);
                $stmt2->execute();
                $stmt2->bind_result($userId, $amount);
                $stmt2->fetch();
                $stmt2->close();
                
                // Record the withdrawal as a transaction
                $transaction_query = "INSERT INTO transactions (user_id, type, amount, created_at) 
                                    VALUES (?, 'withdrawal', ?, NOW())";
                $stmt3 = $conn->prepare($transaction_query);
                $stmt3->bind_param("id", $userId, $amount);
                $stmt3->execute();
                $stmt3->close();
                
                $_SESSION['message'] = "Withdrawal approved and is now processing!";
            } else {
                $_SESSION['message'] = "Failed to approve withdrawal or it was already processed!";
            }
            $stmt->close();
            
        } elseif ($action === 'reject') {
            // Get withdrawal amount to restore to user balance
            $get_withdrawal = "SELECT user_id, amount, fee FROM withdrawals WHERE id = ? AND status = 'pending'";
            $stmt = $conn->prepare($get_withdrawal);
            $stmt->bind_param("i", $withdrawal_id);
            $stmt->execute();
            $stmt->bind_result($userId, $amount, $fee);
            $hasRecord = $stmt->fetch();
            $stmt->close();
            
            if ($hasRecord) {
                // Begin transaction
                $conn->begin_transaction();
                
                try {
                    // Restore the amount + fee to user balance
                    $total_refund = $amount + $fee;
                    $restore_balance = "UPDATE users SET balance = balance + ? WHERE id = ?";
                    $stmt = $conn->prepare($restore_balance);
                    $stmt->bind_param("di", $total_refund, $userId);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Update withdrawal status
                    $reject_withdrawal_query = "UPDATE withdrawals SET status = 'rejected', processed_at = NOW() WHERE id = ?";
                    $stmt = $conn->prepare($reject_withdrawal_query);
                    $stmt->bind_param("i", $withdrawal_id);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Record the refund as a transaction
                    $transaction_query = "INSERT INTO transactions (client_id, transaction_type, amount, date) 
                                        VALUES (?, 'withdrawal_refund', ?, NOW())";
                    $stmt = $conn->prepare($transaction_query);
                    $stmt->bind_param("id", $userId, $total_refund);
                    $stmt->execute();
                    $stmt->close();
                    
                    // Commit transaction
                    $conn->commit();
                    $_SESSION['message'] = "Withdrawal rejected and funds returned to user account!";
                } catch (Exception $e) {
                    // Rollback in case of error
                    $conn->rollback();
                    $_SESSION['message'] = "Error rejecting withdrawal: " . $e->getMessage();
                }
            } else {
                $_SESSION['message'] = "Failed to reject withdrawal or it was already processed!";
            }
        } elseif ($action === 'complete') {
            // Mark a processing withdrawal as completed
            $complete_withdrawal_query = "UPDATE withdrawals SET status = 'completed' WHERE id = ? AND status = 'processing'";
            $stmt = $conn->prepare($complete_withdrawal_query);
            $stmt->bind_param("i", $withdrawal_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $_SESSION['message'] = "Withdrawal marked as completed!";
            } else {
                $_SESSION['message'] = "Failed to complete withdrawal or it was not in processing state!";
            }
            $stmt->close();
        }
    }

    header("Location: agent_dashboard.php");
    exit;
}

// Fetch Pending Recharges
$pending_recharges_query = "SELECT r.id, r.amount, r.recharge_time, u.phone_number 
                            FROM recharges r 
                            JOIN users u ON r.client_id = u.id 
                            WHERE r.agent_id = ? AND r.status = 'pending'";
$stmt = $conn->prepare($pending_recharges_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($recharge_id, $amount, $request_time, $client_phone_number);
$pending_recharges = [];
while ($stmt->fetch()) {
    $pending_recharges[] = [
        'id' => $recharge_id,
        'amount' => $amount,
        'request_time' => $request_time,
        'client_phone_number' => $client_phone_number
    ];
}
$stmt->close();

// Fetch Pending Withdrawals
$pending_withdrawals_query = "
    SELECT w.id, w.user_id, u.first_name, u.last_name, u.phone_number, 
           w.amount, w.fee, w.wallet_address, w.created_at, m.name as method_name
    FROM withdrawals w 
    JOIN users u ON w.user_id = u.id 
    JOIN withdrawal_methods m ON w.method_id = m.id
    WHERE w.status = 'pending'
    ORDER BY w.created_at ASC
";
$pending_withdrawals_result = $conn->query($pending_withdrawals_query);
$pending_withdrawals = [];
if ($pending_withdrawals_result) {
    while ($row = $pending_withdrawals_result->fetch_assoc()) {
        $pending_withdrawals[] = $row;
    }
}

// Fetch Processing Withdrawals
$processing_withdrawals_query = "
    SELECT w.id, w.user_id, u.first_name, u.last_name, u.phone_number, 
           w.amount, w.fee, w.wallet_address, w.created_at, w.processed_at, m.name as method_name
    FROM withdrawals w 
    JOIN users u ON w.user_id = u.id 
    JOIN withdrawal_methods m ON w.method_id = m.id
    WHERE w.status = 'processing'
    ORDER BY w.processed_at ASC
";
$processing_withdrawals_result = $conn->query($processing_withdrawals_query);
$processing_withdrawals = [];
if ($processing_withdrawals_result) {
    while ($row = $processing_withdrawals_result->fetch_assoc()) {
        $processing_withdrawals[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">

        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-user-shield me-2"></i>Agent Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="referrals.php">
                            <i class="fas fa-users me-1"></i>View Referrals
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog me-1"></i>Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php">
                            <i class="fas fa-key me-1"></i>Change Password
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <?php if (!empty($message)): ?>
        <div class="alert alert-info">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <h1>Agent Dashboard</h1>
        
        <!-- Pending Recharges Section -->
        <section class="mt-4">
            <h2>Pending Recharges</h2>
            <?php if (empty($pending_recharges)): ?>
                <p>No pending recharges.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Client Phone</th>
                                <th>Amount</th>
                                <th>Request Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_recharges as $recharge): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($recharge['client_phone_number']); ?></td>
                                <td><?php echo htmlspecialchars($recharge['amount']); ?></td>
                                <td><?php echo htmlspecialchars($recharge['request_time']); ?></td>
                                <td>
                                    <form method="post" action="" class="d-inline">
                                        <input type="hidden" name="recharge_id" value="<?php echo $recharge['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <form method="post" action="" class="d-inline">
                                        <input type="hidden" name="recharge_id" value="<?php echo $recharge['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <!-- Pending Withdrawals Section -->
        <section class="mt-5">
            <h2>Pending Withdrawals</h2>
            <?php if (empty($pending_withdrawals)): ?>
                <p>No pending withdrawals.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Phone</th>
                                <th>Method</th>
                                <th>Wallet/Account</th>
                                <th>Amount</th>
                                <th>Fee</th>
                                <th>Total</th>
                                <th>Request Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_withdrawals as $withdrawal): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($withdrawal['first_name'] . ' ' . $withdrawal['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['phone_number']); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['method_name']); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['wallet_address']); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['amount']); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['fee']); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['amount'] + $withdrawal['fee']); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['created_at']); ?></td>
                                <td>
                                    <form method="post" action="" class="d-inline">
                                        <input type="hidden" name="withdrawal_id" value="<?php echo $withdrawal['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                    </form>
                                    <form method="post" action="" class="d-inline">
                                        <input type="hidden" name="withdrawal_id" value="<?php echo $withdrawal['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <!-- Processing Withdrawals Section -->
        <section class="mt-5">
            <h2>Processing Withdrawals</h2>
            <?php if (empty($processing_withdrawals)): ?>
                <p>No withdrawals currently processing.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Phone</th>
                                <th>Method</th>
                                <th>Wallet/Account</th>
                                <th>Amount</th>
                                <th>Request Time</th>
                                <th>Processing Since</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($processing_withdrawals as $withdrawal): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($withdrawal['first_name'] . ' ' . $withdrawal['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['phone_number']); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['method_name']); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['wallet_address']); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['amount']); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($withdrawal['processed_at']); ?></td>
                                <td>
                                    <form method="post" action="" class="d-inline">
                                        <input type="hidden" name="withdrawal_id" value="<?php echo $withdrawal['id']; ?>">
                                        <input type="hidden" name="action" value="complete">
                                        <button type="submit" class="btn btn-primary btn-sm">Mark Completed</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>