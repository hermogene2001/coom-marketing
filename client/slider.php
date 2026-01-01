<?php
session_start();
include '../includes/db_connection.php';
include 'nav.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get active products
$products_query = "SELECT * FROM products WHERE status = 'active' ORDER BY price ASC";
$products_result = $conn->query($products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COOM-MARKETING - Investment Products</title>
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
        
        .user-balance {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
            border: 1px solid var(--border-color);
        }
        
        .balance-label {
            color: var(--text-secondary);
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .balance-amount {
            font-size: 32px;
            font-weight: bold;
            color: var(--accent-color-light);
        }
        
        .products-slider {
            overflow-x: auto;
            white-space: nowrap;
            padding: 10px 0 30px 0;
            margin-bottom: 30px;
        }
        
        .product-cards {
            display: inline-flex;
            gap: 20px;
            padding: 10px;
        }
        
        .product-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            width: 300px;
            display: inline-block;
            vertical-align: top;
            border: 1px solid var(--border-color);
        }
        
        .product-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: var(--accent-color-light);
        }
        
        .product-price {
            font-size: 24px;
            font-weight: bold;
            color: var(--text-color);
            margin-bottom: 15px;
        }
        
        .product-features {
            margin-bottom: 20px;
        }
        
        .feature-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .feature-label {
            color: var(--text-secondary);
        }
        
        .feature-value {
            font-weight: 500;
        }
        
        .purchase-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .purchase-btn:hover {
            background-color: var(--accent-color-light);
        }
        
        .purchase-btn:disabled {
            background-color: var(--text-secondary);
            cursor: not-allowed;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .no-products {
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
        }
        
        @media (max-width: 768px) {
            .product-cards {
                flex-direction: column;
            }
            
            .product-card {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-chart-line"></i> Investment Products</h1>
            <p>Choose from our premium investment packages</p>
        </div>
        
        <div class="user-balance">
            <div class="balance-label">Your Balance</div>
            <div class="balance-amount">RWF <?php echo number_format($user['balance'], 2); ?></div>
        </div>
        
        <div class="products-slider">
            <div class="product-cards">
                <?php if ($products_result->num_rows > 0): ?>
                    <?php while ($product = $products_result->fetch_assoc()): ?>
                        <div class="product-card">
                            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-price">RWF <?php echo number_format($product['price'], 2); ?></div>
                            <div class="product-features">
                                <div class="feature-item">
                                    <span class="feature-label">Daily Earning:</span>
                                    <span class="feature-value">RWF <?php echo number_format($product['daily_earning'], 2); ?></span>
                                </div>
                                <div class="feature-item">
                                    <span class="feature-label">Profit Rate:</span>
                                    <span class="feature-value"><?php echo $product['profit_rate']; ?>%</span>
                                </div>
                                <div class="feature-item">
                                    <span class="feature-label">Cycle:</span>
                                    <span class="feature-value"><?php echo $product['cycle']; ?> days</span>
                                </div>
                                <div class="feature-item">
                                    <span class="feature-label">Total Profit:</span>
                                    <span class="feature-value">RWF <?php echo number_format($product['daily_earning'] * $product['cycle'], 2); ?></span>
                                </div>
                            </div>
                            <form action="products.php" method="post" style="display: inline;">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" name="purchase_product" class="purchase-btn" <?php echo ($user['balance'] < $product['price']) ? 'disabled' : ''; ?>>
                                    <?php echo ($user['balance'] < $product['price']) ? 'Insufficient Funds' : 'Invest Now'; ?>
                                </button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-products">
                        <i class="fas fa-box-open" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <h3>No investment products available</h3>
                        <p>Please check back later for new investment opportunities</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>