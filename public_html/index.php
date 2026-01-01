<?php
session_start();

// Redirect if the user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit();
    } elseif ($_SESSION['role'] === 'agent') {
        header("Location: views/agent_dashboard.php");
        exit();
    } else {
        header("Location: views/client_dashboard.php");
        exit();
    }
}

// Process login form submission (handle POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include('includes/db.php'); // Include your database connection here
    
    $phone = $_POST['phone_number'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE phone_number = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$phone]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Check if user is active
        if (isset($user['active']) && $user['active'] != 1) {
            $error_message = "Your account has been deactivated. Please contact support for assistance.";
        } else {
            // Set session for logged-in user
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['phone_number'] = $user['phone_number'];
            $_SESSION['referral_code'] = $user['referral_code'];
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } elseif ($user['role'] === 'agent') {
                header("Location: views/agent_dashboard.php");
            } else {
                header("Location: views/client_dashboard.php");
            }
            exit();
        }
    } else {
        $error_message = "Invalid phone number or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Harbor Investment</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a2a3a, #0d1b2a);
            color: #e0e0e0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            margin-top: 140px;
            background-color: #1e293b;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 100%;
            text-align: center;
            border: 1px solid #2d3748;
        }
        .form-control-icon {
            position: relative;
        }
        .form-control-icon i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #3b82f6;
        }
        .form-control {
            padding-left: 35px;
            border-radius: 8px;
            background-color: #2d3748;
            border: 1px solid #4a5568;
            color: #e0e0e0;
        }
        .form-control:focus {
            background-color: #2d3748;
            color: #e0e0e0;
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        .btn-primary {
            background-color: #3b82f6;
            border-color: #3b82f6;
            border-radius: 8px;
            font-weight: 500;
        }
        .btn-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        .text-link {
            color: #3b82f6;
            text-decoration: none;
        }
        .text-link:hover {
            color: #60a5fa;
            text-decoration: underline;
        }
        .info-text {
            font-size: 0.9rem;
            color: #9ca3af;
        }
        .text-muted {
            color: #9ca3af !important;
        }
        h3 {
            color: #f8fafc;
        }
        .alert-danger {
            background-color: #7f1d1d;
            color: #fecaca;
            border-color: #7f1d1d;
        }
        hr {
            border-color: #4a5568;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h3 class="mb-3">Welcome to Harbor Investment</h3>
    <p class="text-muted">Your safe harbor for financial growth.</p>
    <p class="text-muted mb-4">Sign in to access your dashboard.</p>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form action="index.php" method="POST">
        <div class="mb-3 form-control-icon">
            <i class="fas fa-phone"></i>
            <input type="text" class="form-control" name="phone_number" placeholder="Phone Number" required>
        </div>
        <div class="mb-3 form-control-icon">
            <i class="fas fa-lock"></i>
            <input type="password" class="form-control" name="password" placeholder="Password" minlength="6" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <p class="text-center mt-3">
        <a href="#" class="text-link">Forgot Password?</a> | 
        <a href="views/register.php" class="text-link">Register Here</a>
    </p>

    <hr>
    <div class="info-text">
        <p>Grow your wealth securely in our harbor. Our investment platform offers daily profit cycles and professional management.</p>
        <p>Anchor your finances with us and sail toward financial freedom.</p>
    </div>
</div>

</body>
</html>