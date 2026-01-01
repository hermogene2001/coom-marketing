<?php
session_start();
include '../includes/db_connection.php';
include 'nav.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's balance
$sql = "SELECT balance FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

// Fetch product information from database
$sql = "SELECT 
            id, 
            name, 
            image, 
            daily_earning, 
            cycle, 
            price, 
            profit_rate,
            min_withdraw,
            referral_level1_percentage,
            referral_level2_percentage,
            status
        FROM products 
        WHERE status = 'active'
        ORDER BY price ASC";
$result = $conn->query($sql);

$products = array();
while ($row = $result->fetch_assoc()) {
    $products[$row['id']] = $row;
}

// Process product purchase request
$purchase_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['purchase_product'])) {
    $product_id = $_POST['product_id'];
    
    // Check if requested product exists
    if (!isset($products[$product_id])) {
        $purchase_message = "Invalid product selected.";
    } else {
        $product_price = $products[$product_id]['price'];
        
        if ($user_data['balance'] >= $product_price) {
            // Start transaction
            $conn->begin_transaction();
            try {
                // Deduct balance
                $new_balance = $user_data['balance'] - $product_price;
                $sql = "UPDATE users SET balance = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("di", $new_balance, $user_id);
                $stmt->execute();
                
                // Record transaction
                $sql = "INSERT INTO transactions (user_id, type, amount, created_at, description) 
                        VALUES (?, 'product_purchase', ?, NOW(), ?)";
                $description = "Purchase of " . $products[$product_id]['name'];
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ids", $user_id, $product_price, $description);
                $stmt->execute();
                
                // Record user's product purchase
                $sql = "INSERT INTO user_products (user_id, product_id, purchase_date, end_date, status) 
                        VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), 'active')";
                $cycle_days = $products[$product_id]['cycle'];
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iii", $user_id, $product_id, $cycle_days);
                $stmt->execute();
                
                $conn->commit();
                $purchase_message = "Successfully purchased " . $products[$product_id]['name'] . "!";
            } catch (Exception $e) {
                $conn->rollback();
                $purchase_message = "Error processing purchase: " . $e->getMessage();
            }
        } else {
            $purchase_message = "Insufficient balance for purchase.";
        }
    }
}

// Calculate total profit for each product
foreach ($products as &$product) {
    $product['total_profit'] = $product['daily_earning'] * $product['cycle'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COOM-MARKETING - Investment Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
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
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: var(--primary-bg);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px;
            margin-bottom: 100px;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
            color: var(--accent-color-light);
        }
        
        .logo i {
            color: var(--accent-color-light);
            margin-right: 10px;
        }
        
        .language-selector {
            background-color: var(--card-bg);
            padding: 8px 12px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color);
        }
        
        .purchase-log {
            text-align: right;
            color: var(--text-secondary);
            margin-bottom: 15px;
            font-size: 14px;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .purchase-log:hover {
            color: var(--accent-color-light);
        }
        
        .product-card {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            overflow: visible;
            border: 1px solid var(--border-color);
        }
        
        .product-badge {
            display: flex;
            top: 0;
            left: 0;
            padding: 8px 15px;
            border-bottom-right-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            z-index: 2;
            background-color: var(--accent-color);
            color: white;
        }
        
        .product-content {
            display: flex;
            margin-top: 15px;
        }
        
        .product-image {
            width: 80px;
            height: 80px;
            background-color: var(--secondary-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            margin-right: 15px;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-details {
            flex-grow: 1;
        }
        
        .product-stats {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .product-stat {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }
        
        .product-stat-label {
            color: var(--text-secondary);
        }
        
        .product-stat-value {
            font-weight: bold;
            color: var(--text-color);
        }
        
        .highlight-value {
            color: var(--accent-color-light);
        }
        
        .RWF-value {
            color: var(--text-secondary);
            font-size: 12px;
            margin-left: 3px;
        }
        
        .purchase-button {
            display: inline-block;
            background-color: var(--accent-color);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
            border: none;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .purchase-button:hover {
            background-color: var(--accent-color-light);
        }
        
        .buy-now {
            color: white;
            margin-left: 5px;
            font-size: 12px;
        }
        
        .message {
            background-color: var(--card-bg);
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid var(--border-color);
        }
        
        .success {
            background-color: rgba(35, 165, 89, 0.2);
            color: var(--positive);
        }
        
        .error {
            background-color: rgba(227, 76, 38, 0.2);
            color: var(--negative);
        }
        
        .owned-product {
            border: 1px solid var(--accent-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <i class="fas fa-chart-line"></i>
                COOM-MARKETING
            </div>
            <div class="language-selector">
                <i class="fas fa-globe"></i>
                English
            </div>
        </header>
        
        <div class="purchase-log">Purchase history</div>
        
        <?php if (!empty($purchase_message)): ?>
        <div class="message <?php echo strpos($purchase_message, 'Successfully') !== false ? 'success' : 'error'; ?>">
            <?php echo $purchase_message; ?>
        </div>
        <?php endif; ?>
        
        <!-- Display user's current balance for reference -->
        <div class="message">
            Your current balance: <?php echo number_format($user_data['balance'], 2); ?> RWF
        </div>
        
        <?php foreach ($products as $product_id => $product): ?>
        <div class="product-card">
            <div class="product-badge">
                <?php echo $product['name']; ?>
            </div>
            <div class="product-content">
                <div class="product-image">
                    <img src="../uploads/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
                </div>
                <div class="product-details">
                    <div class="product-stats">
                        <div class="product-stat">
                            <div class="product-stat-label">Daily earning</div>
                            <div class="product-stat-value"><?php echo number_format($product['daily_earning'], 2); ?><span class="RWF-value">RWF</span></div>
                        </div>
                        <div class="product-stat">
                            <div class="product-stat-label">Profit rate</div>
                            <div class="product-stat-value highlight-value"><?php echo $product['profit_rate']; ?>%</div>
                        </div>
                        <div class="product-stat">
                            <div class="product-stat-label">Cycle (days)</div>
                            <div class="product-stat-value"><?php echo $product['cycle']; ?></div>
                        </div>
                        <div class="product-stat">
                            <div class="product-stat-label">Total profit</div>
                            <div class="product-stat-value"><?php echo number_format($product['total_profit'], 2); ?><span class="RWF-value">RWF</span></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div style="text-align: right; width: 100%; margin-top: 10px;">
                <form method="post" action="">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    <button type="submit" name="purchase_product" class="purchase-button">
                        <?php echo number_format($product['price'], 2); ?> RWF
                        <span class="buy-now">Buy now</span>
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <script>
        document.querySelector('.purchase-log').addEventListener('click', function() {
            window.location.href = 'purchase_history.php';
        });

        // For demo purposes only - replace with your image path
        const productImages = document.querySelectorAll('.product-image img');
        productImages.forEach(img => {
            img.onerror = function() {
                this.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MCIgaGVpZ2h0PSI4MCIgdmlld0JveD0iMCAwIDgwIDgwIj48dGV4dCB4PSI0MCIgeT0iNDUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIyMCIgZmlsbD0icmVkIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5OZXR0bzwvdGV4dD48L3N2Zz4=';
            };
        });
    </script>
</body>
</html>