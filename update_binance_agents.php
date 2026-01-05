<?php
/**
 * Update agents table to add Binance address field
 */

include_once 'includes/db_connection.php';

// Add binance_address column to agents table
$alter_table_sql = "ALTER TABLE users ADD COLUMN binance_address VARCHAR(255) DEFAULT NULL AFTER payment_details";

if ($conn->query($alter_table_sql) === TRUE) {
    echo "Successfully added binance_address column to users table\n";
} else {
    echo "Error adding binance_address column: " . $conn->error . "\n";
}

// Also create a table to track recharge assignments to agents
$create_assignment_table = "
CREATE TABLE IF NOT EXISTS recharge_agent_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recharge_id INT NOT NULL,
    agent_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recharge_id) REFERENCES recharges(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($create_assignment_table) === TRUE) {
    echo "Successfully created recharge_agent_assignments table\n";
} else {
    echo "Error creating recharge_agent_assignments table: " . $conn->error . "\n";
}

echo "Database update completed.\n";
?>