<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../includes/db.php';

$userId = $_SESSION['user_id'];
$errorMessage = "";
$successMessage = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $currentPassword = htmlspecialchars($_POST['current_password']);
    $newPassword = htmlspecialchars($_POST['new_password']);
    $confirmPassword = htmlspecialchars($_POST['confirm_password']);

    // Check if new password matches confirmation
    if ($newPassword !== $confirmPassword) {
        $errorMessage = "New password and confirmation password do not match.";
    } else {
        // Fetch the current password from the database
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($dbPassword);
        $stmt->fetch();
        $stmt->close();

        // Check if the entered current password is correct
        if (!password_verify($currentPassword, $dbPassword)) {
            $errorMessage = "Current password is incorrect.";
        } else {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // Update the password in the database
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hashedPassword, $userId);

            if ($stmt->execute()) {
                $successMessage = "Password successfully changed!";
            } else {
                $errorMessage = "Failed to change password. Please try again.";
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harbor Investment - Change Password</title>

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
            --border-color: #3a3a5a;
        }
        
        body {
            background-color: var(--primary-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .password-card {
            margin-top: 20px;
            background-color: var(--card-bg);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            border: 1px solid var(--border-color);
        }

        .password-card h4 {
            margin-bottom: 20px;
            color: var(--highlight-color);
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .form-control, .form-select {
            background-color: var(--input-bg);
            border: 1px solid var(--border-color);
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
            border-color: var(--border-color);
            background-color: transparent;
            padding: 10px 20px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--border-color);
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
            border-top: 1px solid var(--border-color);
            z-index: 1000;
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
            margin-top: 20px;
        }
        
        .action-buttons .btn {
            flex: 1;
        }
        
        .input-group-text {
            background-color: var(--input-bg);
            color: #8e8e9a;
            border-color: var(--border-color);
        }
        
        .password-toggle {
            cursor: pointer;
            transition: color 0.2s ease;
        }
        
        .password-toggle:hover {
            color: var(--highlight-color);
        }
        
        .password-strength {
            height: 5px;
            border-radius: 5px;
            margin-top: 5px;
            background-color: #444;
            position: relative;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            border-radius: 5px;
            width: 0;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        
        .password-rules {
            font-size: 0.85rem;
            color: #8e8e9a;
            margin-top: 5px;
        }
        
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .back-button {
            color: #8e8e9a;
            background-color: var(--secondary-color);
            border: 1px solid var(--border-color);
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .back-button:hover {
            color: var(--text-color);
            background-color: var(--accent-color);
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <div class="text-center mb-3">
        <div class="logo-text">
            <i class="fas fa-landmark me-2"></i>  Coom Marketing
        </div>
    </div>
    
    <div class="password-card">
        <div class="header-section">
            <h4><i class="fas fa-lock me-2"></i>Change Password</h4>
            <a href="account.php" class="back-button">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>

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

        <!-- Password change form -->
        <form action="change_password.php" method="POST">
            <div class="mb-3">
                <label for="current_password" class="form-label">Current Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                    <input type="password" name="current_password" id="current_password" class="form-control" required>
                    <span class="input-group-text password-toggle" onclick="togglePassword('current_password')">
                        <i class="fas fa-eye" id="current_password_icon"></i>
                    </span>
                </div>
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" name="new_password" id="new_password" class="form-control" required onkeyup="checkPasswordStrength()">
                    <span class="input-group-text password-toggle" onclick="togglePassword('new_password')">
                        <i class="fas fa-eye" id="new_password_icon"></i>
                    </span>
                </div>
                <div class="password-strength">
                    <div class="password-strength-bar" id="password-strength-bar"></div>
                </div>
                <div class="password-rules">
                    Password should be at least 8 characters and include uppercase, lowercase, numbers, and special characters.
                </div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock-open"></i></span>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required onkeyup="checkPasswordMatch()">
                    <span class="input-group-text password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye" id="confirm_password_icon"></i>
                    </span>
                </div>
                <div id="password-match-message" class="password-rules"></div>
            </div>

            <div class="action-buttons">
                <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='account.php'">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
                <button type="submit" class="btn btn-primary" id="submit-button">
                    <i class="fas fa-check me-2"></i>Update Password
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

<!-- JavaScript for password functionality -->
<script>
function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '_icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function checkPasswordStrength() {
    const password = document.getElementById('new_password').value;
    const strengthBar = document.getElementById('password-strength-bar');
    
    // Calculate password strength
    let strength = 0;
    
    if (password.length >= 8) strength += 25;
    if (password.match(/[A-Z]/)) strength += 25;
    if (password.match(/[0-9]/)) strength += 25;
    if (password.match(/[^A-Za-z0-9]/)) strength += 25;
    
    // Update the strength bar
    strengthBar.style.width = strength + '%';
    
    if (strength <= 25) {
        strengthBar.style.backgroundColor = '#c62828'; // Weak
    } else if (strength <= 50) {
        strengthBar.style.backgroundColor = '#ff9800'; // Medium
    } else if (strength <= 75) {
        strengthBar.style.backgroundColor = '#ffc107'; // Good
    } else {
        strengthBar.style.backgroundColor = '#4caf50'; // Strong
    }
    
    checkPasswordMatch();
}

function checkPasswordMatch() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const matchMessage = document.getElementById('password-match-message');
    const submitButton = document.getElementById('submit-button');
    
    if (!confirmPassword) {
        matchMessage.textContent = '';
        return;
    }
    
    if (newPassword === confirmPassword) {
        matchMessage.textContent = 'Passwords match';
        matchMessage.style.color = '#4caf50';
        submitButton.disabled = false;
    } else {
        matchMessage.textContent = 'Passwords do not match';
        matchMessage.style.color = '#c62828';
        submitButton.disabled = true;
    }
}
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>