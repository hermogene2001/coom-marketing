<?php
session_start();
if ($_SESSION['role'] !== 'agent') {
    header("Location: ../login.php");
    exit;
}

// Database connection
require_once('../../includes/db.php');

// Check if the client ID is provided
$client_id = isset($_GET['client_id']) ? $_GET['client_id'] : null;

if (!$client_id) {
    header("Location: manage_clients.php");
    exit;
}

// Fetch client details
$client_query = "SELECT fname, lname, phone_number, balance FROM users WHERE id = '$client_id' AND role = 'client'";
$client_result = mysqli_query($conn, $client_query);
$client = mysqli_fetch_assoc($client_result);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update client balance
    $new_balance = $_POST['balance'];
    $update_query = "UPDATE users SET balance = '$new_balance' WHERE id = '$client_id'";
    
    if (mysqli_query($conn, $update_query)) {
        $success_message = "Client balance updated successfully.";
    } else {
        $error_message = "Error updating balance: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Balance</title>
    <link rel="stylesheet" href="../../assets/bootstrap.min.css">
    <style>
        body {
            background-color: #f7f7fc; /* Light background */
            color: #343a40; /* Dark text */
        }
        .container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Update Balance for <?php echo htmlspecialchars($client['fname'] . ' ' . $client['lname']); ?></h2>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php elseif (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="balance">Current Balance (RWF)</label>
                <input type="number" class="form-control" id="balance" name="balance" value="<?php echo htmlspecialchars($client['balance']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Balance</button>
        </form>
        <a href="manage_clients.php" class="btn btn-success mt-3">Back to Clients List</a>
    </div>
</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
