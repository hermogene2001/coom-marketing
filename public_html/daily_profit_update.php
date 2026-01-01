<?php
// Database connection
include 'includes/db.php';

// Set timezone to Kigali
ini_set('date.timezone', 'Africa/Kigali');
$now = date('Y-m-d H:i:s');

// Create a lock file to prevent multiple script runs
$lockFile = '/tmp/earnings_script.lock';
if (file_exists($lockFile)) {
    exit('Script is already running.');
}
file_put_contents($lockFile, getmypid());
register_shutdown_function(function () use ($lockFile) {
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
});

/**
 * Process daily earnings for active purchases
 */
function processDailyEarnings($conn, $now) {
    mysqli_begin_transaction($conn);
    
    try {
        // Query active purchases eligible for daily earnings
        $query = "SELECT 
                    purchases.id AS purchase_id, 
                    purchases.client_id, 
                    products.daily_earning, 
                    purchases.last_earned, 
                    purchases.end_datetime
                  FROM purchases
                  JOIN products ON purchases.product_id = products.id
                  WHERE purchases.status = 'active' 
                  AND ? >= DATE_ADD(purchases.last_earned, INTERVAL 1 DAY)
                  AND ? <= purchases.end_datetime
                  FOR UPDATE";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ss', $now, $now);
        $stmt->execute();
        $result = $stmt->get_result();

        $processedCount = 0;
        
        while ($purchase = $result->fetch_assoc()) {
            $purchaseId = $purchase['purchase_id'];
            $userId = $purchase['client_id'];
            $dailyEarning = $purchase['daily_earning'];
            $endDatetime = $purchase['end_datetime'];

            // Check for existing transaction to prevent duplicates
            $checkQuery = "SELECT COUNT(*) FROM transactions 
                          WHERE client_id = ? 
                          AND transaction_type = 'daily_earning' 
                          AND DATE(date) = DATE(?)";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param('is', $userId, $now);
            $checkStmt->execute();
            $checkStmt->bind_result($count);
            $checkStmt->fetch();
            $checkStmt->close();

            if ($count > 0) {
                continue;
            }

            // Update user balance
            $balanceQuery = "UPDATE users SET balance = balance + ? WHERE id = ?";
            $balanceStmt = $conn->prepare($balanceQuery);
            $balanceStmt->bind_param('di', $dailyEarning, $userId);
            $balanceStmt->execute();

            // Log transaction
            $transactionQuery = "INSERT INTO transactions 
                                (client_id, transaction_type, amount, date, reference_id)
                                VALUES (?, 'daily_earning', ?, ?, ?)";
            $transactionStmt = $conn->prepare($transactionQuery);
            $transactionStmt->bind_param('idss', $userId, $dailyEarning, $now, $purchaseId);
            $transactionStmt->execute();

            // Update last earning datetime
            $purchaseUpdateQuery = "UPDATE purchases SET last_earned = ? WHERE id = ?";
            $purchaseUpdateStmt = $conn->prepare($purchaseUpdateQuery);
            $purchaseUpdateStmt->bind_param('si', $now, $purchaseId);
            $purchaseUpdateStmt->execute();
            
            $processedCount++;
        }
        
        mysqli_commit($conn);
        return $processedCount;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Daily earnings processing failed: " . $e->getMessage());
        return false;
    }
}

/**
 * Process refund for expired purchases
 */
function processExpiredPurchases($conn, $now) {
    mysqli_begin_transaction($conn);
    
    try {
        // Get expired active purchases with their investments
        $query = "SELECT 
                    p.id AS purchase_id, 
                    p.client_id,
                    i.id AS investment_id,
                    i.amount AS investment_amount,
                    p.product_id,
                    p.start_datetime,
                    p.end_datetime
                  FROM purchases p
                  JOIN investments i ON p.client_id = i.user_id AND i.purchase_id = p.id
                  WHERE p.status = 'active' 
                  AND p.end_datetime <= ?
                  AND i.status = 'active'";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $now);
        $stmt->execute();
        $result = $stmt->get_result();

        $processedCount = 0;
        
        while ($expired = $result->fetch_assoc()) {
            $purchaseId = $expired['purchase_id'];
            $userId = $expired['client_id'];
            $investmentId = $expired['investment_id'];
            $investmentAmount = $expired['investment_amount'];

            // Mark purchase as completed
            $completePurchaseQuery = "UPDATE purchases SET status = 'completed' WHERE id = ?";
            $completePurchaseStmt = $conn->prepare($completePurchaseQuery);
            $completePurchaseStmt->bind_param('i', $purchaseId);
            $completePurchaseStmt->execute();

            // Refund capital
            $refundQuery = "UPDATE users SET balance = balance + ? WHERE id = ?";
            $refundStmt = $conn->prepare($refundQuery);
            $refundStmt->bind_param('di', $investmentAmount, $userId);
            $refundStmt->execute();

            // Log capital return transaction
            $capitalTransactionQuery = "INSERT INTO transactions 
                                      (client_id, transaction_type, amount, date, reference_id)
                                      VALUES (?, 'capital_return', ?, ?, ?)";
            $capitalTransactionStmt = $conn->prepare($capitalTransactionQuery);
            $capitalTransactionStmt->bind_param('idss', $userId, $investmentAmount, $now, $purchaseId);
            $capitalTransactionStmt->execute();

            // Mark investment as completed
            $completeInvestmentQuery = "UPDATE investments SET status = 'completed' WHERE id = ?";
            $completeInvestmentStmt = $conn->prepare($completeInvestmentQuery);
            $completeInvestmentStmt->bind_param('i', $investmentId);
            $completeInvestmentStmt->execute();
            
            $processedCount++;
        }
        
        mysqli_commit($conn);
        return $processedCount;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        error_log("Expired purchases processing failed: " . $e->getMessage());
        return false;
    }
}

// Main execution
try {
    // Process daily earnings
    $earningsResult = processDailyEarnings($conn, $now);
    if ($earningsResult === false) {
        throw new Exception("Failed to process daily earnings");
    }
    
    // Process expired purchases
    $refundsResult = processExpiredPurchases($conn, $now);
    if ($refundsResult === false) {
        throw new Exception("Failed to process expired purchases");
    }
    
    // Log successful run
    error_log("Earnings script completed at $now. Processed: $earningsResult daily earnings, $refundsResult refunds.");
    
    echo json_encode([
        'status' => 'success',
        'daily_earnings_processed' => $earningsResult,
        'refunds_processed' => $refundsResult,
        'timestamp' => $now
    ]);
    
} catch (Exception $e) {
    error_log("Earnings script error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'timestamp' => $now
    ]);
    http_response_code(500);
} finally {
    // Close connection
    if (isset($conn)) {
        mysqli_close($conn);
    }
    
    // Clean up lock file
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
}
?>