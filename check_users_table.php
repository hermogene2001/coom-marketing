<?php
include 'includes/db_connection.php';
try {
    $result = $conn->query('DESCRIBE users');
    echo 'Users table structure:' . PHP_EOL;
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . ' - ' . $row['Type'] . ' - ' . $row['Null'] . ' - ' . $row['Key'] . ' - ' . $row['Default'] . ' - ' . $row['Extra'] . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>