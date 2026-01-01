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
    $active = isset($_POST['active']) ? 1 : 0; // If checkbox is checked, active = 1, otherwise 0
    
    // Update user information
    $sql = "UPDATE users SET phone_number = ?, role = ?, balance = ?, active = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdii", $phone_number, $role, $balance, $active, $id);
    
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