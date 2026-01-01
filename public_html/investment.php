<?php
// investment.php

// Use `include_once` to prevent multiple inclusions
include_once 'includes/db.php';
include_once '../calculate_daily_profit.php';

function addInvestment($userId, $investmentAmount, $dailyProfit) {
    global $conn; // Use the connection from db.php

    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d', strtotime("+6 months"));
    
    $sql = "INSERT INTO investments (user_id, investment_amount, start_date, end_date, daily_profit, status) 
            VALUES (?, ?, ?, ?, ?, 'active')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idssd", $userId, $investmentAmount, $startDate, $endDate, $dailyProfit);
    $stmt->execute();
    
 
    
}

// Example usage

/**
 * Notify the user when their investment cycle is completed.
 *
 * @param int $userId The ID of the user whose cycle is completed.
 * @param string $message The message to send to the user.
 */
function notifyUserCycleCompletion($userId, $message) {
    global $conn;

    // Fetch user email from the database
    $user_query = "SELECT email FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($userEmail);

    if ($stmt->fetch()) {
        // Send an email notification
        $subject = "Investment Cycle Completed";
        $headers = "From: no-reply@yourdomain.com\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        $emailMessage = "
            <html>
                <body>
                    <h2>Investment Cycle Completed</h2>
                    <p>{$message}</p>
                </body>
            </html>
        ";

        if (mail($userEmail, $subject, $emailMessage, $headers)) {
            echo "Notification sent successfully to {$userEmail}";
        } else {
            echo "Failed to send notification to {$userEmail}";
        }
    } else {
        echo "User email not found.";
    }

    $stmt->close();
}

// Add investment

?>
