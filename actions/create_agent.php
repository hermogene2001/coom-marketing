<?php
session_start();

// Check if the user is an admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Database connection
require_once('../includes/db.php');

// Get form data
$phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
$password = mysqli_real_escape_string($conn, $_POST['password']);
$referral_code = mysqli_real_escape_string($conn, $_POST['referral_code']);

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new agent into the database
$insert_query = "INSERT INTO users (phone_number, password, referral_code, role) VALUES ('$phone_number', '$hashed_password', '$referral_code', 'agent')";

if (mysqli_query($conn, $insert_query)) {
    // Redirect back to admin dashboard with success message
    header('Location: ../admin/users.php?message=Agent created successfully');
} else {
    // Redirect back with error message
    header('Location: ../admin/dashboard.php?message=Error creating agent');
}
?>
