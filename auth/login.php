<?php
session_start();
include('../includes/db_connection.php');

// Initialize error message
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get and validate input
        $phone_number = $_POST['phone'] ?? '';
        $password = $_POST['phone-password'] ?? '';
        
        if (empty($phone_number)) {
            throw new Exception('Phone number is required');
        }
        
        if (empty($password)) {
            throw new Exception('Password is required');
        }

        // Format phone number
        $full_phone = preg_replace('/[^+0-9]/', '', $phone_number);

        // Prepare database query
        $sql = "SELECT * FROM users WHERE phone_number = ?";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Database preparation failed: ' . $conn->error);
        }

        $stmt->bind_param("s", $full_phone);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows === 0) {
            throw new Exception('Invalid phone number or password');
        }

        $user = $result->fetch_assoc();

        // Verify account status
        if ($user['status'] !== 'active') {
            throw new Exception('Your account is inactive. Please contact support.');
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            throw new Exception('Invalid phone number or password');
        }

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['referral_code'] = $user['referral_code'];
        $_SESSION['phone_number'] = $user['phone_number'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['profile_picture'] = !empty($user['profile_picture']) 
            ? $user['profile_picture'] 
            : '../assets/images/default-profile.jpg';

        // Define role-based redirection
        $role_redirects = [
            'admin' => '../admin/dashboard.php',
            'agent' => '../agent/agent_dashboard.php',
            'client' => '../client/'
        ];

        $redirect_url = $role_redirects[$user['role']] ?? '../index.php';
        
        // Redirect to appropriate dashboard
        header("Location: $redirect_url");
        exit();

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
    }
}

// If we get here, there was an error
$_SESSION['login_error'] = $error_message;
header("Location: ../index.php");
exit();
?>