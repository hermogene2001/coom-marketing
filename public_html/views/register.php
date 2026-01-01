<?php
// Pre-fill the invitation code from the URL if available
$referralCode = isset($_GET['referral_code']) ? $_GET['referral_code'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harbor Investment - Register</title>
    <link rel="stylesheet" href="../assets/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a2a3a, #0d1b2a);
            color: #e0e0e0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .card {
            max-width: 500px;
            width: 100%;
            margin: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
            border: 1px solid #2d3748;
            border-radius: 10px;
            overflow: hidden;
        }
        .card-header {
            background-color: #1e293b;
            color: #f8fafc;
            padding: 1.5rem;
            border-bottom: 1px solid #2d3748;
            text-align: center;
        }
        .card-body {
            padding: 2rem;
            background-color: #1e293b;
        }
        .form-control {
            background-color: #2d3748;
            border: 1px solid #4a5568;
            color: #e0e0e0;
            padding: 0.75rem;
            border-radius: 8px;
        }
        .form-control:focus {
            background-color: #2d3748;
            color: #e0e0e0;
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        .form-label {
            color: #e0e0e0;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .btn-primary {
            background-color: #3b82f6;
            border-color: #3b82f6;
            border-radius: 8px;
            font-weight: 500;
            padding: 0.75rem;
            margin-top: 1rem;
        }
        .btn-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        .back-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover {
            color: #60a5fa;
            text-decoration: underline;
        }
        .already-account {
            text-align: center;
            margin-top: 1.5rem;
            color: #9ca3af;
        }
        #phone:invalid {
            border-color: #dc3545;
        }
        #phone:valid {
            border-color: #28a745;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h3>Register with Harbor Investment</h3>
        </div>
        <div class="card-body">
            <form action="../actions/register.php" method="POST">
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone_number" 
                           pattern="07[8|2|3|9]\d{7}" 
                           title="Phone number should start with 07 and be 10 digits long" 
                           required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           minlength="6" required>
                </div>
                <div class="mb-3">
                    <label for="invitationCode" class="form-label">Invitation Code</label>
                    <input type="text" class="form-control" id="invitationCode" 
                           name="invitation_code" required 
                           value="<?php echo htmlspecialchars($referralCode); ?>" readonly>
                </div>
                <button type="submit" class="btn btn-primary w-100">Create Account</button>
            </form>
            <div class="already-account">
                Already have an account? <a href="../index.php" class="back-link">Sign In</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (CDN via jsDelivr) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add real-time validation for phone number
        document.getElementById('phone').addEventListener('input', function() {
            this.setCustomValidity('');
            if (!this.checkValidity()) {
                this.setCustomValidity('Phone number should start with 07 and be 10 digits long');
            }
        });
    </script>
</body>
</html>