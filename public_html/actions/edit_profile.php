<?php
session_start();

// Ensure the user is logged in and has a 'client' role
if ($_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

include '../includes/db.php';

// Fetch the user's details from the session
$userId = $_SESSION['user_id'];

// Fetch the user's current information
$sql = "SELECT phone_number, fname, lname FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($phoneNumber, $firstName, $lastName);
$stmt->fetch();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPhoneNumber = $_POST['phone_number'];
    $newFirstName = $_POST['first_name'];
    $newLastName = $_POST['last_name'];

    // Update the user's information in the database
    $updateSql = "UPDATE users SET phone_number = ?, fname = ?, lname = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("sssi", $newPhoneNumber, $newFirstName, $newLastName, $userId);
    $updateStmt->execute();
    $updateStmt->close();

    // Optionally, you can store the updated info back into session
    $_SESSION['phone_number'] = $newPhoneNumber;

    echo "<script>alert('Profile updated successfully!'); window.location.href = '../views/account.php';</script>";
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harbor Investment - Edit Profile</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
        }
        .profile-container {
            background-color: #1e1e1e;
            border-radius: 10px;
            padding: 25px;
            margin-top: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            border: 1px solid #333;
        }
        .form-control {
            background-color: #2d2d2d;
            color: #e0e0e0;
            border: 1px solid #444;
        }
        .form-control:focus {
            background-color: #333;
            color: #fff;
            border-color: #4a89dc;
            box-shadow: 0 0 0 0.25rem rgba(74, 137, 220, 0.25);
        }
        .btn-primary {
            background-color: #4a89dc;
            border-color: #4a89dc;
        }
        .btn-primary:hover {
            background-color: #3a70c2;
            border-color: #3a70c2;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        .header {
            color: #4a89dc;
            margin-bottom: 20px;
        }
        /* Fixed bottom navbar styles */
        .fixed-bottom {
            background-color: #1e1e1e;
            padding: 10px 0;
            border-top: 1px solid #333;
        }
        .fixed-bottom .nav-link {
            text-align: center;
            color: #aaa;
        }
        .fixed-bottom .nav-link i {
            font-size: 22px;
            display: block;
            margin: 0 auto 5px;
        }
        .fixed-bottom .nav-link.active {
            color: #4a89dc;
        }
        .fixed-bottom .nav-link:hover {
            color: #4a89dc;
        }
        .container {
            margin-bottom: 90px;
        }
        .nav-label {
            font-size: 12px;
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <h3 class="header">Harbor Investment</h3>
    
    <div class="profile-container">
        <h4 class="mb-4">Edit Profile</h4>

        <form action="" method="POST">
            <div class="mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="phone_number" name="phone_number" 
                       value="<?php echo htmlspecialchars($phoneNumber); ?>" required>
            </div>

            <div class="mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" 
                       value="<?php echo htmlspecialchars($firstName); ?>" required>
            </div>

            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" 
                       value="<?php echo htmlspecialchars($lastName); ?>" required>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="../views/account.php" class="btn btn-secondary me-md-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Fixed Bottom Navbar -->
<!-- <nav class="navbar navbar-expand-lg fixed-bottom">
    <div class="container-fluid">
        <div class="row w-100 text-center mx-auto">
            <div class="col-4 text-center">
                <a href="client_dashboard.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span class="nav-label">Home</span>
                </a>
            </div>
            <div class="col-4 text-center">
                <a href="invite.php" class="nav-link">
                    <i class="fas fa-users"></i>
                    <span class="nav-label">Team</span>
                </a>
            </div>
            <div class="col-4 text-center">
                <a href="account.php" class="nav-link active">
                    <i class="fas fa-wallet"></i>
                    <span class="nav-label">Wallet</span>
                </a>
            </div>
        </div>
    </div>
</nav> -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>