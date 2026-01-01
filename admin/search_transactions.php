<?php
require_once('../includes/db.php');

// Get the search query (phone number or transaction type)
$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// Modify the SQL query to include the search parameter
$transactions_query = "
    SELECT t.*, u.phone_number 
    FROM transactions t 
    JOIN users u ON t.client_id = u.id 
    WHERE u.phone_number LIKE ? OR t.transaction_type LIKE ?
    ORDER BY t.transaction_type, t.date DESC
";

// Prepare the statement to prevent SQL injection
$stmt = $conn->prepare($transactions_query);

// Bind the search parameter
$search = '%' . $search_query . '%';
$stmt->bind_param('ss', $search, $search); // Both phone_number and transaction_type are string types
$stmt->execute();
$transactions_result = $stmt->get_result();

$current_type = null; // Variable to track the current transaction type

// Output the table rows based on the search results
if ($transactions_result->num_rows > 0) {
    while ($transaction = mysqli_fetch_assoc($transactions_result)) {
        if ($current_type !== $transaction['transaction_type']) {
            // Display a header row for each new transaction type
            $current_type = $transaction['transaction_type'];
            echo '<tr class="table-primary">
                    <td colspan="5"><strong>' . ucfirst($current_type) . ' Transactions</strong></td>
                  </tr>';
        }
        echo '<tr>
                <td>' . $transaction['id'] . '</td>
                <td>' . $transaction['phone_number'] . '</td>
                <td>' . $transaction['amount'] . '</td>
                <td>' . ucfirst($transaction['transaction_type']) . '</td>
                <td>' . $transaction['date'] . '</td>
              </tr>';
    }
} else {
    echo '<tr><td colspan="5">No transactions found.</td></tr>';
}

$stmt->close();
$conn->close();
?>
