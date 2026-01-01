<?php
session_start();
if ($_SESSION['role'] !== 'agent') {
    header("Location: ../login.php");
    exit;
}

// Database connection
require_once('../../includes/db.php');

// Fetch clients from the database
$client_query = "SELECT id, fname, lname, phone_number, balance FROM users WHERE role = 'client'";
$client_result = mysqli_query($conn, $client_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Clients</title>
    <link rel="stylesheet" href="../../assets/bootstrap.min.css">
    <style>
        body {
            background-color: #f7f7fc; /* Light background */
            color: #343a40; /* Dark text */
        }
        .container {
            margin-top: 20px;
        }
        table {
            width: 100%;
            margin-bottom: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .button-group {
            text-align: right;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Agent Dashboard</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="agent/referrals.php">View Referrals</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="agent/settings.php">Settings</a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link" href="agent/change_password.php">Change Password</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../actions/logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h2>Clients List</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Phone Number</th>
                    <th>Balance</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($client = mysqli_fetch_assoc($client_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['id']); ?></td>
                        <td><?php echo htmlspecialchars($client['fname']); ?></td>
                        <td><?php echo htmlspecialchars($client['lname']); ?></td>
                        <td><?php echo htmlspecialchars($client['phone_number']); ?></td>
                        <td><?php echo number_format($client['balance'], 2); ?> RWF</td>
                        <td>
                            <!-- Link to update balance -->
                            <a href="update_balance.php?client_id=<?php echo $client['id']; ?>" class="btn btn-primary">Update Balance</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="button-group mt-3">
            <a href="../agent_dashboard.php" class="btn btn-success">Back to Dashboard</a>
            <a href="../../actions/logout.php" class="btn btn-danger">Logout</a>
        </div>
    </div>
</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
