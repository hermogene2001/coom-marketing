<?php
session_start();
include '../includes/db_connection.php';
include 'nav.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? $user['first_name'];
    $last_name = $_POST['last_name'] ?? $user['last_name'];
    $email = $_POST['email'] ?? $user['email'];
    $phone = $_POST['phone'] ?? $user['phone_number'];
    
    // Update profile
    $update_query = "UPDATE users SET 
                    first_name = ?, 
                    last_name = ?, 
                    email = ?, 
                    phone_number = ? 
                    WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssssi", $first_name, $last_name, $email, $phone, $user_id);
    
    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
        // Refresh user data
        $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
        $user = $result->fetch_assoc();
    } else {
        $error = "Failed to update profile: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COOM-MARKETING - Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-bg: #0d1117;
            --secondary-bg: #161b22;
            --card-bg: #1a2029;
            --accent-color: #23a559;
            --accent-color-light: #37c070;
            --text-color: #e6edf3;
            --text-secondary: #7d8590;
            --border-color: #303841;
            --positive: #23a559;
            --negative: #e34c26;
            --header-bg: #0d1117;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--primary-bg);
            color: var(--text-color);
            min-height: 100vh;
            padding-top: 80px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 15px;
        }
        
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: var(--secondary-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            overflow: hidden;
            border: 2px solid var(--border-color);
        }
        
        .profile-pic img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .profile-info h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .profile-info p {
            color: var(--text-secondary);
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .profile-stats {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: var(--accent-color-light);
        }
        
        .stat-label {
            font-size: 14px;
            color: var(--text-secondary);
        }
        
        .form-container {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            border: 1px solid var(--border-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        input, select {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background-color: var(--secondary-bg);
            color: var(--text-color);
            font-size: 16px;
        }
        
        .submit-btn {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
            transition: background-color 0.3s;
        }
        
        .submit-btn:hover {
            background-color: var(--accent-color-light);
        }
        
        .error-message {
            color: var(--negative);
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background-color: rgba(227, 76, 38, 0.1);
            border-radius: 8px;
        }
        
        .success-message {
            color: var(--positive);
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background-color: rgba(35, 165, 89, 0.1);
            border-radius: 8px;
        }
        
        .profile-section {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--accent-color-light);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-pic">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="../uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 21V19C20 16.7909 18.2091 15 16 15H8C5.79086 15 4 16.7909 4 19V21" stroke="var(--text-color)" stroke-width="2" stroke-linecap="round"/>
                            <circle cx="12" cy="7" r="4" stroke="var(--text-color)" stroke-width="2"/>
                        </svg>
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                    <p>Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                    <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
                    <p>Phone: <?php echo htmlspecialchars($user['phone_number']); ?></p>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($user['balance'], 2); ?></div>
                            <div class="stat-label">Balance</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo $user['vip_level']; ?></div>
                            <div class="stat-label">VIP Level</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <div class="profile-section">
                <h3 class="section-title">Edit Profile</h3>
                <div class="form-container">
                    <form action="profile.php" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone_number']); ?>">
                        </div>
                        
                        <button type="submit" class="submit-btn">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>