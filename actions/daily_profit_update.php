<?php
include '../includes/db.php';

// Current date and time
$current_time = date('Y-m-d H:i:s');

// Fetch active investments that are still within the cycle period
$investments_query = "
    SELECT inv.id AS investment_id, inv.amount, inv.profit, inv.last_profit_update, inv.product_id, prod.daily_earning 
    FROM investments AS inv 
    JOIN products AS prod ON inv.product_id = prod.id 
    WHERE inv.status = 'active' AND inv.end_date >= ?
";
$stmt = $conn->prepare($investments_query);
$stmt->bind_param("s", $current_time);
$stmt->execute();
$result = $stmt->get_result();

while ($investment = $result->fetch_assoc()) {
    $investment_id = $investment['investment_id'];
    $current_profit = $investment['profit'];
    $last_profit_update = $investment['last_profit_update'];
    $daily_earning = $investment['daily_earning'];

    // Check if 24 hours have passed since the last profit update
    $time_diff = strtotime($current_time) - strtotime($last_profit_update);

    if ($time_diff >= 86400 || $last_profit_update === null) { // 86400 seconds = 24 hours
        // Update the profit field by adding the daily earning
        $new_profit = $current_profit + $daily_earning;
        $update_profit_query = "UPDATE investments SET profit = ?, last_profit_update = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_profit_query);
        $update_stmt->bind_param("dsi", $new_profit, $current_time, $investment_id);
        $update_stmt->execute();
    }
}

echo "Daily earnings updated based on product's daily earning rate.";
?>
