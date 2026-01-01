<?php
require_once('../includes/db.php');

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    $query = "DELETE FROM users WHERE id = $user_id";
    if (mysqli_query($conn, $query)) {
        header('Location: ../admin/users.php?message=User deleted successfully');
    } else {
        header('Location: ../admin/users.php?message=Error deleting user');
    }
}
?>
