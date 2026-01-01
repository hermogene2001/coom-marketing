<?php
session_start();
require_once('../includes/db.php');

// Check if the user is logged in as an agent
if ($_SESSION['role'] !== 'agent') {
    header('Location: ../index.php');
    exit;
}

// Handle the withdrawal approval or rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $withdrawal_id = (int)$_POST['withdrawal_id'];
    $action = $_POST['action'];

    // Update the withdrawal status based on action
    if ($action === 'approve') {
        $update_query = "UPDATE withdrawals SET status = 'approved' WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "i", $withdrawal_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Notify user of approval (optional)
        // You can implement email or other notification methods here
        echo "<script>alert('Withdrawal approved successfully!'); window.location.href = 'agent_approve_withdrawal.php';</script>";
    } else if ($action === 'reject') {
        $update_query = "UPDATE withdrawals SET status = 'rejected' WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "i", $withdrawal_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Notify user of rejection (optional)
        echo "<script>alert('Withdrawal rejected.'); window.location.href = 'agent_dashboard.php';</script>";
    }
}
?>
