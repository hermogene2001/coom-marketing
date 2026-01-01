<?php
session_start();
include '../includes/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables
$error = '';
$success = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (strlen($new_password) < 8) {
        $error = 'Password must be at least 8 characters';
    } else {
        // Verify current password
        $user_id = $_SESSION['user_id'];
        $query = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (password_verify($current_password, $user['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to update password';
            }
        } else {
            $error = 'Current password is incorrect';
        }
    }
}

// Get user data for header
$user_query = "SELECT first_name, last_name, vip_level FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$user_name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
$vip_level = $user['vip_level'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | Coom Marketing Wallet</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #1e2430;
            color: white;
            max-width: 480px;
            margin: 0 auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #1e2430;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-size: 18px;
            font-weight: bold;
        }
        
        .logo-icon {
            width: 24px;
            height: 24px;
            background-color: #f3b71b;
            border-radius: 4px;
            margin-right: 8px;
        }
        
        .back-btn {
            color: white;
            text-decoration: none;
            font-size: 24px;
            margin-right: 10px;
        }
        
        .content-container {
            padding: 20px;
        }
        
        .password-form {
            max-width: 400px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #ccc;
            font-size: 14px;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #2a3547;
            background-color: #2a3547;
            color: white;
            font-size: 16px;
        }
        
        .submit-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 10px;
            font-weight: bold;
        }
        
        .submit-btn:hover {
            background-color: #45a049;
        }
        
        .error-message {
            color: #ff6b6b;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .success-message {
            color: #4CAF50;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="myaccount.php" class="back-btn">‹</a>
        <div class="logo">
            <div class="logo-icon"></div>
            <div>Change Password</div>
        </div>
        <div style="width: 24px;"></div> <!-- Spacer for balance -->
    </div>
    
    <div class="content-container">
        <div class="password-form">
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <form action="change_password.php" method="post">
                <div class="form-group">
                    <label for="current_password">Old Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Reenter new password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                </div>
                
                <button type="submit" class="submit-btn">Confirm</button>
            </form>
        </div>
    </div>
</body>
</html>