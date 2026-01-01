<?php
session_start();
if ($_SESSION['role'] !== 'agent') {
    header("Location: ../login.php");
    exit;
}

// Database connection
require_once('../../includes/db.php');

// Get the agent's ID
$agent_id = $_SESSION['user_id'];

// Fetch referrals associated with the agent
$referral_query = "SELECT id, phone_number, balance FROM users WHERE referral_code = (SELECT referral_code FROM users WHERE id = '$agent_id')";
$referral_result = mysqli_query($conn, $referral_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Referrals - Harbor Investment</title>
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
            --nav-bg: #1a2a3a;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        
        .balance-positive {
            color: var(--success-color);
            font-weight: 600;
        }
        
        .button-group {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .no-referrals {
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
        }
        
        @media (max-width: 768px) {
            .button-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-users me-2"></i>My Referrals</h2>
        
        <div class="table-responsive">
            <?php if (mysqli_num_rows($referral_result) > 0): ?>
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Phone Number</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($referral = mysqli_fetch_assoc($referral_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($referral['id']); ?></td>
                                <td><?php echo htmlspecialchars($referral['phone_number']); ?></td>
                                <td class="balance-positive"><?php echo number_format($referral['balance'], 2); ?> RWF</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-referrals">
                    <i class="fas fa-user-plus fa-2x mb-3" style="color: var(--primary-color);"></i>
                    <p>You don't have any referrals yet.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="button-group">
            <a href="../agent_dashboard.php" class="btn btn-success">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <a href="../../actions/logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>