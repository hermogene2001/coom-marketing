<?php

// distribute_profits.php
require_once('../includes/db.php');

// Function to distribute profits to users
function distributeProfits($client_id) {
    global $conn;

    // Check for eligible purchases
    $query = "SELECT daily_earning FROM purchases WHERE client_id = $client_id AND purchase_date <= NOW() - INTERVAL 24 HOUR";
    $result = mysqli_query($conn, $query);
    
    // Loop through eligible purchases and update balance
    while ($row = mysqli_fetch_assoc($result)) {
        $daily_earning = $row['daily_earning'];

        // Update user's balance
        $update_query = "UPDATE users SET balance = balance + $daily_earning WHERE id = $client_id";
        mysqli_query($conn, $update_query);

        // Update purchase date to the current time
        $update_purchase_query = "UPDATE purchases SET purchase_date = NOW() WHERE client_id = $client_id";
        mysqli_query($conn, $update_purchase_query);
    }
}

// Trigger profit distribution when user accesses the dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['distribute_profits'])) {
    session_start();
    if ($_SESSION['role'] === 'client') {
        distributeProfits($_SESSION['user_id']);
        echo "Profits distributed successfully.";
    } else {
        echo "Access denied.";
    }
}
