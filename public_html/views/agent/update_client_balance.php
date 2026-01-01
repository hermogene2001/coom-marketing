<?php
session_start();
if ($_SESSION['role'] !== 'agent') {
    header("Location: ../login.php");
    exit;
}

// Database connection
require_once('../../includes/db.php');

// Fetch all clients for the agent to manage
$client_query = "SELECT id, phone_number, balance FROM users WHERE role = 'client'";
$client_result = mysqli_query($conn, $client_query);

// Handle balance update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'];
    $new_balance = $_POST['balance'];

    // Update client's balance
    $update_query = "UPDATE users SET balance = balance + '$new_balance' WHERE id = '$client_id'";
    if (mysqli_query($conn, $update_query)) {
        $_SESSION['message'] = "Client balance updated successfully.";
        header("Location: update_client_balance.php");
        exit;
    } else {
        $_SESSION['message'] = "Error updating balance: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Client Balance</title>
    <link rel="stylesheet" href="../../assets/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Update Client Balance</h2>

        <!-- Display success or error message -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Form to update balance -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="client_id">Select Client</label>
                <select class="form-control" id="client_id" name="client_id" required>
                    <?php while ($client = mysqli_fetch_assoc($client_result)): ?>
                        <option value="<?php echo $client['id']; ?>">
                            <?php echo $client['phone_number'] . " (Balance: " . $client['balance'] . ")"; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="balance">New Balance</label>
                <input type="number" class="form-control" id="balance" name="balance" required>
            </div>

            <button type="submit" class="btn btn-success">Update Balance</button>
            <a href="../agent_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
