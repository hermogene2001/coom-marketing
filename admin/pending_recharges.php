<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once('../includes/db.php');
include('../includes/menu.php');

// Fetch pending recharges with phone number from the users table
$pending_recharges_query = "
    SELECT recharges.id, recharges.amount, recharges.recharge_time, users.phone_number 
    FROM recharges 
    JOIN users ON recharges.client_id = users.id 
    WHERE recharges.status = 'pending'";
$pending_recharges_result = mysqli_query($conn, $pending_recharges_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Recharges - Coom Marketing</title>
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
            --success-color: #10b981;
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
            color: var(--success-color);
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
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center mb-4">
            <i class="fas fa-clock me-2"></i>Pending Recharges
        </h2>

        <div class="table-responsive">
            <?php if (mysqli_num_rows($pending_recharges_result) > 0) { ?>
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>User Phone</th>
                            <th>Amount</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($recharge = mysqli_fetch_assoc($pending_recharges_result)) { ?>
                            <tr>
                                <td><?= $recharge['id']; ?></td>
                                <td><?= $recharge['phone_number']; ?></td>
                                <td class="amount-cell">+<?= number_format($recharge['amount'], 2); ?> USD</td>
                                <td><?= date('M j, Y H:i', strtotime($recharge['recharge_time'])); ?></td>
                                <td><span class="badge badge-warning status-badge">Pending</span></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class="no-pending">
                    <i class="fas fa-check-circle fa-2x mb-3" style="color: var(--success-color);"></i>
                    <p>No pending recharges at the moment.</p>
                </div>
            <?php } ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include('modals.php'); // Import modals ?>
</body>
</html>