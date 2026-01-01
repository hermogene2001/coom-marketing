<?php if (session_status() === PHP_SESSION_NONE) session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phone Login - COOM MARKETING</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #121212;
            color: #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .login-container {
            background-color: #1e1e1e;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            padding: 30px;
            width: 100%;
            max-width: 400px;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .login-header h1 {
            font-size: 24px;
            color: #ffeb3b;
            margin-bottom: 8px;
        }
        
        .form-group {
            margin-bottom: 18px;
        }
        
        label {
            display: block;
            margin-bottom: 6px;
            color: #bbbbbb;
            font-size: 14px;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #333;
            background-color: #252525;
            color: #ffffff;
            border-radius: 4px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #ffeb3b;
            box-shadow: 0 0 0 2px rgba(255, 235, 59, 0.3);
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .btn-primary {
            flex: 1;
            padding: 13px;
            background-color: #ffeb3b;
            color: #121212;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
        }
        
        .btn-secondary {
            flex: 1;
            padding: 13px;
            background-color: #333;
            color: #ffffff;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            text-decoration: none;
        }
        
        .btn-primary:hover {
            background-color: #ffd600;
        }
        
        .btn-secondary:hover {
            background-color: #444;
        }
        
        .links-container {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }
        
        .links-container a {
            color: #bbbbbb;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.2s;
        }
        
        .links-container a:hover {
            color: #ffeb3b;
        }
        
        .error-message {
            color: #ff5252;
            margin-bottom: 15px;
            text-align: center;
            font-size: 13px;
            padding: 10px;
            background-color: rgba(255, 82, 82, 0.1);
            border-radius: 4px;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        
        .hint-text {
            font-size: 12px;
            color: #777;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Welcome Back</h1>
            <p>Sign in to your account</p>
        </div>
        
        <?php 
        // if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (isset($_SESSION['login_error'])) : ?>
            <div class="error-message">
                <?php echo htmlspecialchars($_SESSION['login_error']); ?>
                <?php unset($_SESSION['login_error']); ?>
            </div>
        <?php endif; ?>
        
        <form action="auth/login.php" method="post" autocomplete="off">
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" 
                       placeholder="Enter your full phone number with country code (e.g. +1234567890)" required
                       pattern="\+[1-9]\d{1,14}" 
                       title="Enter phone number with country code (e.g. +1234567890)">
                <p class="hint-text">Enter your full phone number with country code (e.g. +1234567890)</p>
            </div>
            
            <div class="form-group">
                <label for="phone-password">Password</label>
                <input type="password" id="phone-password" name="phone-password" 
                       placeholder="Enter your password" required
                       minlength="6">
            </div>
            
            <div class="button-group">
                <button type="submit" class="btn-primary">Sign In</button>
                <a href="signup.php" class="btn-secondary">Sign Up</a>
            </div>
            
            <div class="links-container">
                <a href="forgot-password.php">Forgot password?</a>
                <a href="help.php">Need help?</a>
            </div>
        </form>
    </div>

    <script>
        // Auto-format country code input
        document.getElementById('country-code').addEventListener('input', function(e) {
            this.value = '+' + this.value.replace(/[^0-9+]/g, '').replace(/^\+/, '');
            if (this.value.length > 5) this.value = this.value.substring(0, 5);
        });
        
        // Format phone number input
        document.getElementById('phone').addEventListener('input', function(e) {
            this.value = '+' + this.value.replace(/[^0-9+]/g, '').replace(/^\+/, '');
        });
        
        // Clean URL if it has tab parameters
        if (window.location.search.includes('tab=')) {
            history.replaceState({}, '', window.location.pathname);
        }
    </script>
</body>
</html>