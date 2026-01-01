<?php
session_start();
include '../includes/db.php';
// include '../calculate_daily_profit.php'; // Ensure this function is defined to calculate daily earnings

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$clientId = $_SESSION['user_id'];
$productId = $_GET['product_id'];

// Fetch product details, including price, cycle days, and daily earning
$product_query = "SELECT price, daily_earning, cycle FROM products WHERE id = '$productId'";
$product_result = mysqli_query($conn, $product_query);
$product = mysqli_fetch_assoc($product_result);

if ($product) {
    $price = $product['price'];
    $cycle_days = $product['cycle'];
    $dailyEarning = $product['daily_earning'];

    // Fetch user balance
    $user_query = "SELECT balance FROM users WHERE id = '$clientId'";
    $user_result = mysqli_query($conn, $user_query);
    $user = mysqli_fetch_assoc($user_result);
    $user_balance = $user['balance'];

    // Check if user has enough balance
    if ($user_balance >= $price) {
        // Deduct price from user balance
        $new_balance = $user_balance - $price;
        mysqli_query($conn, "UPDATE users SET balance = '$new_balance' WHERE id = '$clientId'");

        // Record transaction for the purchase
        $transaction_query = "INSERT INTO transactions (client_id, transaction_type, amount, date) VALUES (?, 'purchase', ?, NOW())";
        $stmt = $conn->prepare($transaction_query);
        $stmt->bind_param("id", $clientId, $price);
        $stmt->execute();

        // Record the purchase with start and end dates based on the cycle
        $purchase_datetime = date('Y-m-d H:i:s');
        $end_datetime = date('Y-m-d H:i:s', strtotime("+$cycle_days days", strtotime($purchase_datetime)));
        
        $insert_purchase = "INSERT INTO purchases (client_id, product_id, purchase_date, end_datetime, last_earned) 
                            VALUES ('$clientId', '$productId', '$purchase_datetime', '$end_datetime', '$purchase_datetime')";
        mysqli_query($conn, $insert_purchase);

        // Insert into investments table to track daily earnings and end date
        $investment_query = "INSERT INTO investments (user_id, amount, invested_at, start_date, end_date, status, daily_profit, last_profit_update) 
                     VALUES ('$clientId', '$price', NOW(), '$purchase_datetime', '$end_datetime', 'active', '0.00', '$purchase_datetime')";

        mysqli_query($conn, $investment_query);


        // Display success message
        echo "Purchase and investment successful!<br>";
        echo "Your investment end date is: " . $end_datetime;
?>
<script type="text/javascript">
    setTimeout(function() {
        window.location.href = "../views/purchased.php";
    }, 5000); // Redirect after 5 seconds
</script>
<?php
        exit;
    } else {
        echo "Insufficient balance.";
        header("Location: ../views/client_dashboard.php");
        exit;
    }
} else {
    echo "Product not found.";
    header("Location: ../views/client_dashboard.php");
    exit;
}

// Check and update investments when the end date has passed
$today = date('Y-m-d');
$update_query = "UPDATE investments SET status = 'completed' WHERE end_date < ? AND status = 'active'";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("s", $today);
$stmt->execute();

echo "Investment statuses updated successfully.";
?>
