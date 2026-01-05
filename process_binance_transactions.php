<?php
/*
 * Binance Transaction Processing Script
 * This script should be run as a cron job to process Binance deposits and withdrawals
 */

// Set the timezone
date_default_timezone_set('UTC');

// Include necessary files
require_once 'includes/db_connection.php';
require_once 'includes/binance_api.php';

// Create the integration object
$binance_integration = new COOMBinanceIntegration($conn);

// Process deposits
echo "Processing Binance deposits...\n";
$binance_integration->processDeposits();
echo "Deposit processing completed.\n";

// Process withdrawals
echo "Processing Binance withdrawals...\n";
$binance_integration->processWithdrawals();
echo "Withdrawal processing completed.\n";

echo "Binance transaction processing completed at " . date('Y-m-d H:i:s') . "\n";
?>