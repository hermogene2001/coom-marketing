<?php
session_start();

// Database connection
require_once('../includes/db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Get form input values
$current_password = $_POST['current_password'];
$new_password = $_POST['new_password'];
$confirm_new_password = $_POST['confirm_new_password'];

// Fetch the current password from the database
$query = "SELECT password FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    // Verify current password
    if (password_verify($current_password, $user['password'])) {
        // Check if new password matches the confirmation
        if ($new_password === $confirm_new_password) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the database
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $hashed_password, $user_id);

            if ($update_stmt->execute()) {
                // Success message and redirect
                header('Location: ../admin/dashboard.php?message=Password updated successfully');
            } else {
                header('Location: ../admin/dashboard.php?message=Error updating password');
            }
        } else {
            header('Location: ../admin/dashboard.php?message=New passwords do not match');
        }
    } else {
        header('Location: ../admin/dashboard.php?message=Incorrect current password');
    }
} else {
    header('Location: ../admin/dashboard.php?message=User not found');
}

?>
