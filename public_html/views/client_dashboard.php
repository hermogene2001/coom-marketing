<?php
session_start();

// Check if the user is logged in and has a 'client' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

// Get the logged-in user's ID
$clientId = $_SESSION['user_id'];

include '../includes/db.php';

// Fetch user balance using prepared statement
$stmt = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->bind_param("i", $clientId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_balance = $user['balance'];

// Check for messages in session
$success_msg = isset($_SESSION['success_msg']) ? $_SESSION['success_msg'] : '';
$error_msg = isset($_SESSION['error_msg']) ? $_SESSION['error_msg'] : '';
unset($_SESSION['success_msg']); // Clear the message after displaying
unset($_SESSION['error_msg']); // Clear the message after displaying
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investment Products - Harbor Investment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
            --card-border: #334155;
            --text-color: #e2e8f0;
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --success-color: #10b981;
            --accent-color: #38bdf8;
            --nav-bg: #1a2a3a;
            --error-color: #ef4444;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-bottom: 80px;
        }
        
        .container {
            padding-top: 2rem;
        }
        
        .page-title {
            color: var(--accent-color);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .product-card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            border-color: var(--primary-color);
        }
        
        .product-info {
            display: flex;
            align-items: center;
        }
        
        .product-info img {
            width: 80px;
            height: 80px;
            margin-right: 20px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid var(--card-border);
        }
        
        .product-details h5 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--accent-color);
        }
        
        .product-details p {
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
            opacity: 0.9;
        }
        
        .price-tag {
            color: var(--success-color);
            font-weight: 600;
        }
        
        .buy-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 25px;
            font-weight: 500;
            text-transform: uppercase;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .buy-btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
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
        
        .nav-btn:hover {
            color: var(--accent-color);
        }
        
        .nav-btn i {
            display: block;
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }
        
        /* Message Alert Styles */
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            width: 350px;
        }
        
        .alert {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: none;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            color: white;
        }
        
        .alert.show {
            opacity: 1;
        }
        
        .alert-success {
            background-color: var(--success-color);
        }
        
        .alert-danger {
            background-color: var(--error-color);
        }
        
        .close-btn {
            color: white;
            opacity: 0.8;
            background: none;
            border: none;
            font-size: 1.25rem;
            line-height: 1;
        }
        
        .close-btn:hover {
            opacity: 1;
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

    <!-- Message Display Container -->
    <div class="alert-container">
        <?php if ($success_msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-2"></i>
                    <div><?php echo htmlspecialchars($success_msg); ?></div>
                    <button type="button" class="close-btn ms-auto" aria-label="Close">&times;</button>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if ($error_msg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div><?php echo htmlspecialchars($error_msg); ?></div>
                    <button type="button" class="close-btn ms-auto" aria-label="Close">&times;</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
<?php include 'about.php'; ?>
    <div class="container">
        <h2 class="page-title"><i class="fas fa-box-open me-2"></i>Investment Products</h2>

        <?php
        // Fetch available products using prepared statement
        $stmt = $conn->prepare("SELECT id, name, daily_earning, cycle, price, image FROM products WHERE status = 'active'");
        $stmt->execute();
        $product_result = $stmt->get_result();
        
        while ($product = $product_result->fetch_assoc()):
            $total_income = ($product['daily_earning'] * $product['cycle']) + $product['price'];
            $image_path = !empty($product['image']) ? "../uploads/".htmlspecialchars($product['image']) : "../assets/default-product.png";
        ?>
            <div class="product-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="product-info">
                            <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="product-details">
                                <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p><i class="fas fa-coins me-2"></i>Daily earnings: <span class="price-tag"><?php echo number_format($product['daily_earning'], 2); ?> RWF</span></p>
                                <p><i class="fas fa-calendar-alt me-2"></i>Cycle: <strong><?php echo htmlspecialchars($product['cycle']); ?> days</strong></p>
                                <p><i class="fas fa-tag me-2"></i>Price: <span class="price-tag"><?php echo number_format($product['price'], 2); ?> RWF</span></p>
                                <p><i class="fas fa-chart-line me-2"></i>Projected return: <span class="price-tag"><?php echo number_format($total_income, 2); ?> RWF</span></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <button class="buy-btn" onclick="buyProduct(<?php echo (int)$product['id']; ?>, <?php echo (float)$product['price']; ?>)">
                            <i class="fas fa-shopping-cart me-2"></i>Invest Now
                        </button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        
        <?php if ($product_result->num_rows === 0): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-2"></i> No investment products available at the moment.
            </div>
        <?php endif; ?>
    </div>

    <!-- Fixed Bottom Navbar -->
    <nav class="navbar navbar-bottom fixed-bottom">
        <div class="container-fluid">
            <div class="row w-100">
                <div class="col-3">
                    <a href="client_dashboard.php" class="nav-btn">
                        <i class="fas fa-box"></i>
                        <span>Products</span>
                    </a>
                </div>
                <div class="col-3">
                    <a href="purchased.php" class="nav-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span>My Investments</span>
                    </a>
                </div>
                <div class="col-3">
                    <a href="invite.php" class="nav-btn">
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Show alerts with animation
            $('.alert').addClass('show');
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').removeClass('show');
                setTimeout(function() {
                    $('.alert').remove();
                }, 500);
            }, 5000);
            
            // Manual close button
            $('.close-btn').click(function() {
                $(this).closest('.alert').removeClass('show');
                setTimeout(function() {
                    $(this).closest('.alert').remove();
                }.bind(this), 500);
            });
            
            // AJAX call to process daily earnings
            $.ajax({
                url: 'credit_daily_earnings.php',
                type: 'GET',
                success: function(response) {
                    console.log("Daily earnings processed successfully.");
                },
                error: function(xhr, status, error) {
                    console.log("Error processing daily earnings: " + error);
                }
            });
        });
        
        function buyProduct(productId, price) {
            const userBalance = <?php echo (float)$user_balance; ?>;
            if (userBalance >= price) {
                if (confirm("Are you sure you want to invest in this product?")) {
                    window.location.href = "../actions/buy_product.php?product_id=" + productId;
                }
            } else {
                alert("Insufficient balance. Please recharge your account to continue.");
                window.location.href = "recharge.php";
            }
        }
    </script>
</body>
</html>