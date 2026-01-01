<?php
session_start();
if ($_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}
date_default_timezone_set("Africa/Kigali"); 
// Database connection
require_once('../includes/db.php');
include '../includes/function.php';

// Minimum deposit amount
$min_deposit = 3000;
$max_deposit = 3000000;

// Initialize variables
$selected_agent = null;

// Handle the recharge form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $client_id = $_SESSION['user_id'];
    $agent_id = intval($_POST['agent']);  // Selected agent

    // Validate the amount
    if ($amount < $min_deposit || $amount > $max_deposit) {
        echo "<script>alert('The minimum deposit amount is 3,000 RWF AND maximum deposit amount is 3000,000 RWF. Please enter a valid amount.');</script>";
    } elseif ($amount > 0) {
        // Insert recharge record in the recharges table with status 'pending'
        $insert_recharge_query = "INSERT INTO recharges (client_id, agent_id, amount, status) VALUES (?, ?, ?, 'pending')";
        $stmt = mysqli_prepare($conn, $insert_recharge_query);
        mysqli_stmt_bind_param($stmt, "iid", $client_id, $agent_id, $amount);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Simulate notification to agent
        echo "<script>alert('Recharge request successful! Copy that number and send money to that number.');</script>";

        // Fetch selected agent's information
        $agent_query = "SELECT phone_number, CONCAT(fname, ' ', lname) AS name FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $agent_query);
        mysqli_stmt_bind_param($stmt, "i", $agent_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $selected_agent_phone, $selected_agent_name);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        $selected_agent = [
            'name' => $selected_agent_name,
            'phone' => $selected_agent_phone
        ];
    } else {
        echo "<script>alert('Invalid amount. Please enter a positive number.');</script>";
    }
}

// Fetch the current balance
$current_balance_query = "SELECT balance FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $current_balance_query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $current_balance);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Fetch a random agent (single random agent)
$random_agent_query = "SELECT id, phone_number, CONCAT(fname, ' ', lname) AS name FROM users WHERE role = 'agent' ORDER BY RAND() LIMIT 1";
$stmt = mysqli_prepare($conn, $random_agent_query);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $random_agent_id, $random_agent_phone, $random_agent_name);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

$random_agent = [
    'id' => $random_agent_id,
    'phone' => $random_agent_phone,
    'name' => $random_agent_name
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recharge Account | Harbor Investment</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.2/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        :root {
            --primary-color: #1a2a3a;
            --secondary-color: #2c3e50;
            --accent-color: #3498db;
            --text-color: #ecf0f1;
            --highlight-color: #e74c3c;
        }
        
        body {
            background-color: var(--primary-color);
            color: var(--text-color);
        }
        .container {
            background-color: var(--secondary-color);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            margin-bottom: 100px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .form-control, .form-control:focus {
            background-color: rgba(0, 0, 0, 0.2);
            color: var(--text-color);
            border-color: rgba(255, 255, 255, 0.1);
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-outline-secondary {
            color: var(--text-color);
            border-color: var(--text-color);
        }
        .btn-outline-secondary:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        hr {
            border-color: rgba(255, 255, 255, 0.1);
        }
        .fixed-bottom {
            background-color: var(--secondary-color) !important;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        .nav-link {
            color: var(--text-color);
            padding: 10px 5px;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--accent-color);
        }
        .nav-link i {
            display: block;
            margin: 0 auto 5px;
            font-size: 1.2rem;
        }
        .nav-link span {
            font-size: 0.8rem;
        }
        .payment-method {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .payment-method i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--accent-color);
        }
    </style>
</head>
<body>
    <div class="container mt-4 mb-12">
        <h2 class="text-center mb-4">Recharge Your Account</h2>
        
        <div class="row">
            <div class="col-md-6">
                <div class="profile-info mb-4">
                    <p>Your current balance: <span class="balance">RWF <?php echo number_format($current_balance, 2); ?></span></p>
                    <p><small class="text-muted">Minimum deposit: RWF 3,000 | Maximum deposit: RWF 3,000,000</small></p>
                </div>

                <form action="" method="POST">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Recharge Amount (RWF)</label>
                        <input type="number" class="form-control" id="amount" name="amount" required min="<?php echo $min_deposit; ?>" max="<?php echo $max_deposit; ?>" step="100">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Selected Agent</label>
                        <div class="payment-method text-center">
                            <i class="fas fa-user-tie"></i>
                            <h5><?php echo htmlspecialchars($random_agent['name']); ?></h5>
                            <p class="mb-2">Phone: <?php echo htmlspecialchars($random_agent['phone']); ?></p>
                            <input type="hidden" name="agent" value="<?php echo htmlspecialchars($random_agent['id']); ?>">
                            <button type="button" class="btn btn-outline-secondary copy-phone" data-phone="<?php echo htmlspecialchars($random_agent['phone']); ?>">
                                <i class="fas fa-copy"></i> Copy Number
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2">
                        <i class="fas fa-plus-circle"></i> Recharge Account
                    </button>
                </form>
            </div>

            <div class="col-md-6">
                <div class="payment-methods">
                    <h4 class="mb-3">Payment Methods</h4>
                    
                    <div class="payment-method text-center">
                        <i class="fas fa-mobile-alt"></i>
                        <h5>Mobile Money</h5>
                        <p>Send money to the agent's number</p>
                        <a href="tel:*182*1*1*<?php echo htmlspecialchars($random_agent['phone']); ?>#" class="btn btn-outline-secondary">
                            <i class="fas fa-phone"></i> Dial *182*1*1*<?php echo htmlspecialchars($random_agent['phone']); ?>#
                        </a>
                    </div>
                    
                    <?php if ($selected_agent): ?>
                    <div class="payment-method text-center mt-4">
                        <i class="fas fa-check-circle text-success"></i>
                        <h5>Request Submitted</h5>
                        <p>Send money to:</p>
                        <h6><?php echo htmlspecialchars($selected_agent['name']); ?></h6>
                        <p><?php echo htmlspecialchars($selected_agent['phone']); ?></p>
                        <button class="btn btn-outline-secondary copy-phone" data-phone="<?php echo htmlspecialchars($selected_agent['phone']); ?>">
                            <i class="fas fa-copy"></i> Copy Number
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Fixed Bottom Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-bottom">
        <div class="container-fluid">
            <div class="row w-100 text-center">
                <div class="col-3 text-center">
                    <a href="client_dashboard.php" class="nav-link">
                        <i class="fas fa-box"></i>
                        <span>Products</span>
                    </a>
                </div>
                <div class="col-3 text-center">
                    <a href="purchased.php" class="nav-link">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Investments</span>
                    </a>
                </div>
                <div class="col-3 text-center">
                    <a href="invite.php" class="nav-link">
                        <i class="fas fa-user-plus"></i>
                        <span>Invite</span>
                    </a>
                </div>
                <div class="col-3 text-center">
                    <a href="account.php" class="nav-link">
                        <i class="fas fa-user"></i>
                        <span>Account</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.copy-phone').click(function(e) {
                e.preventDefault();
                const phoneNumber = $(this).data('phone');
                navigator.clipboard.writeText(phoneNumber).then(() => {
                    // Change button temporarily to show success
                    const originalHtml = $(this).html();
                    $(this).html('<i class="fas fa-check"></i> Copied!');
                    setTimeout(() => {
                        $(this).html(originalHtml);
                    }, 2000);
                }).catch(err => {
                    console.error('Could not copy text: ', err);
                    alert('Failed to copy. Please copy manually.');
                });
            });
            
            // Display current time in Kigali
            function updateKigaliTime() {
                const options = { timeZone: 'Africa/Kigali', hour: '2-digit', minute: '2-digit', second: '2-digit' };
                const formatter = new Intl.DateTimeFormat('en-US', options);
                document.getElementById('kigali-time').textContent = formatter.format(new Date());
            }
            setInterval(updateKigaliTime, 1000);
        });
    </script>
</body>
</html>