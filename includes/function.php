<?php

function applyReferralBonus($userId, $rechargeAmount, $conn) {
    // Fetch the referrer (Level 1)
    $sql = "SELECT referrer_id FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($referrerId);
    $stmt->fetch();
    $stmt->close();

    if ($referrerId) {
        // Level 1 bonus (6%)
        $level1Bonus = $rechargeAmount * 0.06;

        // Update referral bonus and balance for Level 1 referrer
        $sql = "UPDATE users SET referral_bonus = referral_bonus + ?, balance = balance + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ddi", $level1Bonus, $level1Bonus, $referrerId);
        if (!$stmt->execute()) {
            die('Error applying Level 1 bonus: ' . $stmt->error);
        }
        $stmt->close();

        // Record Level 1 transaction
        recordTransaction($referrerId, $level1Bonus, 'referral_bonus', $conn);

        // Fetch the referrer of the referrer (Level 2)
        $sql = "SELECT referrer_id FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $referrerId);
        $stmt->execute();
        $stmt->bind_result($level2ReferrerId);
        $stmt->fetch();
        $stmt->close();

        if ($level2ReferrerId) {
            // Level 2 bonus (3%)
            $level2Bonus = $rechargeAmount * 0.03;

            // Update referral bonus and balance for Level 2 referrer
            $sql = "UPDATE users SET referral_bonus = referral_bonus + ?, balance = balance + ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ddi", $level2Bonus, $level2Bonus, $level2ReferrerId);
            if (!$stmt->execute()) {
                error_log('Error applying Level 2 bonus: ' . $stmt->error);
            }
            $stmt->close();

            // Record Level 2 transaction
            recordTransaction($level2ReferrerId, $level2Bonus, 'referral_bonus', $conn);
        }
    }
}

function recordTransaction($userId, $amount, $type, $conn) {
    // SQL query to insert a transaction record
    $sql = "INSERT INTO transactions (user_id, transaction_type, amount, transaction_date) 
            VALUES (?, ?, ?, NOW())";
    
    // Prepare the SQL statement
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Error preparing SQL statement: ' . $conn->error);
        return;
    }

    // Bind parameters to the SQL statement
    $stmt->bind_param("isd", $userId, $type, $amount);

    // Execute the SQL statement
    if (!$stmt->execute()) {
        error_log('Error recording transaction: ' . $stmt->error);
    }

    // Close the statement
    $stmt->close();
}

// Example usage:
// applyReferralBonus($userId, $rechargeAmount, $conn);

?>