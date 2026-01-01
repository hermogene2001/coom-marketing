<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$clientId = $_SESSION['user_id'];
$productId = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

// Validate product ID
if ($productId <= 0) {
    $_SESSION['error'] = "Invalid product selection";
    header("Location: ../views/client_dashboard.php");
    exit;
}

// Check if user already purchased this product
$checkPurchase = $conn->prepare("SELECT id FROM purchases WHERE client_id = ? AND product_id = ?");
$checkPurchase->bind_param("ii", $clientId, $productId);
$checkPurchase->execute();
$checkPurchase->store_result();

if ($checkPurchase->num_rows > 0) {
    $_SESSION['error'] = "You have already purchased this product";
    header("Location: ../views/client_dashboard.php");
    exit;
}

// Fetch product details using prepared statement
$product_query = $conn->prepare("SELECT price, daily_earning, cycle FROM products WHERE id = ?");
$product_query->bind_param("i", $productId);
$product_query->execute();
$product_result = $product_query->get_result();
$product = $product_result->fetch_assoc();

if (!$product) {
    $_SESSION['error'] = "Product not found";
    header("Location: ../views/client_dashboard.php");
    exit;
}

$price = $product['price'];
$cycle_days = $product['cycle'];
$dailyEarning = $product['daily_earning'];

// Fetch user balance with prepared statement
$user_query = $conn->prepare("SELECT balance FROM users WHERE id = ?");
$user_query->bind_param("i", $clientId);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
$user_balance = $user['balance'];

// Check if user has enough balance
if ($user_balance < $price) {
    $_SESSION['error'] = "Insufficient balance";
    header("Location: ../views/client_dashboard.php");
    exit;
}

// Start transaction for atomic operations
$conn->begin_transaction();

try {
    // Deduct price from user balance
    $new_balance = $user_balance - $price;
    $update_balance = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $update_balance->bind_param("di", $new_balance, $clientId);
    $update_balance->execute();

    // Record transaction for the purchase
    $transaction_query = "INSERT INTO transactions (client_id, transaction_type, amount, date) VALUES (?, 'purchase', ?, NOW())";
    $stmt = $conn->prepare($transaction_query);
    $stmt->bind_param("id", $clientId, $price);
    $stmt->execute();

    // Record the purchase with start and end dates
    $purchase_datetime = date('Y-m-d');
    $end_datetime = date('Y-m-d', strtotime("+$cycle_days days"));
    
    $insert_purchase = $conn->prepare("INSERT INTO purchases (client_id, product_id, purchase_date, end_datetime, last_earned) 
                        VALUES (?, ?, ?, ?, ?)");
    $insert_purchase->bind_param("iisss", $clientId, $productId, $purchase_datetime, $end_datetime, $purchase_datetime);
    $insert_purchase->execute();

    // Insert into investments table
    $investment_query = $conn->prepare("INSERT INTO investments (user_id, amount, invested_at, start_date, end_date, status, daily_profit, last_profit_update) 
                     VALUES (?, ?, NOW(), ?, ?, 'active', '0.00', ?)");
    $investment_query->bind_param("idsss", $clientId, $price, $purchase_datetime, $end_datetime, $purchase_datetime);
    $investment_query->execute();

    // Commit transaction if all queries succeeded
    $conn->commit();

    $_SESSION['success'] = "Purchase and investment successful! Your investment end date is: " . $end_datetime;
    header("Location: ../views/purchased.php");
    exit;

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $_SESSION['error'] = "An error occurred during purchase. Please try again.";
    header("Location: ../views/client_dashboard.php");
    exit;
}

// Check and update investments when the end date has passed (moved to a separate cron job would be better)
$today = date('Y-m-d');
$update_query = $conn->prepare("UPDATE investments SET status = 'completed' WHERE end_date < ? AND status = 'active'");
$update_query->bind_param("s", $today);
$update_query->execute();
?>