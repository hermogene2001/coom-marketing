<?php
include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone_number'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE phone_number = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Set session for logged in user
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['referral_code'] = $user['referral_code'];

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: ../views/admin_dashboard.php");
        } elseif ($user['role'] === 'agent') {
            header("Location: ../views/agent_dashboard.php");
        } else {
            header("Location: ../views/client_dashboard.php");
        }
        exit;
    } else {
        echo "Invalid credentials!";
    }
}
?>
