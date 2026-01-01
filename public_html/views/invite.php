<?php
session_start();
if ($_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}
$referralCode = $_SESSION['referral_code'];

// Create a referral link
$baseURL = "https://herbalinside.com/views/register.php"; // Updated to Harbor Investment
$inviteLink = $baseURL . "?referral_code=" . urlencode($referralCode);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invite Friends - Harbor Investment</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

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
            --nav-bg: #1a2a3a;
            --accent-color: #38bdf8;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 80px;
        }
        
        .container {
            padding-top: 2rem;
            max-width: 800px;
        }
        
        .page-title {
            color: var(--accent-color);
            margin-bottom: 1.5rem;
            font-weight: 600;
            text-align: center;
        }
        
        .bonus-rules {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .bonus-rules ul {
            padding-left: 1.5rem;
        }
        
        .bonus-rules li {
            margin-bottom: 0.75rem;
            color: var(--text-color);
        }
        
        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-bottom: 1px solid var(--card-border);
            font-weight: 500;
            padding: 1rem;
            text-align: center;
        }
        
        .card-body {
            padding: 2rem;
            text-align: center;
        }
        
        .referral-code {
            font-size: 1.75rem;
            color: var(--warning-color);
            font-weight: 700;
            margin: 1rem 0;
            padding: 0.75rem;
            background-color: rgba(245, 158, 11, 0.1);
            border-radius: 8px;
            display: inline-block;
        }
        
        .share-message {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            color: var(--text-muted);
        }
        
        .invite-link {
            word-break: break-all;
            color: var(--accent-color);
            margin: 1rem 0;
            padding: 0.75rem;
            background-color: rgba(56, 189, 248, 0.1);
            border-radius: 8px;
        }
        
        .btn-copy {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-copy:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }
        
        .btn-dashboard {
            color: var(--accent-color);
            text-decoration: none;
            margin-top: 1.5rem;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-dashboard:hover {
            color: var(--primary-color);
            text-decoration: underline;
        }
        
        .navbar-bottom {
            background-color: var(--nav-bg);
            border-top: 1px solid var(--card-border);
            padding: 0.5rem 0;
        }
        
        .nav-btn {
            color: var(--text-color);
            text-align: center;
            padding: 0.5rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .nav-btn.active {
            color: var(--accent-color);
        }
        
        .nav-btn i {
            display: block;
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }
        
        @media (min-width: 768px) {
            .nav-btn {
                font-size: 1rem;
            }
            
            .nav-btn i {
                display: inline-block;
                margin-right: 0.5rem;
                margin-bottom: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="page-title"><i class="fas fa-user-friends me-2"></i>Invite Friends</h1>
        
        <div class="bonus-rules">
            <h3 class="text-center mb-3" style="color: var(--accent-color);">Referral Bonus Program</h3>
            <ul>
                <li>Earn <strong class="highlight">10% bonus</strong> when your Level 1 referrals invest</li>
                <li>Earn <strong class="highlight">7% bonus</strong> when your Level 2 referrals invest</li>
                <li>Bonuses are credited automatically to your balance</li>
                <li>No limits - withdraw your earnings anytime</li>
            </ul>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-gift me-2"></i>Your Referral Code</h3>
            </div>
            <div class="card-body">
                <div class="share-message">Share your unique code with friends and start earning bonuses</div>
                <div class="referral-code">
                    <i class="fas fa-tag me-2"></i><?php echo $referralCode; ?>
                </div>
                
                <div class="share-message mt-4">Or share this direct invitation link:</div>
                <div class="invite-link" id="invite-link">
                    <i class="fas fa-link me-2"></i><?php echo $inviteLink; ?>
                </div>
                
                <button id="copyButton" class="btn btn-copy">
                    <i class="fas fa-copy me-2"></i>Copy Link
                </button>
                
                <a href="client_dashboard.php" class="btn-dashboard">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Fixed Bottom Navbar -->
    <nav class="navbar navbar-bottom fixed-bottom">
        <div class="container-fluid">
            <div class="row w-100">
                <div class="col-3">
                    <a href="client_dashboard.php" class="nav-btn">
                        <i class="fas fa-box"></i>
                        <span>Invest</span>
                    </a>
                </div>
                <div class="col-3">
                    <a href="purchased.php" class="nav-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Portfolio</span>
                    </a>
                </div>
                <div class="col-3">
                    <a href="invite.php" class="nav-btn active">
                        <i class="fas fa-user-friends"></i>
                        <span>Invite</span>
                    </a>
                </div>
                <div class="col-3">
                    <a href="account.php" class="nav-btn">
                        <i class="fas fa-user-circle"></i>
                        <span>Account</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Copy Referral Link Script -->
    <script>
        document.getElementById("copyButton").addEventListener("click", function() {
            var copyText = document.getElementById("invite-link").textContent.trim();
            navigator.clipboard.writeText(copyText).then(function() {
                // Change button text temporarily
                var originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
                this.classList.add('btn-success');
                this.classList.remove('btn-primary');
                
                // Revert after 2 seconds
                setTimeout(function() {
                    document.getElementById("copyButton").innerHTML = originalText;
                    document.getElementById("copyButton").classList.add('btn-primary');
                    document.getElementById("copyButton").classList.remove('btn-success');
                }, 2000);
            }.bind(this), function() {
                alert("Failed to copy text. Please try again.");
            });
        });
    </script>
</body>
</html>