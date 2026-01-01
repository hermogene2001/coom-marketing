<?php
// reinvest_or_withdraw.php

include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['user_id'];
    $investmentId = $_POST['investment_id'];
    $action = $_POST['action'];

    if ($action === 'reinvest') {
        $sql = "SELECT investment_amount, daily_profit FROM investments WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $investmentId, $userId);
        $stmt->execute();
        $stmt->bind_result($investmentAmount, $dailyProfit);
        $stmt->fetch();

        addInvestment($userId, $investmentAmount, $dailyProfit);
    } elseif ($action === 'withdraw') {
        $sql = "UPDATE users SET balance = balance + (SELECT investment_amount FROM investments WHERE id = ?) WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $investmentId, $userId);
        $stmt->execute();

        $conn->query("UPDATE investments SET status = 'completed' WHERE id = $investmentId");
    }

    $stmt->close();
    header("Location: investment_summary.php");
}

?>