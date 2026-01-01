<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../includes/db.php';

// Retrieve user ID from session
$userId = $_SESSION['user_id'];

// Fetch user's balance from the database
$sql = "SELECT balance FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($balance);
$stmt->fetch();
$stmt->close();

// Fetch user's transaction history
$sql = "SELECT transaction_type, amount, date FROM transactions WHERE client_id = ? ORDER BY date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wallet</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.2/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #003f3c;
        }
        .balance-card {
            background-color: #007bff;
            border-radius: 15px;
            padding: 30px;
            color: white;
            text-align: center;
        }

        .balance-card h2 {
            font-size: 36px;
            font-weight: 600;
        }

        .balance-card p {
            font-size: 20px;
        }

        .transaction-table {
            margin-top: 20px;
        }

        .transaction-table th, .transaction-table td {
            padding: 15px;
            text-align: center;
        }

        /* Custom styles for mobile devices */
        @media (max-width: 576px) {
            .balance-card h2 {
                font-size: 24px;
            }
            .balance-card p {
                font-size: 18px;
            }
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="text-center">
        <!-- Wallet Balance -->
        <div class="balance-card mb-4">
            <h2>Current Balance</h2>
            <p>RWF <?php echo number_format($balance, 2); ?></p>
        </div>

        <!-- Transaction History Table -->
        <div class="transaction-table">
            <h4 class="text-light">Transaction History</h4>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($transactions) > 0): ?>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($transaction['transaction_type']); ?></td>
                                <td><?php echo number_format($transaction['amount'], 2); ?> RWF</td>
                                <td><?php echo date('d-m-Y', strtotime($transaction['date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3">No transactions found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="account.php" class="btn btn-success">Back To Settings</a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
