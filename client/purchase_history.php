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

// Fetch user's product purchases
$sql = "SELECT 
            up.id, 
            p.name as product_name, 
            p.daily_earning,
            p.image,
            up.purchase_date, 
            up.end_date, 
            up.status,
            p.price
        FROM user_products up
        JOIN products p ON up.product_id = p.id
        WHERE up.user_id = ?
        ORDER BY up.purchase_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$purchases = array();
while ($row = $result->fetch_assoc()) {
    $purchases[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coom Marketing - Purchase History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a1d24;
            --secondary-color: #222831;
            --accent-color: #ffd700;
            --text-color: #ffffff;
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
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 15px;
            margin-bottom: 100px;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            margin-bottom: 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
        }
        
        .logo i {
            color: var(--accent-color);
            margin-right: 10px;
        }
        
        .back-button {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 5px 10px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 20px;
            font-size: 20px;
        }
        
        .purchase-item {
            background-color: var(--secondary-color);
            border-radius: 15px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .purchase-image {
            width: 60px;
            height: 60px;
            background-color: #ffde00;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            margin-right: 15px;
            overflow: hidden;
        }
        
        .purchase-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .purchase-details {
            flex-grow: 1;
        }
        
        .purchase-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .purchase-info {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: #ccc;
            margin-bottom: 3px;
        }
        
        .purchase-status {
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
            text-align: center;
            width: fit-content;
            margin-top: 5px;
        }
        
        .status-active {
            background-color: rgba(95, 211, 138, 0.2);
            color: #5fd38a;
        }
        
        .status-expired {
            background-color: rgba(233, 90, 137, 0.2);
            color: #e95a89;
        }
        
        .no-purchases {
            text-align: center;
            padding: 30px;
            color: #ccc;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <i class="fas fa-cubes"></i>
                Coom Marketing
            </div>
            <div class="back-button">
                <i class="fas fa-arrow-left"></i>
                Back
            </div>
        </header>
        
        <div class="page-title">Purchase History</div>
        
        <?php if (empty($purchases)): ?>
        <div class="no-purchases">
            <i class="fas fa-shopping-cart" style="font-size: 40px; margin-bottom: 15px; display: block;"></i>
            <p>You haven't made any purchases yet.</p>
        </div>
        <?php else: ?>
            <?php foreach ($purchases as $purchase): ?>
            <div class="purchase-item">
                <div class="purchase-image">
                    <img src="../uploads/<?php echo $purchase['image']; ?>" alt="<?php echo $purchase['product_name']; ?>">
                </div>
                <div class="purchase-details">
                    <div class="purchase-name"><?php echo $purchase['product_name']; ?></div>
                    <div class="purchase-info">
                        <div>Daily earning:</div>
                        <div><?php echo number_format($purchase['daily_earning'], 2); ?> RWF</div>
                    </div>
                    <div class="purchase-info">
                        <div>Purchase date:</div>
                        <div><?php echo date('Y-m-d', strtotime($purchase['purchase_date'])); ?></div>
                    </div>
                    <div class="purchase-info">
                        <div>End date:</div>
                        <div><?php echo date('Y-m-d', strtotime($purchase['end_date'])); ?></div>
                    </div>
                    <div class="purchase-info">
                        <div>Purchase amount:</div>
                        <div><?php echo number_format($purchase['price'], 2); ?> RWF</div>
                    </div>
                    <div class="purchase-status status-<?php echo strtolower($purchase['status']); ?>">
                        <?php echo ucfirst($purchase['status']); ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <script>
        document.querySelector('.back-button').addEventListener('click', function() {
            window.location.href = 'products.php';
        });

        // For demo purposes only - replace with your image path
        const productImages = document.querySelectorAll('.purchase-image img');
        productImages.forEach(img => {
            img.onerror = function() {
                this.src = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MCIgaGVpZ2h0PSI4MCIgdmlld0JveD0iMCAwIDgwIDgwIj48dGV4dCB4PSI0MCIgeT0iNDUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIyMCIgZmlsbD0icmVkIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5OZXR0bzwvdGV4dD48L3N2Zz4=';
            };
        });
    </script>
</body>
</html>