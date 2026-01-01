<?php
session_start();
include '../includes/db.php';

// Function to generate a unique referral code
function generateReferralCode($length = 8) {
    return substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, $length);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $phoneNumber = $_POST['phone_number'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $invitationCode = $_POST['invitation_code'] ?? null; // This is the referrer's code
    $referrerId = null;

    // Check if the invitation code is empty
    if (empty($invitationCode)) {
        echo "<script>
                alert('Invitation code is required. Please provide a valid referral code.');
                window.location.href = '../views/register.php'; // Replace with your registration page URL
              </script>";
        exit;
    }

    // Generate a unique referral code for the new user
    $referralCode = generateReferralCode();

    // Check if the phone number already exists
    $sql = "SELECT * FROM users WHERE phone_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $phoneNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Phone number already exists, handle the error and redirect back to registration page
        echo "<script>
                alert('Phone number already registered.');
                window.location.href = '../views/register.php'; // Replace with your registration page URL
              </script>";
    } else {
        // Check if the invitation code (referrer's code) is valid
        $referrerQuery = "SELECT id FROM users WHERE referral_code = ?";
        $stmt = $conn->prepare($referrerQuery);
        $stmt->bind_param("s", $invitationCode);
        $stmt->execute();
        $stmt->bind_result($referrerId);
        $stmt->fetch();
        $stmt->close();

        if (!$referrerId) {
            // Invalid invitation code, redirect back to registration page
            echo "<script>
                    alert('Invalid referral code. Please check and try again.');
                    window.location.href = '../views/register.php'; // Replace with your registration page URL
                  </script>";
            exit;
        }

        // Insert the new user into the database, with referrer ID in the invitation_code column
        $sql = "INSERT INTO users (phone_number, password, invitation_code, referral_code, balance, created_at, role) VALUES (?, ?, ?, ?, 1000, NOW(), 'client')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $phoneNumber, $password, $referrerId, $referralCode);

        if ($stmt->execute()) {
            // Registration successful, redirect to the dashboard or login page
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['phone_number'] = $phoneNumber;
            $_SESSION['role'] = 'client';
            $_SESSION['referral_code'] = $referralCode;

            echo "<script>
                    alert('Registration successful. Your referral code: " . $referralCode . "');
                    window.location.href = '../views/client_dashboard.php'; // Replace with your dashboard page URL
                  </script>";
        } else {
            // Registration failed, redirect back to registration page
            echo "<script>
                    alert('Registration failed. Please try again.');
                    window.location.href = '../views/register.php'; // Replace with your registration page URL
                  </script>";
        }
    }

    $stmt->close();
    $conn->close();
}
?>
