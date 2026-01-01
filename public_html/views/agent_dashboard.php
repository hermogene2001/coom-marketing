<?php
// Include necessary files and logic
include 'agent/files.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard - Harbor Investment</title>
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
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --nav-bg: #1a2a3a;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: var(--nav-bg) !important;
            border-bottom: 1px solid var(--card-border);
        }
        
        .navbar-brand {
            color: var(--primary-color) !important;
            font-weight: 600;
        }
        
        .nav-link {
            color: var(--text-color) !important;
            padding: 0.5rem 1rem;
            border-radius: 4px;
        }
        
        .nav-link:hover {
            background-color: rgba(59, 130, 246, 0.1);
        }
        
        .container {
            padding-top: 2rem;
        }
        
        h2 {
            color: var(--text-color);
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .table {
            color: var(--text-color);
            background-color: var(--card-bg);
            border-color: var(--card-border);
        }
        
        .table th {
            background-color: var(--nav-bg);
            border-color: var(--card-border);
            font-weight: 500;
        }
        
        .table td {
            border-color: var(--card-border);
            vertical-align: middle;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 6px;
            margin: 0.15rem;
        }
        
        .action-btns {
            white-space: nowrap;
        }
        
        .no-pending {
            color: var(--text-muted);
            padding: 1.5rem;
            text-align: center;
            background-color: var(--card-bg);
            border-radius: 8px;
            border: 1px dashed var(--card-border);
        }
        
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .navbar-nav {
                gap: 0.5rem;
                padding-top: 1rem;
            }
            
            .nav-link {
                padding: 0.5rem;
            }
            
            .btn-sm {
                padding: 0.3rem 0.6rem;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 1rem;
            }
            
            h2 {
                font-size: 1.4rem;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
    <script>
        window.onload = function() {
            <?php if (!empty($message)): ?>
                alert("<?php echo addslashes($message); ?>");
            <?php endif; ?>
        };
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-user-shield me-2"></i>Agent Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="agent/referrals.php">
                            <i class="fas fa-users me-1"></i>View Referrals
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="agent/settings.php">
                            <i class="fas fa-cog me-1"></i>Settings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="agent/change_password.php">
                            <i class="fas fa-key me-1"></i>Change Password
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../actions/logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2><i class="fas fa-clock me-2"></i>Pending Recharges</h2>
        
        <?php if (!empty($pending_recharges)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Client Phone</th>
                            <th>Amount (RWF)</th>
                            <th>Request Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_recharges as $recharge): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($recharge['client_phone_number']); ?></td>
                                <td class="text-success fw-bold">+<?php echo number_format($recharge['amount'], 2); ?></td>
                                <td><?php echo date('M j, Y H:i', strtotime($recharge['request_time'])); ?></td>
                                <td class="action-btns">
                                    <form method="POST" action="">
                                        <input type="hidden" name="recharge_id" value="<?php echo $recharge['id']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">
                                            <i class="fas fa-check me-1"></i>Approve
                                        </button>
                                        <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">
                                            <i class="fas fa-times me-1"></i>Reject
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-pending">
                <i class="fas fa-check-circle fa-2x mb-2" style="color: var(--success-color);"></i>
                <p>No pending recharges at the moment.</p>
            </div>
        <?php endif; ?>

        <h2><i class="fas fa-money-bill-wave me-2"></i>Pending Withdrawal Requests</h2>
        <div class="table-responsive">
            <?php if (!empty($withdrawals_result)): ?>
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Client Name</th>
                            <th>Amount (RWF)</th>
                            <th>Date Requested</th>
                            <th>Bank Details</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($withdrawals_result as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['fname'] . ' ' . $row['lname']); ?></td>
                            <td class="text-danger fw-bold">-<?php echo number_format($row['net_amount'], 2); ?></td>
                            <td><?php echo date('M j, Y H:i', strtotime($row['date'])); ?></td>
                            <td>
                                <small>
                                    <strong><?php echo htmlspecialchars($row['bank_name']); ?></strong><br>
                                    <?php echo htmlspecialchars($row['account_number']); ?><br>
                                    <?php echo htmlspecialchars($row['account_holder']); ?>
                                </small>
                            </td>
                            <td class="action-btns">
                                <form method="POST" action="">
                                    <input type="hidden" name="withdrawal_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">
                                        <i class="fas fa-check me-1"></i>Approve
                                    </button>
                                    <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">
                                        <i class="fas fa-times me-1"></i>Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-pending">
                    <i class="fas fa-check-circle fa-2x mb-2" style="color: var(--success-color);"></i>
                    <p>No pending withdrawals at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>