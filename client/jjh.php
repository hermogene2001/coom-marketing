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
                $transaction_query = "INSERT INTO transactions (client_id, transaction_type, amount, date) 
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
            $approve_withdrawal_query = "UPDATE withdrawals SET status = 'approved', date = NOW() WHERE id = ?";
            $stmt = $conn->prepare($approve_withdrawal_query);
            $stmt->bind_param("i", $withdrawal_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = "Withdrawal approved successfully!";
        } elseif ($action === 'reject') {
            $reject_withdrawal_query = "UPDATE withdrawals SET status = 'rejected', date = NOW() WHERE id = ?";
            $stmt = $conn->prepare($reject_withdrawal_query);
            $stmt->bind_param("i", $withdrawal_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = "Withdrawal rejected!";
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
$withdrawals_query = "
    SELECT w.id, w.amount, w.user_id, u.first_name, u.last_name, w.created_at, 
           ub.bank_name, ub.account_number, ub.account_holder 
    FROM withdrawals w 
    JOIN users u ON w.user_id  = u.id 
    LEFT JOIN user_banks ub ON u.id = ub.user_id 
    WHERE w.status = 'pending'
";
$withdrawals_result = $conn->query($withdrawals_query);
?>
