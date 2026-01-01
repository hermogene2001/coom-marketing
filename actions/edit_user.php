<?php
require_once('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['id'];
    $phone_number = $_POST['phone_number'];
    $role = $_POST['role'];

    $query = "UPDATE users SET phone_number = '$phone_number', role = '$role' WHERE id = $user_id";
    if (mysqli_query($conn, $query)) {
        header('Location: ../views/admin_dashboard.php?message=User updated successfully');
    } else {
        header('Location: ../views/admin_dashboard.php?message=Error updating user');
    }
}
?>
