<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Balance</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #1a202c;
            color: #e0e0e0;
            min-height: 100vh;
        }
        
        .header {
            background-color: #1e2330;
            padding: 16px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .back-button {
            color: #ffffff;
            font-size: 24px;
            cursor: pointer;
            background: none;
            border: none;
        }
        
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .balance-card {
            background-color: #2a3141;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .balance-info {
            flex: 1;
        }
        
        .account-label {
            color: #a0aec0;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .balance-amount {
            font-size: 20px;
            font-weight: bold;
            color: #f0b90b; /* USD currency color */
        }
        
        .currency {
            color: #f0b90b;
        }
        
        .balance-icon {
            max-width: 60px;
            margin-left: 15px;
        }
        
        .money-bag {
            width: 60px;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <button class="back-button" onclick="history.back()">←</button>
    </div>
    
    <div class="container">
        <?php
        include "../includes/db_connection.php";
        
        // Get user ID from session (you should implement proper authentication)
        $userId = $_SESSION['user_id'] ?? 1; // Default to 1 for testing
        
        // Query to get balances for this user
        $sql = "SELECT account_type, balance FROM user_balances WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Display balances
        while($row = $result->fetch_assoc()) {
            $accountType = $row['account_type'];
            $balance = number_format($row['balance'], 2);
            $displayName = ($accountType == 'basic') ? 'Basic account' : 'Withdrawal account';
            
            echo '
            <div class="balance-card">
                <div class="balance-info">
                    <div class="account-label">'.$displayName.'</div>
                    <div class="balance-amount">'.$balance.' <span class="currency">$</span></div>
                </div>
                <div class="money-bag">';
            
            // Different icon based on account type
            if($accountType == 'basic') {
                echo '<svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M30 5C25 5 20 10 20 15L40 15C40 10 35 5 30 5Z" fill="#f0b90b"/>
                    <path d="M20 15L20 20C20 30 30 35 30 45C30 55 40 55 40 45C40 35 50 30 50 20L50 15L20 15Z" fill="#f0b90b"/>
                    <circle cx="30" cy="30" r="8" fill="#1a202c"/>
                    <path d="M27 30L33 30M30 27L30 33" stroke="#f0b90b" stroke-width="2"/>
                </svg>';
            } else {
                echo '<svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M25 15C22 15 20 18 20 21L30 21C30 18 28 15 25 15Z" fill="#f0b90b"/>
                    <path d="M20 21L20 24C20 30 25 32 25 38C25 44 30 44 30 38C30 32 35 30 35 24L35 21L20 21Z" fill="#f0b90b"/>
                    <path d="M35 20C32 20 30 23 30 26L40 26C40 23 38 20 35 20Z" fill="#f0b90b"/>
                    <path d="M30 26L30 29C30 35 35 37 35 43C35 49 40 49 40 43C40 37 45 35 45 29L45 26L30 26Z" fill="#f0b90b"/>
                </svg>';
            }
            
            echo '</div>
            </div>';
        }
        
        // Close connection
        $stmt->close();
        $conn->close();
        ?>
    </div>
</body>
</html>