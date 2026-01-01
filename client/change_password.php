<?php
session_start();
include '../includes/db_connection.php';
include 'nav.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords
    if ($new_password !== $confirm_password) {
        $error = "New passwords do not match";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long";
    } else {
        // Get current password hash
        $query = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success = "Password changed successfully!";
            } else {
                $error = "Failed to update password: " . $conn->error;
            }
        } else {
            $error = "Current password is incorrect";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COOM-MARKETING - Change Password</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-bg: #0d1117;
            --secondary-bg: #161b22;
            --card-bg: #1a2029;
            --accent-color: #23a559;
            --accent-color-light: #37c070;
            --text-color: #e6edf3;
            --text-secondary: #7d8590;
            --border-color: #303841;
            --positive: #23a559;
            --negative: #e34c26;
            --header-bg: #0d1117;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--primary-bg);
            color: var(--text-color);
            min-height: 100vh;
            padding-top: 80px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 15px;
        }
        
        .password-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: var(--accent-color-light);
        }
        
        .page-header p {
            color: var(--text-secondary);
            font-size: 16px;
        }
        
        .form-container {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            border: 1px solid var(--border-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--secondary-bg);
            color: var(--text-color);
            font-size: 16px;
        }
        
        .submit-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: var(--accent-color-light);
        }
        
        .error-message {
            color: var(--negative);
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background-color: rgba(227, 76, 38, 0.1);
            border-radius: 8px;
        }
        
        .success-message {
            color: var(--positive);
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background-color: rgba(35, 165, 89, 0.1);
            border-radius: 8px;
        }
        
        .password-requirements {
            background-color: var(--secondary-bg);
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
            font-size: 14px;
        }
        
        .requirement {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        
        .requirement i {
            margin-right: 8px;
        }
        
        .requirement.valid {
            color: var(--positive);
        }
        
        .requirement.invalid {
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="password-container">
            <div class="page-header">
                <h1><i class="fas fa-lock"></i> Change Password</h1>
                <p>Secure your account with a new password</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form action="change_password.php" method="post">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" placeholder="Enter current password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
                        <div class="password-requirements">
                            <div class="requirement" id="length-req">
                                <i class="fas fa-circle"></i>
                                At least 6 characters long
                            </div>
                            <div class="requirement" id="match-req">
                                <i class="fas fa-circle"></i>
                                Must match confirmation
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                    </div>
                    
                    <button type="submit" class="submit-btn">Change Password</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Password validation
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const lengthReq = document.getElementById('length-req');
        const matchReq = document.getElementById('match-req');
        
        function validatePassword() {
            const newPassword = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            // Check length
            if (newPassword.length >= 6) {
                lengthReq.classList.add('valid');
                lengthReq.classList.remove('invalid');
                lengthReq.querySelector('i').className = 'fas fa-check';
            } else {
                lengthReq.classList.add('invalid');
                lengthReq.classList.remove('valid');
                lengthReq.querySelector('i').className = 'fas fa-circle';
            }
            
            // Check if passwords match
            if (newPassword === confirmPassword && newPassword.length > 0) {
                matchReq.classList.add('valid');
                matchReq.classList.remove('invalid');
                matchReq.querySelector('i').className = 'fas fa-check';
            } else {
                matchReq.classList.add('invalid');
                matchReq.classList.remove('valid');
                matchReq.querySelector('i').className = 'fas fa-circle';
            }
        }
        
        newPasswordInput.addEventListener('input', validatePassword);
        confirmPasswordInput.addEventListener('input', validatePassword);
    </script>
</body>
</html>