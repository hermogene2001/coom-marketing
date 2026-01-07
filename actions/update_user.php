<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $phone_number = $_POST['phone_number'];
    $role = $_POST['role'];
    $balance = $_POST['balance'];
    $status = isset($_POST['active']) ? 'active' : 'inactive'; // If checkbox is checked, status = 'active', otherwise 'inactive'
    
    // Update user information
    $sql = "UPDATE users SET phone_number = ?, role = ?, balance = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdsi", $phone_number, $role, $balance, $status, $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "User updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error updating user: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
    
    header('Location: ../admin/users.php');
    exit();
} else {
    header('Location: ../admin/users.php');
    exit();
}
?>