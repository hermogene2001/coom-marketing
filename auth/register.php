<?php
session_start();
include '../includes/db_connection.php';

function generateReferralCode($length = 8) {
    return substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, $length);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get all form data
    $email = $_POST['email'] ?? null;
    $phone_number = preg_replace('/[^+0-9]/', '', $_POST['phone_number'] ?? '');
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $invitation_code = $_POST['invitation'] ?? null;
    
    // Generate name from email or phone
    $name = $email ? explode('@', $email)[0] : 'User' . substr($phone_number, -4);
    $referral_code = generateReferralCode();

    // Validate inputs
    if (empty($email) && empty($phone_number)) {
        $_SESSION['error'] = 'Either email or phone number is required';
        header("Location: ../signup.php");
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match';
        header("Location: ../signup.php");
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters';
        header("Location: ../signup.php");
        exit;
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Check existing user
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone_number = ?");
    $stmt->bind_param("ss", $email, $phone_number);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        $_SESSION['error'] = 'User already exists';
        header("Location: ../signup.php");
        exit;
    }
    $stmt->close();

    // Process invitation code
    $referrer_id = null;
    if (!empty($invitation_code)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE referral_code = ?");
        $stmt->bind_param("s", $invitation_code);
        $stmt->execute();
        $stmt->bind_result($referrer_id);
        $stmt->fetch();
        $stmt->close();

        if (!$referrer_id && $invitation_code !== '947474') {
            $_SESSION['error'] = 'Invalid invitation code';
            header("Location: ../signup.php");
            exit;
        }
    }

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (first_name, email, phone_number, password, invitation_code, referral_code, balance, created_at, role, status) VALUES (?, ?, ?, ?, ?, ?, 0, NOW(), 'client', 'active')");
    $stmt->bind_param("ssssis", $name, $email, $phone_number, $password_hash, $referrer_id, $referral_code);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['email'] = $email;
        $_SESSION['phone_number'] = $phone_number;
        $_SESSION['role'] = 'client';
        $_SESSION['referral_code'] = $referral_code;
        $_SESSION['user_name'] = $name;
        
        header("Location: ../client/");
        exit;
    } else {
        $_SESSION['error'] = 'Registration failed: ' . $stmt->error;
        header("Location: ../signup.php");
    }

    $stmt->close();
    $conn->close();
}
?>