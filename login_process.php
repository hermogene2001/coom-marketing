<?php
session_start();
include 'includes/db_connection.php';

$error_message = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_type = isset($_POST['login_type']) ? $_POST['login_type'] : 'email';
    
    if ($login_type === 'email') {
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        if (empty($email) || empty($password)) {
            $error_message = 'Please enter both email and password.';
        } else {
            // Authenticate user
            $sql = "SELECT id, email, password, username, balance, vip_level FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verify password (using password_verify if you're using password_hash)
                if (password_verify($password, $user['password'])) {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['balance'] = $user['balance'];
                    $_SESSION['vip_level'] = $user['vip_level'];
                    
                    // Redirect to dashboard
                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error_message = 'Invalid password.';
                }
            } else {
                $error_message = 'User not found.';
            }
        }
    } else if ($login_type === 'phone') {
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Phone login logic similar to email login
        // ...
    } else if ($login_type === 'telegram') {
        $telegram_id = isset($_POST['telegram_id']) ? trim($_POST['telegram_id']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Telegram login logic
        // ...
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Netto - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a1d24;
            --secondary-color: #222831;
            --accent-color: #ffd700;
            --text-color: #ffffff;
            --input-bg: #897956;
            --error-color: #e74c3c;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: var(--primary-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }
        
        .language-selector {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            align-items: center;
            color: var(--text-color);
            font-size: 14px;
        }
        
        .language-selector i {
            margin-right: 5px;
            color: var(--accent-color);
        }
        
        .logo-container {
            margin-top: 40px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            background-color: var(--accent-color);
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: bold;
            margin-top: 10px;
            color: var(--accent-color);
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
        }
        
        .tab-container {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .tab {
            flex: 1;
            text-align: center;
            padding: 15px 0;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.6);
            position: relative;
            transition: color 0.3s;
        }
        
        .tab.active {
            color: var(--text-color);
            font-weight: bold;
        }
        
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background-color: var(--accent-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 10px;
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
        }
        
        .form-input {
            width: 100%;
            padding: 15px;
            border-radius: 5px;
            border: none;
            background-color: var(--input-bg);
            color: var(--text-color);
            font-size: 16px;
            position: relative;
        }
        
        .input-icon {
            position: relative;
        }
        
        .input-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
        }
        
        .input-icon input {
            padding-left: 45px;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            cursor: pointer;
        }
        
        .signin-btn {
            width: 100%;
            padding: 15px;
            border-radius: 30px;
            border: none;
            background-color: var(--accent-color);
            color: #000;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 15px;
        }
        
        .signup-btn {
            width: 100%;
            padding: 15px;
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background-color: transparent;
            color: var(--text-color);
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .background-image {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 40%;
            max-width: 300px;
            opacity: 0.4;
            z-index: -1;
        }
        
        .error-message {
            background-color: rgba(231, 76, 60, 0.2);
            color: var(--error-color);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="language-selector">
        <i class="fas fa-globe"></i>
        English
    </div>
    
    <div class="logo-container">
        <div class="logo">
            <img src="images/netto-logo.png" alt="Netto" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MCIgaGVpZ2h0PSI4MCIgdmlld0JveD0iMCAwIDgwIDgwIj48dGV4dCB4PSI0MCIgeT0iNDUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIyMCIgZmlsbD0icmVkIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5OZXR0bzwvdGV4dD48L3N2Zz4=';" style="max-width: 100%; max-height: 100%;">
        </div>
        <div class="logo-text">Netto</div>
    </div>
    
    <div class="login-container">
        <?php if (!empty($error_message)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>
        
        <div class="tab-container">
            <div class="tab <?php echo (!isset($_POST['login_type']) || $_POST['login_type'] === 'email') ? 'active' : ''; ?>" id="email-tab">Email Login</div>
            <div class="tab <?php echo (isset($_POST['login_type']) && $_POST['login_type'] === 'phone') ? 'active' : ''; ?>" id="phone-tab">Phone Login</div>
            <div class="tab <?php echo (isset($_POST['login_type']) && $_POST['login_type'] === 'telegram') ? 'active' : ''; ?>" id="telegram-tab">Telegram Login</div>
        </div>
        
        <form id="login-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
            <input type="hidden" name="login_type" id="login_type" value="email">
            
            <div id="email-field" class="form-group">
                <label class="form-label">E-mail</label>
                <div class="input-icon">
                    <i class="far fa-envelope"></i>
                    <input type="email" class="form-input" name="email" placeholder="E-mail" required>
                </div>
            </div>
            
            <div id="phone-field" class="form-group" style="display: none;">
                <label class="form-label">Phone</label>
                <div class="input-icon">
                    <i class="fas fa-phone"></i>
                    <input type="tel" class="form-input" name="phone" placeholder="Phone Number">
                </div>
            </div>
            
            <div id="telegram-field" class="form-group" style="display: none;">
                <label class="form-label">Telegram ID</label>
                <div class="input-icon">
                    <i class="fab fa-telegram-plane"></i>
                    <input type="text" class="form-input" name="telegram_id" placeholder="Telegram ID">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-icon">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-input" name="password" id="password" placeholder="Password" required>
                    <span class="password-toggle" id="password-toggle">
                        <i class="far fa-eye-slash"></i>
                    </span>
                </div>
            </div>
            
            <button type="submit" class="signin-btn">Sign In</button>
            
            <button type="button" class="signup-btn" id="signup-btn">Sign Up</button>
        </form>
    </div>
    
    <img src="images/login-background.png" alt="" class="background-image" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMDAiIGhlaWdodD0iMzAwIiB2aWV3Qm94PSIwIDAgMzAwIDMwMCI+PHBhdGggZD0iTTEwMCwxMDBMMjAwLDIwMEwxNTAsMjUwTDUwLDE1MFoiIGZpbGw9IiM5OTk5OTkiIGZpbGwtb3BhY2l0eT0iMC4zIi8+PC9zdmc+';">
    
    <script>
        // Tab switching functionality
        const tabs = document.querySelectorAll('.tab');
        const emailTab = document.getElementById('email-tab');
        const phoneTab = document.getElementById('phone-tab');
        const telegramTab = document.getElementById('telegram-tab');
        
        const emailField = document.getElementById('email-field');
        const phoneField = document.getElementById('phone-field');
        const telegramField = document.getElementById('telegram-field');
        const loginTypeInput = document.getElementById('login_type');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Update active tab
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                
                // Show appropriate input field and update login type
                if (tab.id === 'email-tab') {
                    emailField.style.display = 'block';
                    phoneField.style.display = 'none';
                    telegramField.style.display = 'none';
                    loginTypeInput.value = 'email';
                } else if (tab.id === 'phone-tab') {
                    emailField.style.display = 'none';
                    phoneField.style.display = 'block';
                    telegramField.style.display = 'none';
                    loginTypeInput.value = 'phone';
                } else if (tab.id === 'telegram-tab') {
                    emailField.style.display = 'none';
                    phoneField.style.display = 'none';
                    telegramField.style.display = 'block';
                    loginTypeInput.value = 'telegram';
                }
            });
        });
        
        // Password visibility toggle
        const passwordToggle = document.getElementById('password-toggle');
        const passwordInput = document.getElementById('password');
        
        passwordToggle.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle eye icon
            const eyeIcon = passwordToggle.querySelector('i');
            eyeIcon.className = type === 'password' ? 'far fa-eye-slash' : 'far fa-eye';
        });
        
        // Sign up button event
        document.getElementById('signup-btn').addEventListener('click', function() {
            window.location.href = 'register.php';
        });
    </script>
</body>
</html>