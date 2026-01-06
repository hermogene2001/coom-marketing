<?php
session_start();

// Ensure the user is logged in as an agent and the ID is set
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'agent' || !isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Database connection
require_once('../includes/db.php');

// Fetch the agent's current details
$agent_id = $_SESSION['user_id'];
$agent_query = "SELECT first_name, last_name, phone_number, referral_code, binance_address FROM users WHERE id = '$agent_id'";
$agent_result = mysqli_query($conn, $agent_query);
$agent = mysqli_fetch_assoc($agent_result);

// Fetch client details
$client_id = isset($_GET['client_id']) ? $_GET['client_id'] : null;

if ($client_id) {
    $client_query = "SELECT balance, phone_number FROM users WHERE id = '$client_id' AND role = 'client'";
    $client_result = mysqli_query($conn, $client_query);
    $client = mysqli_fetch_assoc($client_result);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update agent's personal details
    if (isset($_POST['phone_number']) && isset($_POST['fname']) && isset($_POST['lname'])) {
        $new_phone_number = $_POST['phone_number'];
        $new_fname = $_POST['fname'];
        $new_lname = $_POST['lname'];
        $new_binance_address = $_POST['binance_address'] ?? '';

        $update_query = "UPDATE users SET phone_number = '$new_phone_number', first_name = '$new_fname', last_name = '$new_lname', binance_address = '$new_binance_address' WHERE id = '$agent_id'";
        if (mysqli_query($conn, $update_query)) {
            $success_message = "Settings updated successfully.";
        } else {
            $error_message = "Error updating settings: " . mysqli_error($conn);
        }
    }

    // Update client balance
    if (isset($_POST['client_balance']) && $client_id) {
        $new_balance = $_POST['client_balance'];

        $update_balance_query = "UPDATE users SET balance = '$new_balance' WHERE id = '$client_id'";
        if (mysqli_query($conn, $update_balance_query)) {
            $success_message = "Client balance updated successfully.";
        } else {
            $error_message = "Error updating client balance: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings - Coom Marketing</title>
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
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            padding-top: 2rem;
            max-width: 800px;
        }
        
        h2, h3 {
            color: var(--text-color);
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-control {
            background-color: #2d3748;
            border: 1px solid var(--card-border);
            color: var(--text-color);
            padding: 0.75rem;
            border-radius: 8px;
        }
        
        .form-control:focus {
            background-color: #2d3748;
            color: var(--text-color);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        
        .form-control:disabled {
            background-color: #1e293b;
            opacity: 0.7;
        }
        
        .btn {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .alert {
            border-radius: 8px;
        }
        
        .button-group {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .settings-card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="settings-card">
            <h2><i class="fas fa-user-cog me-2"></i>Account Settings</h2>
            
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                </div>
            <?php elseif (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="fname"><i class="fas fa-user"></i> First Name</label>
                    <input type="text" class="form-control" id="fname" name="fname" 
                           value="<?php echo htmlspecialchars($agent['first_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="lname"><i class="fas fa-user"></i> Last Name</label>
                    <input type="text" class="form-control" id="lname" name="lname" 
                           value="<?php echo htmlspecialchars($agent['last_name'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone_number"><i class="fas fa-phone"></i> Phone Number</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" 
                           value="<?php echo htmlspecialchars($agent['phone_number'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="referral_code"><i class="fas fa-user-tag"></i> Referral Code</label>
                    <input type="text" class="form-control" id="referral_code" 
                           value="<?php echo htmlspecialchars($agent['referral_code'] ?? ''); ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="binance_address"><i class="fab fa-btc"></i> Binance Address</label>
                    <input type="text" class="form-control" id="binance_address" name="binance_address" 
                           value="<?php echo htmlspecialchars($agent['binance_address'] ?? ''); ?>" placeholder="Enter your Binance wallet address">
                    <small class="form-text text-muted">Enter your Binance wallet address to receive cryptocurrency deposits from clients</small>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Settings
                </button>
            </form>
        </div>

        <?php if ($client_id && $client): ?>
            <div class="settings-card">
                <h3><i class="fas fa-wallet me-2"></i>Update Client Balance</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="client_phone"><i class="fas fa-phone"></i> Client Phone Number</label>
                        <input type="text" class="form-control" id="client_phone" 
                               value="<?php echo htmlspecialchars($client['phone_number'] ?? ''); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label for="client_balance"><i class="fas fa-money-bill-wave"></i> Client Balance (USD)</label>
                        <input type="number" step="0.01" class="form-control" id="client_balance" name="client_balance" 
                               value="<?php echo htmlspecialchars($client['balance'] ?? ''); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle me-2"></i>Update Balance
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <div class="button-group">
            <a href="agent_dashboard.php" class="btn btn-success">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <a href="../auth/logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>