<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../includes/db.php';

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize user input
    $bankName = htmlspecialchars($_POST['bank_name']);
    $accountNumber = htmlspecialchars($_POST['account_number']);
    $accountHolder = htmlspecialchars($_POST['account_holder']);

    // Insert or update bank details in the database
    $sql = "REPLACE INTO user_banks (user_id, bank_name, account_number, account_holder) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $userId, $bankName, $accountNumber, $accountHolder);

    if ($stmt->execute()) {
        $successMessage = "Bank details successfully updated!";
    } else {
        $errorMessage = "Failed to update bank details. Please try again.";
    }

    $stmt->close();
}

$sql = "SELECT bank_name, account_number, account_holder FROM user_banks WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($bankName, $accountNumber, $accountHolder);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harbor Investment - Link Account</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a1a2e;
            --secondary-color: #16213e;
            --accent-color: #0f3460;
            --highlight-color: #2d60b8;
            --text-color: #e1e1e1;
            --card-bg: #222536;
            --input-bg: #2c2f44;
            --success-color: #2e7d32;
            --danger-color: #c62828;
        }
        
        body {
            background-color: var(--primary-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .bank-card {
            margin-top: 20px;
            background-color: var(--card-bg);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border: 1px solid #3a3a5a;
        }

        .bank-card h4 {
            margin-bottom: 20px;
            color: var(--highlight-color);
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .form-control, .form-select {
            background-color: var(--input-bg);
            border: 1px solid #3a3a5a;
            color: var(--text-color);
            border-radius: 8px;
            padding: 12px 15px;
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--input-bg);
            color: var(--text-color);
            border-color: var(--highlight-color);
            box-shadow: 0 0 0 0.25rem rgba(45, 96, 184, 0.25);
        }

        .form-label {
            color: #b0b0b0;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .btn-primary {
            background-color: var(--highlight-color);
            border: none;
            padding: 12px 24px;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #3570d8;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .btn-outline-secondary {
            color: #b0b0b0;
            border-color: #3a3a5a;
            background-color: transparent;
            padding: 10px 20px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-secondary:hover {
            background-color: #3a3a5a;
            color: var(--text-color);
        }
        
        .alert-success {
            background-color: var(--success-color);
            color: white;
            border: none;
            border-radius: 8px;
        }

        .alert-danger {
            background-color: var(--danger-color);
            color: white;
            border: none;
            border-radius: 8px;
        }

        /* Bottom navbar styles */
        .bottom-navbar {
            background-color: var(--secondary-color);
            padding: 12px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.2);
            border-top: 1px solid #3a3a5a;
        }

        .bottom-navbar .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .bottom-navbar .nav-link {
            color: #8e8e9a;
            text-align: center;
            padding: 8px 0;
            transition: all 0.3s ease;
            font-size: 0.85rem;
        }

        .bottom-navbar .nav-link i {
            font-size: 1.4rem;
            margin-bottom: 4px;
        }

        .bottom-navbar .nav-link:hover, 
        .bottom-navbar .nav-link.active {
            color: var(--highlight-color);
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--highlight-color);
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        
        .container {
            margin-bottom: 90px;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .action-buttons .btn {
            flex: 1;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="text-center mb-3">
        <div class="logo-text">
            <i class="fas fa-landmark me-2"></i> Harbor Investment
        </div>
    </div>
    
    <div class="bank-card">
        <h4><i class="fas fa-link me-2"></i>Link Payment Account</h4>

        <!-- Display success or error message -->
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?php echo $successMessage; ?>
            </div>
        <?php elseif (!empty($errorMessage)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <!-- Bank binding form -->
        <form action="binding_bank.php" method="POST">
            <div class="mb-3">
                <label for="bank_name" class="form-label">Payment Provider</label>
                <select name="bank_name" id="bank_name" class="form-select" required>
                    <option value="" disabled selected>Select a payment provider</option>
                    <option value="MTN Mobile Money" <?php echo (isset($bankName) && $bankName == 'MTN Mobile Money') ? 'selected' : ''; ?>>MTN Mobile Money</option>
                    <option value="Airtel Money" <?php echo (isset($bankName) && $bankName == 'Airtel Money') ? 'selected' : ''; ?>>Airtel Money</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="account_number" class="form-label">Account Number</label>
                <div class="input-group">
                    <span class="input-group-text" style="background-color: var(--input-bg); color: var(--text-color); border-color: #3a3a5a;">
                        <i class="fas fa-hashtag"></i>
                    </span>
                    <input type="text" name="account_number" id="account_number" min='10' class="form-control" required value="<?php echo isset($accountNumber) ? htmlspecialchars($accountNumber) : ''; ?>" placeholder="Enter your account number">
                </div>
            </div>

            <div class="mb-3">
                <label for="account_holder" class="form-label">Account Holder Name</label>
                <div class="input-group">
                    <span class="input-group-text" style="background-color: var(--input-bg); color: var(--text-color); border-color: #3a3a5a;">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" name="account_holder" id="account_holder" class="form-control" required value="<?php echo isset($accountHolder) ? htmlspecialchars($accountHolder) : ''; ?>" placeholder="Enter account holder name">
                </div>
            </div>

            <div class="action-buttons">
                <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='account.php'">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Account
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Bottom Navbar -->
<nav class="bottom-navbar">
    <!-- <div class="container"> -->
        <div class="row text-center">
            <div class="col-3">
                <div class="nav-item">
                    <a href="client_dashboard.php" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
            </div>
            <div class="col-3">
                <div class="nav-item">
                    <a href="purchased.php" class="nav-link">
                        <i class="fas fa-briefcase"></i>
                        <span>Portfolio</span>
                    </a>
                </div>
            </div>
            <div class="col-3">
                <div class="nav-item">
                    <a href="invite.php" class="nav-link">
                        <i class="fas fa-user-plus"></i>
                        <span>Referral</span>
                    </a>
                </div>
            </div>
            <div class="col-3">
                <div class="nav-item">
                    <a href="account.php" class="nav-link active">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>