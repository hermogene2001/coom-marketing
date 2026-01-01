<?php
session_start();
include '../includes/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Get basic account transactions (deposits and bonuses)
$basic_query = "SELECT * FROM transactions 
               WHERE user_id = ? AND (type = 'deposit' OR type = 'bonus')
               ORDER BY created_at DESC LIMIT 10";
$basic_stmt = $conn->prepare($basic_query);
$basic_stmt->bind_param("i", $user_id);
$basic_stmt->execute();
$basic_result = $basic_stmt->get_result();

// Get withdrawal transactions
$withdrawal_query = "SELECT * FROM transactions 
                    WHERE user_id = ? AND type = 'withdrawal'
                    ORDER BY created_at DESC LIMIT 10";
$withdrawal_stmt = $conn->prepare($withdrawal_query);
$withdrawal_stmt->bind_param("i", $user_id);
$withdrawal_stmt->execute();
$withdrawal_result = $withdrawal_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Details</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #1e2430;
            color: white;
            max-width: 600px;
            margin: 0 auto;
            padding: 15px;
        }
        
        .tabs {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid #383f4e;
        }
        
        .tab {
            padding: 15px 0;
            margin-right: 30px;
            font-size: 15px;
            position: relative;
            cursor: pointer;
        }
        
        .tab.active {
            color: #f3b71b;
            font-weight: bold;
        }
        
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #f3b71b;
        }
        
        .tab.inactive {
            color: #7a8599;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .transaction-card {
            background-color: #262d3d;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .transaction-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
        }
        
        .transaction-info {
            flex: 1;
        }
        
        .transaction-label {
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .transaction-type {
            color: #7a8599;
            font-size: 12px;
            text-transform: capitalize;
        }
        
        .transaction-amount {
            font-weight: bold;
            text-align: right;
        }
        
        .amount-positive {
            color: #4CAF50;
        }
        
        .amount-negative {
            color: #f44336;
        }
        
        .transaction-date {
            color: #7a8599;
            font-size: 12px;
        }
        
        .transaction-status {
            font-size: 12px;
            padding: 2px 8px;
            border-radius: 4px;
            text-transform: capitalize;
        }
        
        .status-pending {
            background-color: #FFC107;
            color: #1e2430;
        }
        
        .status-completed {
            background-color: #4CAF50;
            color: white;
        }
        
        .status-failed {
            background-color: #f44336;
            color: white;
        }
        
        .no-data {
            text-align: center;
            color: #7a8599;
            padding: 30px 0;
            font-size: 14px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #1e2430;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-size: 18px;
            font-weight: bold;
        }
        
        .logo-icon {
            width: 24px;
            height: 24px;
            background-color: #f3b71b;
            border-radius: 4px;
            margin-right: 8px;
        }
        
        .back-btn {
            color: white;
            text-decoration: none;
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="myaccount.php" class="back-btn">‹</a>
        <div class="logo">
            <div class="logo-icon"></div>
            <div>Financial records</div>
        </div>
        <div style="width: 24px;"></div> <!-- Spacer for balance -->
    </div>
    <div class="tabs">
        <div class="tab active" data-tab="basic">Basic account</div>
        <div class="tab inactive" data-tab="withdrawal">Withdrawal account</div>
    </div>
    
    <div id="basic-content" class="tab-content active">
        <?php if ($basic_result->num_rows > 0): ?>
            <?php while ($transaction = $basic_result->fetch_assoc()): ?>
                <div class="transaction-card">
                    <div class="transaction-row">
                        <div class="transaction-info">
                            <div class="transaction-label"><?php echo htmlspecialchars($transaction['description']); ?></div>
                            <div class="transaction-type"><?php echo htmlspecialchars($transaction['type']); ?></div>
                            <div class="transaction-status status-<?php echo htmlspecialchars($transaction['status']); ?>">
                                <?php echo htmlspecialchars($transaction['status']); ?>
                            </div>
                        </div>
                        <div class="transaction-amount amount-positive">
                            +<?php echo number_format($transaction['amount'], 2); ?> RWF
                        </div>
                    </div>
                    <div class="transaction-date">
                        <?php echo date('d/m/Y H:i:s', strtotime($transaction['created_at'])); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-data">No transactions available</div>
        <?php endif; ?>
    </div>
    
    <div id="withdrawal-content" class="tab-content">
        <?php if ($withdrawal_result->num_rows > 0): ?>
            <?php while ($transaction = $withdrawal_result->fetch_assoc()): ?>
                <div class="transaction-card">
                    <div class="transaction-row">
                        <div class="transaction-info">
                            <div class="transaction-label">Withdrawal request</div>
                            <div class="transaction-type">withdrawal</div>
                            <div class="transaction-status status-<?php echo htmlspecialchars($transaction['status']); ?>">
                                <?php echo htmlspecialchars($transaction['status']); ?>
                            </div>
                        </div>
                        <div class="transaction-amount amount-negative">
                            -<?php echo number_format($transaction['amount'], 2); ?> RWF
                        </div>
                    </div>
                    <div class="transaction-date">
                        <?php echo date('d/m/Y H:i:s', strtotime($transaction['created_at'])); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-data">No withdrawal transactions available</div>
        <?php endif; ?>
    </div>

    <script>
        // Get all tab elements
        const tabs = document.querySelectorAll('.tab');
        
        // Add click event listener to each tab
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs
                tabs.forEach(t => {
                    t.classList.remove('active');
                    t.classList.add('inactive');
                });
                
                // Add active class to clicked tab
                tab.classList.add('active');
                tab.classList.remove('inactive');
                
                // Hide all tab content
                const tabContents = document.querySelectorAll('.tab-content');
                tabContents.forEach(content => {
                    content.classList.remove('active');
                });
                
                // Show the selected tab content
                const tabName = tab.getAttribute('data-tab');
                document.getElementById(`${tabName}-content`).classList.add('active');
            });
        });
    </script>
</body>
</html>