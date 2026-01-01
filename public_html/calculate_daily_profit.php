<?php

// calculate_daily_profit.php

include 'includes/db.php';
include 'investment.php';

function calculateDailyProfit() {
    global $conn;

    $sql = "SELECT id, user_id, profit, end_date FROM investments WHERE status = 'active'";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $investmentId = $row['id'];
        $userId = $row['user_id'];
        $dailyProfit = $row['profit'];
        $endDate = $row['end_date'];
        
        $conn->query("UPDATE users SET balance = balance + $dailyProfit WHERE id = $userId");
        
        logTransaction($userId, 'daily_profit', $dailyProfit);

        if (strtotime($endDate) <= time()) {
            $conn->query("UPDATE investments SET status = 'completed' WHERE id = $investmentId");
            notifyUserCycleCompletion($userId);
        }
    }
}

function logTransaction($userId, $type, $amount) {
    global $conn;

    $sql = "INSERT INTO transactions (client_id, transaction_type, amount) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isd", $userId, $type, $amount);
    $stmt->execute();

   
}
?>