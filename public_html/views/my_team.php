<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../includes/db.php';

// Retrieve the logged-in user's ID from the session
$userId = $_SESSION['user_id'];

// Fetch all users whose invitation_code matches the logged-in user's ID
$sql = "SELECT id, phone_number, created_at FROM users WHERE invitation_code = ? AND role != 'admin'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$teamMembers = [];
while ($row = $result->fetch_assoc()) {
    $teamMembers[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harbor Investment - My Team</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
            --card-border: #334155;
            --text-color: #e2e8f0;
            --text-muted: #94a3b8;
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --nav-bg: #1a2a3a;
            --accent-color: #38bdf8;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .team-card {
            margin-top: 20px;
            background-color: var(--card-bg);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            border: 1px solid var(--card-border);
        }
        
        .team-card h4 {
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .table-dark {
            background-color: var(--card-bg);
            color: var(--text-color);
            border-color: var(--card-border);
        }
        
        .table-dark th {
            background-color: var(--dark-bg);
            border-color: var(--card-border);
            color: var(--accent-color);
        }
        
        .table-dark td {
            border-color: var(--card-border);
        }
        
        /* Fixed bottom navbar styles */
        .fixed-bottom {
            background-color: var(--nav-bg);
            padding: 10px 0;
            border-top: 1px solid var(--card-border);
        }
        
        .fixed-bottom .nav-link {
            text-align: center;
            color: var(--text-muted);
        }
        
        .fixed-bottom .nav-link i {
            font-size: 22px;
            display: block;
            margin: 0 auto 5px;
        }
        
        .fixed-bottom .nav-link.active {
            color: var(--primary-color);
        }
        
        .fixed-bottom .nav-link:hover {
            color: var(--primary-hover);
        }
        
        .container {
            margin-bottom: 90px;
        }
        
        .nav-label {
            font-size: 12px;
        }
        
        .header {
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .text-center {
            color: var(--text-muted);
        }
    </style>
</head>
<body>

<div class="container mt-4">
    <h3 class="header">Harbor Investment</h3>
    
    <div class="team-card">
        <h4>My Team Members</h4>

        <!-- Display a table of team members -->
        <div class="table-responsive">
            <table class="table table-dark table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Phone Number</th>
                        <th>Date Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($teamMembers) > 0): ?>
                        <?php foreach ($teamMembers as $member): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['id']); ?></td>
                                <td><?php echo htmlspecialchars($member['phone_number']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($member['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">No team members yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Fixed Bottom Navbar -->
<nav class="navbar navbar-expand-lg fixed-bottom">
    <div class="container-fluid">
        <div class="row w-100 text-center mx-auto">
            <div class="col-4 text-center">
                <a href="client_dashboard.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span class="nav-label">Home</span>
                </a>
            </div>
            <div class="col-4 text-center">
                <a href="invite.php" class="nav-link active">
                    <i class="fas fa-users"></i>
                    <span class="nav-label">Team</span>
                </a>
            </div>
            <div class="col-4 text-center">
                <a href="account.php" class="nav-link">
                    <i class="fas fa-wallet"></i>
                    <span class="nav-label">Wallet</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>