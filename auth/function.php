<?php

function applyReferralBonus($userId, $rechargeAmount, $conn) {
    // Fetch the referrer (Level 1)
    $sql = "SELECT invitation_code FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($referrerId);
    $stmt->fetch();
    $stmt->close();

    if ($referrerId) {
        // Level 1 bonus (6%)
        $level1Bonus = $rechargeAmount * 0.06;

        // Add the bonus to referrer's referral_bonus and balance
        $sql = "UPDATE users SET referral_bonus = referral_bonus + ?, balance = balance + ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ddi", $level1Bonus, $level1Bonus, $referrerId);
        if (!$stmt->execute()) {
            die('Error applying Level 1 bonus: ' . $stmt->error);
        }
        $stmt->close();

        // Fetch the referrer of the referrer (Level 2)
        $sql = "SELECT invitation_code FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $referrerId);
        $stmt->execute();
        $stmt->bind_result($level2ReferrerId);
        $stmt->fetch();
        $stmt->close();

        if ($level2ReferrerId) {
            // Level 2 bonus (3%)
            $level2Bonus = $rechargeAmount * 0.03;

            // Add the bonus to Level 2 referrer's referral_bonus and balance
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

// Example usage:
// applyReferralBonus($userId, $rechargeAmount, $conn);

?>
