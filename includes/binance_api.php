<?php
/**
 * Binance API Integration for COOM-MARKETING
 * Handles cryptocurrency deposits and withdrawals via Binance API
 */

class BinanceAPI {
    private $api_key;
    private $secret_key;
    private $base_url = 'https://api.binance.com';
    
    public function __construct($api_key = null, $secret_key = null) {
        $this->api_key = $api_key ?: getenv('BINANCE_API_KEY');
        $this->secret_key = $secret_key ?: getenv('BINANCE_SECRET_KEY');
    }
    
    /**
     * Generate signature for API requests
     */
    private function generateSignature($params) {
        $query_string = http_build_query($params);
        return hash_hmac('sha256', $query_string, $this->secret_key);
    }
    
    /**
     * Get account information
     */
    public function getAccountInfo() {
        $endpoint = '/api/v3/account';
        $params = [
            'timestamp' => time() * 1000
        ];
        $params['signature'] = $this->generateSignature($params);
        
        return $this->signedRequest($endpoint, $params);
    }
    
    /**
     * Get deposit address for a specific coin
     */
    public function getDepositAddress($coin) {
        $endpoint = '/sapi/v1/capital/deposit/address';
        $params = [
            'coin' => $coin,
            'timestamp' => time() * 1000
        ];
        $params['signature'] = $this->generateSignature($params);
        
        return $this->signedRequest($endpoint, $params);
    }
    
    /**
     * Withdraw cryptocurrency to an address
     */
    public function withdrawCrypto($coin, $address, $amount, $network = null) {
        $endpoint = '/sapi/v1/capital/withdraw/apply';
        $params = [
            'coin' => $coin,
            'address' => $address,
            'amount' => $amount,
            'timestamp' => time() * 1000
        ];
        
        if ($network) {
            $params['network'] = $network;
        }
        
        $params['signature'] = $this->generateSignature($params);
        
        return $this->signedRequest($endpoint, $params, 'POST');
    }
    
    /**
     * Get deposit history
     */
    public function getDepositHistory($coin = null, $startTime = null, $endTime = null) {
        $endpoint = '/sapi/v1/capital/deposit/hisrec';
        $params = [
            'timestamp' => time() * 1000
        ];
        
        if ($coin) $params['coin'] = $coin;
        if ($startTime) $params['startTime'] = $startTime;
        if ($endTime) $params['endTime'] = $endTime;
        
        $params['signature'] = $this->generateSignature($params);
        
        return $this->signedRequest($endpoint, $params);
    }
    
    /**
     * Get withdrawal history
     */
    public function getWithdrawHistory($coin = null, $startTime = null, $endTime = null) {
        $endpoint = '/sapi/v1/capital/withdraw/history';
        $params = [
            'timestamp' => time() * 1000
        ];
        
        if ($coin) $params['coin'] = $coin;
        if ($startTime) $params['startTime'] = $startTime;
        if ($endTime) $params['endTime'] = $endTime;
        
        $params['signature'] = $this->generateSignature($params);
        
        return $this->signedRequest($endpoint, $params);
    }
    
    /**
     * Make a signed API request
     */
    private function signedRequest($endpoint, $params, $method = 'GET') {
        $url = $this->base_url . $endpoint;
        
        $headers = [
            'X-MBX-APIKEY: ' . $this->api_key
        ];
        
        $query_string = http_build_query($params);
        
        if ($method === 'GET') {
            $url .= '?' . $query_string;
            $curl_options = [
                CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => true
            ];
        } else {
            $curl_options = [
                CURLOPT_URL => $url . '?' . http_build_query(array_filter($params, function($k) { return $k !== 'signature'; }, ARRAY_FILTER_USE_KEY)),
                CURLOPT_HTTPHEADER => array_merge($headers, ['Content-Type: application/x-www-form-urlencoded']),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $query_string,
                CURLOPT_SSL_VERIFYPEER => true
            ];
        }
        
        $curl = curl_init();
        curl_setopt_array($curl, $curl_options);
        $result = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($http_code !== 200) {
            throw new Exception("Binance API Error: $result (HTTP Code: $http_code)");
        }
        
        return json_decode($result, true);
    }
    
    /**
     * Make a public API request (no authentication needed)
     */
    public function publicRequest($endpoint, $params = []) {
        $url = $this->base_url . $endpoint;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        $result = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($http_code !== 200) {
            throw new Exception("Binance API Error: $result (HTTP Code: $http_code)");
        }
        
        return json_decode($result, true);
    }
}

/**
 * COOM-MARKETING Binance Integration Class
 * Handles integration between the platform and Binance API
 */
class COOMBinanceIntegration {
    private $binance_api;
    private $db_connection;
    
    public function __construct($db_connection) {
        $this->db_connection = $db_connection;
        $this->binance_api = new BinanceAPI();
    }
    
    /**
     * Get deposit address for a user
     */
    public function getUserDepositAddress($user_id, $coin = 'USDT') {
        try {
            // Check if we already have an address for this user and coin
            $stmt = $this->db_connection->prepare("
                SELECT address, network FROM binance_deposit_addresses 
                WHERE user_id = ? AND coin = ? LIMIT 1
            ");
            $stmt->bind_param("is", $user_id, $coin);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            // If not, get a new address from Binance
            $response = $this->binance_api->getDepositAddress($coin);
            
            if (isset($response['address'])) {
                // Store the address in our database
                $stmt = $this->db_connection->prepare("
                    INSERT INTO binance_deposit_addresses (user_id, coin, address, network, created_at) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->bind_param("isss", $user_id, $coin, $response['address'], $response['tag'] ?? $response['network'] ?? 'default');
                $stmt->execute();
                
                return [
                    'address' => $response['address'],
                    'network' => $response['tag'] ?? $response['network'] ?? 'default'
                ];
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Binance API Error getting deposit address: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Process deposits by checking Binance for new transactions
     */
    public function processDeposits() {
        try {
            // Get all pending recharges with Binance payment method
            $stmt = $this->db_connection->prepare("
                SELECT r.id, r.user_id, r.amount, r.created_at, u.balance 
                FROM recharges r
                JOIN users u ON r.user_id = u.id
                WHERE r.payment_method = 'binance' AND r.status = 'pending'
            ");
            $stmt->execute();
            $recharges = $stmt->get_result();
            
            while ($recharge = $recharges->fetch_assoc()) {
                // Check Binance for deposits to this user's address
                $deposit_address = $this->getUserDepositAddress($recharge['user_id']);
                
                if ($deposit_address) {
                    // Check for deposits to this address
                    $startTime = strtotime($recharge['created_at']) * 1000; // Convert to milliseconds
                    $endTime = time() * 1000; // Current time in milliseconds
                    
                    $deposit_history = $this->binance_api->getDepositHistory(null, $startTime, $endTime);
                    
                    if (isset($deposit_history['depositList']) && is_array($deposit_history['depositList'])) {
                        foreach ($deposit_history['depositList'] as $deposit) {
                            if ($deposit['address'] === $deposit_address['address'] && 
                                $this->isAmountMatch($deposit['amount'], $recharge['amount'])) {
                                
                                // Update user balance
                                $new_balance = $recharge['balance'] + $recharge['amount'];
                                
                                $update_stmt = $this->db_connection->prepare("
                                    UPDATE users SET balance = ? WHERE id = ?
                                ");
                                $update_stmt->bind_param("di", $new_balance, $recharge['user_id']);
                                $update_stmt->execute();
                                
                                // Update recharge status
                                $update_recharge_stmt = $this->db_connection->prepare("
                                    UPDATE recharges SET status = 'completed', 
                                    transaction_id = ? WHERE id = ?
                                ");
                                $update_recharge_stmt->bind_param("si", $deposit['txId'], $recharge['id']);
                                $update_recharge_stmt->execute();
                                
                                // Add transaction record
                                $transaction_stmt = $this->db_connection->prepare("
                                    INSERT INTO transactions (user_id, type, amount, description, created_at) 
                                    VALUES (?, 'deposit', ?, 'Binance deposit', NOW())
                                ");
                                $transaction_stmt->bind_param("id", $recharge['user_id'], $recharge['amount']);
                                $transaction_stmt->execute();
                                
                                error_log("Binance deposit processed for user {$recharge['user_id']}, amount: {$recharge['amount']}");
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Error processing Binance deposits: " . $e->getMessage());
        }
    }
    
    /**
     * Process withdrawals to user's Binance account
     */
    public function processWithdrawals() {
        try {
            // Get all pending withdrawals with Binance payment method
            $stmt = $this->db_connection->prepare("
                SELECT w.id, w.user_id, w.amount, w.account_number, u.balance 
                FROM withdrawals w
                JOIN users u ON w.user_id = u.id
                WHERE w.payment_method = 'binance' AND w.status = 'pending'
            ");
            $stmt->execute();
            $withdrawals = $stmt->get_result();
            
            while ($withdrawal = $withdrawals->fetch_assoc()) {
                if ($withdrawal['balance'] >= $withdrawal['amount']) {
                    // For demo purposes, we'll use USDT as the default coin
                    // In a real implementation, you'd want to determine the coin from the address or user preference
                    $coin = $this->determineCoinFromAddress($withdrawal['account_number']);
                    
                    try {
                        // Attempt withdrawal via Binance API
                        $result = $this->binance_api->withdrawCrypto(
                            $coin,
                            $withdrawal['account_number'],
                            $withdrawal['amount']
                        );
                        
                        if (isset($result['id'])) {
                            // Update user balance (deduct withdrawal amount)
                            $new_balance = $withdrawal['balance'] - $withdrawal['amount'];
                            
                            $update_user_stmt = $this->db_connection->prepare("
                                UPDATE users SET balance = ? WHERE id = ?
                            ");
                            $update_user_stmt->bind_param("di", $new_balance, $withdrawal['user_id']);
                            $update_user_stmt->execute();
                            
                            // Update withdrawal status
                            $update_withdrawal_stmt = $this->db_connection->prepare("
                                UPDATE withdrawals SET status = 'completed', 
                                transaction_id = ? WHERE id = ?
                            ");
                            $update_withdrawal_stmt->bind_param("si", $result['id'], $withdrawal['id']);
                            $update_withdrawal_stmt->execute();
                            
                            // Add transaction record
                            $transaction_stmt = $this->db_connection->prepare("
                                INSERT INTO transactions (user_id, type, amount, description, created_at) 
                                VALUES (?, 'withdrawal', ?, 'Binance withdrawal', NOW())
                            ");
                            $transaction_stmt->bind_param("id", $withdrawal['user_id'], $withdrawal['amount']);
                            $transaction_stmt->execute();
                            
                            error_log("Binance withdrawal processed for user {$withdrawal['user_id']}, amount: {$withdrawal['amount']}");
                        }
                    } catch (Exception $e) {
                        // Update withdrawal status as failed
                        $update_withdrawal_stmt = $this->db_connection->prepare("
                            UPDATE withdrawals SET status = 'failed', 
                            notes = ? WHERE id = ?
                        ");
                        $error_msg = $e->getMessage();
                        $update_withdrawal_stmt->bind_param("si", $error_msg, $withdrawal['id']);
                        $update_withdrawal_stmt->execute();
                        
                        error_log("Binance withdrawal failed for user {$withdrawal['user_id']}: " . $e->getMessage());
                    }
                } else {
                    // Insufficient balance
                    $update_withdrawal_stmt = $this->db_connection->prepare("
                        UPDATE withdrawals SET status = 'failed', 
                        notes = 'Insufficient balance' WHERE id = ?
                    ");
                    $update_withdrawal_stmt->bind_param("i", $withdrawal['id']);
                    $update_withdrawal_stmt->execute();
                }
            }
        } catch (Exception $e) {
            error_log("Error processing Binance withdrawals: " . $e->getMessage());
        }
    }
    
    /**
     * Determine coin type from address (simplified version)
     */
    private function determineCoinFromAddress($address) {
        // This is a simplified version - in reality, you'd need more sophisticated address validation
        // For example, USDT TRC20 addresses start with T, ERC20 addresses start with 0x, etc.
        
        if (substr($address, 0, 1) === 'T' && strlen($address) === 34) {
            return 'USDT'; // TRC20
        } elseif (substr($address, 0, 2) === '0x' && strlen($address) === 42) {
            return 'USDT'; // ERC20
        } else {
            // Default to USDT for now
            return 'USDT';
        }
    }
    
    /**
     * Check if amounts match (with some tolerance for decimal differences)
     */
    private function isAmountMatch($deposit_amount, $expected_amount, $tolerance = 0.01) {
        return abs(floatval($deposit_amount) - floatval($expected_amount)) <= $tolerance;
    }
}

// Function to run Binance processing (typically called via cron job)
function processBinanceTransactions() {
    include_once 'db_connection.php';
    
    $integration = new COOMBinanceIntegration($conn);
    
    // Process deposits first
    $integration->processDeposits();
    
    // Then process withdrawals
    $integration->processWithdrawals();
}

// For testing purposes, you can call this directly
// processBinanceTransactions();
?>