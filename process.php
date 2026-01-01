<?php
require_once 'includes/db_connection.php';
// require_once 'includes/functions.php'; // Assuming you have a functions file

ini_set('display_errors', 2);
ini_set('display_startup_errors', 2);
error_reporting(E_ALL);

// Set maximum execution time
set_time_limit(3600); // 1 hour max

class ProductEarningsProcessor {
    private $conn;
    private $currentDate;
    private $processedCount = 0;
    private $capitalReturns = 0;
    private $totalCredited = 0;
    
    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->currentDate = date('Y-m-d');
    }
    
    public function process() {
        try {
            $this->conn->begin_transaction();
            
            $this->log("Starting product earnings processing for {$this->currentDate}");
            
            // Get all active user products that should earn today
            $userProducts = $this->getActiveUserProducts();
            
            if (empty($userProducts)) {
                $this->log("No active user products found for processing.");
                return;
            }
            
            foreach ($userProducts as $userProduct) {
                $this->processUserProduct($userProduct);
            }
            
            $this->conn->commit();
            
            $this->log("Processing completed successfully.");
            $this->log("Summary:");
            $this->log("- Total processed: {$this->processedCount}");
            $this->log("- Capital returns: {$this->capitalReturns}");
            $this->log("- Total credited: {$this->totalCredited} RWF");
            
        } catch (Exception $e) {
            $this->conn->rollback();
            $this->log("ERROR: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    private function getActiveUserProducts() {
        $sql = "SELECT up.id, up.user_id, up.product_id, up.purchase_date, up.end_date,
                       p.name as product_name, p.daily_earning, p.price, p.cycle,
                       u.first_name, u.balance
                FROM user_products up
                JOIN products p ON up.product_id = p.id
                JOIN users u ON up.user_id = u.id
                WHERE up.status = 'active'
                AND up.end_date >= CURDATE()
                ORDER BY up.user_id, up.product_id";
        
        $result = $this->conn->query($sql);
        
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        
        return $products;
    }
    
    private function processUserProduct($userProduct) {
        $isLastDay = (date('Y-m-d', strtotime($userProduct['end_date']))) === $this->currentDate;
        $creditAmount = $userProduct['daily_earning'];
        $description = "Daily earning from {$userProduct['product_name']} (ID: {$userProduct['product_id']})";
        
        if ($isLastDay) {
            // Add capital return on last day
            $creditAmount += $userProduct['price'];
            $description = "Cycle completed - {$userProduct['product_name']}: Daily earning + Capital return";
            $this->capitalReturns++;
            
            // Mark product as expired
            $this->expireUserProduct($userProduct['id']);
        }
        
        // Credit user balance
        $this->creditUserBalance($userProduct['user_id'], $creditAmount);
        
        // Record transaction
        $this->recordTransaction(
            $userProduct['user_id'],
            $creditAmount,
            $description,
            $userProduct['product_id']
        );
        
        // Update user product stats
        $this->updateUserProductStats($userProduct['id'], $creditAmount);
        
        $this->processedCount++;
        $this->totalCredited += $creditAmount;
        
        $logMsg = "Processed user #{$userProduct['user_id']} ({$userProduct['first_name']}): ";
        $logMsg .= "Product #{$userProduct['product_id']} - {$creditAmount} RWF";
        $logMsg .= $isLastDay ? " (with capital return)" : "";
        $this->log($logMsg);
    }
    
    private function expireUserProduct($userProductId) {
        $sql = "UPDATE user_products SET status = 'expired' WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $userProductId);
        $stmt->execute();
    }
    
    private function creditUserBalance($userId, $amount) {
        $sql = "UPDATE users SET balance = balance + ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("di", $amount, $userId);
        $stmt->execute();
    }
    
    private function recordTransaction($userId, $amount, $description, $productId) {
        $sql = "INSERT INTO transactions 
                (user_id, type, amount, description, created_at) 
                VALUES (?, 'product_earning', ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ids", $userId, $amount, $description);
        $stmt->execute();
    }
    
    private function updateUserProductStats($userProductId, $amount) {
        $sql = "UPDATE user_products 
                SET total_earned = total_earned + ?, 
                    last_payout_date = NOW() 
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("di", $amount, $userProductId);
        $stmt->execute();
    }
    
    private function log($message, $level = 'INFO') {
        $logEntry = sprintf(
            "[%s] [%s] %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message
        );
        
        // Log to file
        file_put_contents('product_earnings.log', $logEntry, FILE_APPEND);
        
        // Also output to console if running manually
        if (php_sapi_name() === 'cli') {
            echo $logEntry;
        }
    }
}

// Execute the processor
try {
    $processor = new ProductEarningsProcessor($conn);
    $processor->process();
    
    // For cron job monitoring
    echo "Product earnings processing completed successfully.\n";
    
} catch (Exception $e) {
    // For cron job monitoring
    echo "Product earnings processing failed: " . $e->getMessage() . "\n";
    exit(1); // Non-zero exit code for error
}

exit(0); // Success
?>