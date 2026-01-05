<?php
/**
 * Binance Database Setup for COOM-MARKETING
 * Creates necessary tables for Binance integration
 */

include_once 'db_connection.php';

// SQL to create binance_deposit_addresses table
$create_deposit_addresses_table = "
CREATE TABLE IF NOT EXISTS binance_deposit_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    coin VARCHAR(10) NOT NULL,
    address VARCHAR(255) NOT NULL,
    network VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_coin (user_id, coin),
    INDEX idx_address (address)
)";

// SQL to update recharges table to include transaction_id
$alter_recharges_table = "
ALTER TABLE recharges 
ADD COLUMN IF NOT EXISTS transaction_id VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS notes TEXT DEFAULT NULL
";

// SQL to update withdrawals table to include transaction_id
$alter_withdrawals_table = "
ALTER TABLE withdrawals 
ADD COLUMN IF NOT EXISTS transaction_id VARCHAR(255) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS notes TEXT DEFAULT NULL
";

try {
    // Create binance_deposit_addresses table
    if ($conn->query($create_deposit_addresses_table) === TRUE) {
        echo "Table binance_deposit_addresses created successfully or already exists.\n";
    } else {
        echo "Error creating binance_deposit_addresses table: " . $conn->error . "\n";
    }
    
    // Alter recharges table
    if ($conn->query($alter_recharges_table) === TRUE) {
        echo "Table recharges altered successfully or already has the columns.\n";
    } else {
        echo "Error altering recharges table: " . $conn->error . "\n";
    }
    
    // Alter withdrawals table
    if ($conn->query($alter_withdrawals_table) === TRUE) {
        echo "Table withdrawals altered successfully or already has the columns.\n";
    } else {
        echo "Error altering withdrawals table: " . $conn->error . "\n";
    }
    
    echo "Binance database setup completed.\n";
} catch (Exception $e) {
    echo "Error during database setup: " . $e->getMessage() . "\n";
}
?>