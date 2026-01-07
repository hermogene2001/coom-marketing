<?php
/**
 * Create withdrawal agent assignments table
 */

include_once 'includes/db_connection.php';

// Create a table to track withdrawal assignments to agents
$create_assignment_table = "
CREATE TABLE IF NOT EXISTS withdrawal_agent_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    withdrawal_id INT NOT NULL,
    agent_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (withdrawal_id) REFERENCES withdrawals(id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($create_assignment_table) === TRUE) {
    echo "Successfully created withdrawal_agent_assignments table\n";
} else {
    echo "Error creating withdrawal_agent_assignments table: " . $conn->error . "\n";
}

echo "Database update completed.\n";
?>