<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once('../includes/db.php');
include('../includes/menu.php');

// Fetch pending withdrawals with phone number from the users table
$pending_withdrawals_query = "
    SELECT withdrawals.id, withdrawals.amount, withdrawals.date, users.phone_number 
    FROM withdrawals 
    JOIN users ON withdrawals.client_id = users.id 
    WHERE withdrawals.status = 'pending'";
$pending_withdrawals_result = mysqli_query($conn, $pending_withdrawals_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Withdrawals - Coom Marketing</title>
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
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
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
            font-weight: 600;
        }
        
        .table {
            color: var(--text-color);
            background-color: var(--card-bg);
            border-color: var(--card-border);
        }
        
        .table th {
            background-color: #1a2a3a;
            border-color: var(--card-border);
            font-weight: 500;
        }
        
        .table td {
            border-color: var(--card-border);
            vertical-align: middle;
        }
        
        .amount-cell {
            font-weight: 600;
            color: var(--danger-color);
        }
        
        .no-pending {
            color: var(--text-muted);
            font-size: 1.1rem;
            padding: 2rem;
            text-align: center;
            background-color: var(--card-bg);
            border-radius: 8px;
            border: 1px dashed var(--card-border);
        }
        
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .status-badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            border-radius: 0.375rem;
        }
        
        .badge-warning {
            background-color: var(--warning-color);
            color: #000;
        }
        
        .action-btns {
            white-space: nowrap;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.375rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">
            <i class="fas fa-money-bill-wave me-2"></i>Pending Withdrawals
        </h2>

        <div class="table-responsive">
            <?php if (mysqli_num_rows($pending_withdrawals_result) > 0) { ?>
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>User Phone</th>
                            <th>Amount</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($withdrawal = mysqli_fetch_assoc($pending_withdrawals_result)) { ?>
                            <tr>
                                <td><?= $withdrawal['id']; ?></td>
                                <td><?= $withdrawal['phone_number']; ?></td>
                                <td class="amount-cell">-<?= number_format($withdrawal['amount'], 2); ?> USD</td>
                                <td><?= date('M j, Y H:i', strtotime($withdrawal['date'])); ?></td>
                                <td><span class="badge badge-warning status-badge">Pending</span></td>
                                <td class="action-btns">
                                    <a href="../actions/approve_withdrawal.php?id=<?= $withdrawal['id']; ?>" class="btn btn-success btn-sm" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a href="../actions/reject_withdrawal.php?id=<?= $withdrawal['id']; ?>" class="btn btn-danger btn-sm" title="Reject" onclick="return confirm('Are you sure you want to reject this withdrawal?')">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class="no-pending">
                    <i class="fas fa-check-circle fa-2x mb-3" style="color: var(--primary-color);"></i>
                    <p>No pending withdrawals at the moment.</p>
                </div>
            <?php } ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include('modals.php'); // Import modals ?>
</body>
</html>