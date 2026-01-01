<?php
session_start();

// Ensure the user is logged in and has a 'client' role
if ($_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

// Fetch the user's details from the session
$phoneNumber = $_SESSION['phone_number'];
$referralCode = $_SESSION['referral_code'];
$userId = $_SESSION['user_id'];

include '../includes/db.php'; // Include database connection
include '../includes/function.php'; // Include helper functions

// Fetch the user's balance, referral bonus, first name, and last name from the users table
$sql = "SELECT balance, referral_bonus, fname, lname FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($balance, $referralBonus, $fname, $lname);
$stmt->fetch();
$stmt->close();

// Fetch the total profit (daily income) from the investments table
$sql = "SELECT SUM(daily_profit) as total_daily_income FROM investments WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($totalDailyIncome);
$stmt->fetch();
$stmt->close();

// Fetch the user's social media links from the database
$sql = "SELECT facebook, twitter, telegram, whatsapp FROM social_links";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stmt->bind_result($facebookLink, $twitterLink, $telegramLink, $whatsappLink);
$stmt->fetch();
$stmt->close();

// Close the connection
$conn->close();

// Check if name is missing
$nameMissing = empty($fname) || empty($lname);

// Count how many social links we have
$socialLinksCount = 0;
if (!empty($facebookLink)) $socialLinksCount++;
if (!empty($twitterLink)) $socialLinksCount++;
if (!empty($telegramLink)) $socialLinksCount++;
if (!empty($whatsappLink)) $socialLinksCount++;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Information | Harbor Investment</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.2/css/all.min.css" rel="stylesheet">

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
        .profile-card {
            background-color: var(--secondary-color);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .profile-info {
            font-size: 16px;
            margin-bottom: 15px;
            padding: 10px;
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 5px;
        }
        .account-action {
            padding: 10px;
        }
        .action-link {
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
            text-decoration: none;
            color: var(--text-color);
            padding: 10px;
            border-radius: 5px;
            background-color: rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        .action-link:hover {
            background-color: var(--accent-color);
            color: white;
            text-decoration: none;
            transform: translateX(5px);
        }
        .action-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .balance {
            font-size: 22px;
            font-weight: bold;
            color: var(--accent-color);
        }
        .logout-btn {
            margin-top: 20px;
        }
        .container {
            margin-bottom: 100px;
        }
        /* Social media button styles */
        .social-media-container {
            position: fixed;
            bottom: 110px;
            right: 20px;
            z-index: 1000;
        }
        .social-media-button {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--accent-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }
        .social-media-button:hover {
            transform: scale(1.1);
        }
        .social-media-links {
            position: absolute;
            bottom: 60px;
            right: 0;
            display: none;
            flex-direction: column;
            gap: 10px;
            transition: all 0.3s ease;
        }
        .social-media-links.show {
            display: flex;
        }
        .social-media-links a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--secondary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }
        .social-media-links a:hover {
            transform: translateY(-3px);
        }
        .alert-warning {
            background-color: #f39c12;
            color: #2c3e50;
            border: none;
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
    </style>
</head>
<body>

<div class="container">
    <div class="profile-card">
        <h2 class="text-center mb-4">Account Information</h2>

        <?php if ($nameMissing): ?>
            <div class="alert alert-warning text-center" role="alert">
                Your name is missing. Please <a href="../actions/edit_profile.php" class="alert-link">update your profile</a>.
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-6">
                <div class="profile-info">
                    <strong>Names:</strong> 
                    <?php 
                    echo $nameMissing ? "Please update your name" : htmlspecialchars($fname) . " " . htmlspecialchars($lname); 
                    ?>
                </div>

                <div class="profile-info">
                    <strong>Phone Number:</strong> <span class="text-warning"><?php echo htmlspecialchars($phoneNumber); ?></span>
                </div>

                <div class="profile-info">
                    <strong>Referral Code:</strong> <?php echo htmlspecialchars($referralCode); ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="profile-info">
                    <strong>Account Balance:</strong>
                    <span class="balance">RWF <?php echo number_format($balance, 2); ?></span>
                </div>

                <div class="profile-info">
                    <strong>Project Revenue:</strong>
                    <span class="balance">RWF <?php echo number_format($totalDailyIncome, 2); ?></span>
                </div>

                <div class="profile-info">
                    <strong>Invitation Income:</strong>
                    <span class="balance">RWF <?php echo number_format($referralBonus, 2); ?></span>
                </div>
            </div>
        </div>

        <hr>

        <!-- Reorganized Account actions -->
        <div class="account-action row">
            <div class="col-md-4">
                <a href="recharge.php" class="action-link"><i class="fas fa-plus-circle"></i> Deposit Funds</a>
                <a href="withdrawal.php" class="action-link"><i class="fas fa-minus-circle"></i> Withdraw Funds</a>
                <a href="my_wallet.php" class="action-link"><i class="fas fa-wallet"></i> My Wallet</a>
            </div>
            <div class="col-md-4">
                <a href="binding_bank.php" class="action-link"><i class="fas fa-university"></i> Bank Details</a>
                <a href="transaction_history.php" class="action-link"><i class="fas fa-history"></i> Transactions</a>
                <a href="change_password.php" class="action-link"><i class="fas fa-lock"></i> Change Password</a>
            </div>
            <div class="col-md-4">
                <a href="invite.php" class="action-link"><i class="fas fa-user-friends"></i> Invite Friends</a>
                <a href="my_team.php" class="action-link"><i class="fas fa-users"></i> My Team</a>
                <a href="../actions/edit_profile.php" class="action-link"><i class="fas fa-user-edit"></i> Edit Profile</a>
            </div>
        </div>

        <!-- Logout Button -->
        <div class="text-center logout-btn">
            <a href="../actions/logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</div>

<!-- Social Media Button and Links -->
<div class="social-media-container">
    <?php if ($socialLinksCount > 0): ?>
        <div class="social-media-button" id="socialMediaButton">
            <i class="fas fa-share-alt"></i>
        </div>
        <div class="social-media-links" id="socialMediaLinks">
            <?php if (!empty($facebookLink)): ?>
                <a href="<?php echo htmlspecialchars($facebookLink); ?>" target="_blank" aria-label="Facebook" style="background-color: #3b5998;">
                    <i class="fab fa-facebook-f"></i>
                </a>
            <?php endif; ?>
            <?php if (!empty($twitterLink)): ?>
                <a href="<?php echo htmlspecialchars($twitterLink); ?>" target="_blank" aria-label="Twitter" style="background-color: #1da1f2;">
                    <i class="fab fa-twitter"></i>
                </a>
            <?php endif; ?>
            <?php if (!empty($telegramLink)): ?>
                <a href="<?php echo htmlspecialchars($telegramLink); ?>" target="_blank" aria-label="Telegram" style="background-color: #0088cc;">
                    <i class="fab fa-telegram"></i>
                </a>
            <?php endif; ?>
            <?php if (!empty($whatsappLink)): ?>
                <a href="<?php echo htmlspecialchars($whatsappLink); ?>" target="_blank" aria-label="WhatsApp" style="background-color: #25D366;">
                    <i class="fab fa-whatsapp"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
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
                <a href="account.php" class="nav-link active">
                    <i class="fas fa-user"></i>
                    <span>Account</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Toggle social media links visibility
    document.getElementById('socialMediaButton').addEventListener('click', function() {
        document.getElementById('socialMediaLinks').classList.toggle('show');
    });

    // Close social media links when clicking outside
    document.addEventListener('click', function(event) {
        const socialContainer = document.querySelector('.social-media-container');
        const socialButton = document.getElementById('socialMediaButton');
        const socialLinks = document.getElementById('socialMediaLinks');
        
        if (!socialContainer.contains(event.target) && socialLinks.classList.contains('show')) {
            socialLinks.classList.remove('show');
        }
    });
</script>
</body>
</html>