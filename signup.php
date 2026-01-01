<?php if (session_status() === PHP_SESSION_NONE) session_start();?>
<?php
// Pre-fill the invitation code from the URL if available
$referralCode = isset($_GET['referral_code']) ? $_GET['referral_code'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - COOM MARKETING</title>
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
        
        .signup-container {
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
        
        .signup-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .signup-header h1 {
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
        
        .btn-primary {
            width: 100%;
            padding: 13px;
            background-color: #ffeb3b;
            color: #121212;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-primary:hover {
            background-color: #ffd600;
        }
        
        .login-link {
            margin-top: 15px;
            text-align: center;
        }
        
        .login-link a {
            color: #bbbbbb;
            text-decoration: none;
            font-size: 13px;
            transition: color 0.2s;
        }
        
        .login-link a:hover {
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
        
        .password-strength {
            height: 4px;
            background: #333;
            margin-top: 8px;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s, background 0.3s;
        }
        
        .phone-input-group {
            display: flex;
            gap: 10px;
        }
        
        .country-code {
            width: 80px;
        }
        
        .phone-number {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-header">
            <h1>Create Account</h1>
            <p>Join COOM MARKETING today</p>
        </div>
        
        <?php 
        // if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (isset($_SESSION['error'])) : ?>
            <div class="error-message">
                <?php echo htmlspecialchars($_SESSION['error']); ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <form action="auth/register.php" method="post" autocomplete="off">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" 
                       placeholder="your@email.com" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone_number" 
                       placeholder="Enter your full phone number with country code (e.g. +1234567890)" required
                       pattern="\+[1-9]\d{1,14}" 
                       title="Enter phone number with country code (e.g. +1234567890)">
                <p class="hint-text">Enter your full phone number with country code (e.g. +1234567890)</p>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" 
                       placeholder="Create password" required
                       minlength="8"
                       oninput="checkPasswordStrength(this.value)">
                <div class="password-strength">
                    <div class="strength-meter" id="strength-meter"></div>
                </div>
                <p class="hint-text">Minimum 8 characters with numbers</p>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" 
                       placeholder="Confirm your password" required
                       minlength="8">
            </div>
            
            <div class="form-group">
                <label for="invitation">Invitation Code (Optional)</label>
                <input type="text" id="invitation" name="invitation" 
                       placeholder="Enter invitation code if any" 
                       value="<?php echo htmlspecialchars($referralCode); ?>" readonly>
                <p class="hint-text">Leave blank if you don't have one</p>
            </div>
            
            <button type="submit" class="btn-primary">Create Account</button>
            
            <div class="login-link">
                Already have an account? <a href="index.php">Sign In</a>
            </div>
        </form>
    </div>

    <script>
        // Auto-format country code input
        document.querySelector('input[name="country-code"]').addEventListener('input', function(e) {
            this.value = '+' + this.value.replace(/[^0-9+]/g, '').replace(/^\+/, '');
            if (this.value.length > 5) this.value = this.value.substring(0, 5);
        });
        
        // Format phone number input
        document.getElementById('phone').addEventListener('input', function(e) {
            this.value = '+' + this.value.replace(/[^0-9+]/g, '').replace(/^\+/, '');
        });
        
        // Password strength indicator
        function checkPasswordStrength(password) {
            const meter = document.getElementById('strength-meter');
            let strength = 0;
            
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;
            if (/[A-Z]/.test(password)) strength += 1;
            if (/[0-9]/.test(password)) strength += 1;
            if (/[^A-Za-z0-9]/.test(password)) strength += 1;
            
            const width = (strength / 5) * 100;
            meter.style.width = width + '%';
            
            if (strength <= 2) {
                meter.style.backgroundColor = '#ff5252';
            } else if (strength <= 4) {
                meter.style.backgroundColor = '#ffab40';
            } else {
                meter.style.backgroundColor = '#69f0ae';
            }
        }
        
        // Confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            if (password !== this.value && this.value.length > 0) {
                this.setCustomValidity("Passwords don't match");
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>