<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords match
    if ($new_password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match!";
        header('Location: ../admin/manage_users.php');
        exit();
    }
    
    // Hash the password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    // Update user password
    $sql = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hashed_password, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Password changed successfully!";
    } else {
        $_SESSION['error_message'] = "Error changing password: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
    
    header('Location: ../admin/manage_users.php');
    exit();
} else {
    header('Location: ../admin/manage_users.php');
    exit();
}
?>