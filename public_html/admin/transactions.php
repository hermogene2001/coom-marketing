<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
require_once('../includes/db.php');
include('../includes/menu.php');

// Default query (if no search term is provided)
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Modify the SQL query to include the search parameter
$transactions_query = "
    SELECT t.*, u.phone_number 
    FROM transactions t 
    JOIN users u ON t.client_id = u.id 
    WHERE u.phone_number LIKE ? OR t.transaction_type LIKE ?
    ORDER BY t.transaction_type, t.date DESC
";

// Prepare the statement to prevent SQL injection
$stmt = $conn->prepare($transactions_query);

// Bind the search parameter
$search = '%' . $search_query . '%';
$stmt->bind_param('ss', $search, $search);
$stmt->execute();
$transactions_result = $stmt->get_result();

$current_type = null; // Variable to track the current transaction type
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Harbor Investment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
            --card-border: #334155;
            --text-color: #e2e8f0;
            --text-muted: #94a3b8;
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --type-header-bg: #1a2a3a;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
        }
        
        .container {
            padding-top: 2rem;
        }
        
        h2 {
            color: var(--text-color);
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 0.5rem;
        }
        
        .table {
            color: var(--text-color);
            background-color: var(--card-bg);
            border-color: var(--card-border);
        }
        
        .table th {
            background-color: var(--type-header-bg);
            border-color: var(--card-border);
        }
        
        .table td {
            border-color: var(--card-border);
        }
        
        .table-primary {
            background-color: var(--type-header-bg);
        }
        
        .table-primary td {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .form-control {
            background-color: #2d3748;
            border-color: var(--card-border);
            color: var(--text-color);
        }
        
        .form-control:focus {
            background-color: #2d3748;
            color: var(--text-color);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        
        .input-group-text {
            background-color: var(--type-header-bg);
            border-color: var(--card-border);
            color: var(--text-color);
        }
        
        .amount-positive {
            color: #10b981; /* Green for positive amounts */
        }
        
        .amount-negative {
            color: #ef4444; /* Red for negative amounts */
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-exchange-alt me-2"></i>Transaction History</h2>

        <!-- Search Form with live search -->
        <div class="mb-4">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" name="search" id="searchInput" class="form-control" 
                       value="<?= htmlspecialchars($search_query); ?>" 
                       placeholder="Search by phone number or transaction type">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr class="table-dark">
                        <th>ID</th>
                        <th>User Phone</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody id="transactionsTable">
                    <?php while ($transaction = mysqli_fetch_assoc($transactions_result)) { 
                        if ($current_type !== $transaction['transaction_type']) {
                            // Display a header row for each new transaction type
                            $current_type = $transaction['transaction_type'];
                    ?>
                        <tr class="table-primary">
                            <td colspan="5">
                                <i class="fas fa-<?= $current_type === 'deposit' ? 'plus-circle' : ($current_type === 'withdrawal' ? 'minus-circle' : 'exchange-alt'); ?> me-2"></i>
                                <strong><?= ucfirst($current_type); ?> Transactions</strong>
                            </td>
                        </tr>
                    <?php } ?>
                        <tr>
                            <td><?= $transaction['id']; ?></td>
                            <td><?= $transaction['phone_number']; ?></td>
                            <td class="<?= $transaction['amount'] >= 0 ? 'amount-positive' : 'amount-negative'; ?>">
                                <?= ($transaction['amount'] >= 0 ? '+' : '') . $transaction['amount']; ?>
                            </td>
                            <td><?= ucfirst($transaction['transaction_type']); ?></td>
                            <td><?= date('M j, Y H:i', strtotime($transaction['date'])); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Event listener for the search input
        $('#searchInput').on('input', function() {
            var query = $(this).val();

            $.ajax({
                url: 'search_transactions.php',
                method: 'GET',
                data: { search: query },
                success: function(response) {
                    $('#transactionsTable').html(response);
                }
            });
        });
    });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>