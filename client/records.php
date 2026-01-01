<?php
session_start();
include '../includes/db_connection.php';
include 'nav.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];

// Get all transactions for the user
$transactions_query = "SELECT type, amount, created_at, description FROM transactions WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($transactions_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COOM-MARKETING - Transaction Records</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-bg: #0d1117;
            --secondary-bg: #161b22;
            --card-bg: #1a2029;
            --accent-color: #23a559;
            --accent-color-light: #37c070;
            --text-color: #e6edf3;
            --text-secondary: #7d8590;
            --border-color: #303841;
            --positive: #23a559;
            --negative: #e34c26;
            --header-bg: #0d1117;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--primary-bg);
            color: var(--text-color);
            min-height: 100vh;
            padding-top: 80px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 15px;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: var(--accent-color-light);
        }
        
        .page-header p {
            color: var(--text-secondary);
            font-size: 16px;
        }
        
        .records-list {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid var(--border-color);
        }
        
        .record-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .record-item:last-child {
            border-bottom: none;
        }
        
        .record-info {
            flex: 1;
        }
        
        .record-type {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .record-description {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .record-date {
            color: var(--text-secondary);
            font-size: 14px;
        }
        
        .record-amount {
            font-weight: bold;
            font-size: 16px;
            align-self: center;
        }
        
        .deposit {
            color: var(--positive);
        }
        
        .withdrawal {
            color: var(--negative);
        }
        
        .investment {
            color: var(--accent-color-light);
        }
        
        .referral {
            color: #3498db;
        }
        
        .no-records {
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
        }
        
        .search-filter {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            align-items: center;
        }
        
        .search-box {
            flex: 1;
            max-width: 300px;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--secondary-bg);
            color: var(--text-color);
        }
        
        .filter-select {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--secondary-bg);
            color: var(--text-color);
            margin-left: 15px;
        }
        
        .total-records {
            background-color: var(--secondary-bg);
            padding: 10px 15px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: center;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-file-invoice-dollar"></i> Transaction Records</h1>
            <p>View all your transaction history</p>
        </div>
        
        <div class="search-filter">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Search transactions...">
            </div>
            <select class="filter-select" id="filterSelect">
                <option value="">All Types</option>
                <option value="deposit">Deposit</option>
                <option value="withdrawal">Withdrawal</option>
                <option value="investment">Investment</option>
                <option value="referral">Referral</option>
            </select>
        </div>
        
        <div class="records-list">
            <h3 style="margin-bottom: 20px; color: var(--accent-color-light);">All Transactions</h3>
            <?php if ($transactions_result->num_rows > 0): ?>
                <?php while ($transaction = $transactions_result->fetch_assoc()): ?>
                    <div class="record-item" data-type="<?php echo $transaction['type']; ?>">
                        <div class="record-info">
                            <div class="record-type"><?php echo ucfirst($transaction['type']); ?></div>
                            <div class="record-description"><?php echo htmlspecialchars($transaction['description'] ?? 'No description'); ?></div>
                            <div class="record-date"><?php echo date('M j, Y g:i A', strtotime($transaction['created_at'])); ?></div>
                        </div>
                        <div class="record-amount <?php echo $transaction['type']; ?>">
                            <?php if (in_array($transaction['type'], ['deposit', 'investment', 'referral_bonus'])): ?>
                                +RWF <?php echo number_format($transaction['amount'], 2); ?>
                            <?php else: ?>
                                -RWF <?php echo number_format($transaction['amount'], 2); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-records">
                    <i class="fas fa-file-alt" style="font-size: 48px; margin-bottom: 15px;"></i>
                    <h3>No transaction records</h3>
                    <p>You don't have any transaction history yet</p>
                </div>
            <?php endif; ?>
            
            <div class="total-records">
                Total Transactions: <?php echo $transactions_result->num_rows; ?>
            </div>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const records = document.querySelectorAll('.record-item');
            
            records.forEach(record => {
                const type = record.querySelector('.record-type').textContent.toLowerCase();
                const description = record.querySelector('.record-description').textContent.toLowerCase();
                
                if (type.includes(searchTerm) || description.includes(searchTerm)) {
                    record.style.display = '';
                } else {
                    record.style.display = 'none';
                }
            });
        });
        
        // Filter functionality
        document.getElementById('filterSelect').addEventListener('change', function() {
            const filterValue = this.value;
            const records = document.querySelectorAll('.record-item');
            
            records.forEach(record => {
                const recordType = record.getAttribute('data-type');
                
                if (filterValue === '' || recordType === filterValue) {
                    record.style.display = '';
                } else {
                    record.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>