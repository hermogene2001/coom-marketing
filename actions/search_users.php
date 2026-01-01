<?php
require_once('../includes/db.php');

// Get the phone query
$phone = isset($_GET['phone']) ? $_GET['phone'] : '';

$sql = "SELECT * FROM users WHERE role != 'admin'";
if (!empty($phone)) {
    $sql .= " AND phone_number LIKE ?";
}

$stmt = $conn->prepare($sql);

if (!empty($phone)) {
    $search = '%' . $phone . '%';
    $stmt->bind_param("s", $search);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($user = $result->fetch_assoc()) {
        // Set active status (assuming we have an 'active' column in the database with 1 for active, 0 for inactive)
        // If the column doesn't exist yet, default to active (1)
        $isActive = isset($user['active']) ? $user['active'] : 1;
        $statusClass = $isActive ? 'status-active' : 'status-inactive';
        $statusText = $isActive ? 'Active' : 'Inactive';
        
        echo '<tr>';
        echo '<td>' . $user['id'] . '</td>';
        echo '<td>' . $user['fname'] . ' ' . $user['lname'] . '</td>';
        echo '<td>' . $user['phone_number'] . '</td>';
        echo '<td>' . $user['referral_code'] . '</td>';
        echo '<td>' . $user['balance'] . '</td>';
        echo '<td>' . $user['role'] . '</td>';
        echo '<td class="' . $statusClass . '">' . $statusText . '</td>';
        echo '<td>
            <button class="btn edit-btn" 
                data-id="' . $user['id'] . '" 
                data-phone="' . $user['phone_number'] . '" 
                data-role="' . $user['role'] . '" 
                data-balance="' . $user['balance'] . '"
                data-active="' . $isActive . '">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button class="btn password-btn"
                data-id="' . $user['id'] . '"
                data-name="' . $user['fname'] . ' ' . $user['lname'] . '"
                data-phone="' . $user['phone_number'] . '">
                <i class="fas fa-key"></i> Password
            </button>
            <a href="../actions/delete_user.php?id=' . $user['id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Delete user?\')">
                <i class="fas fa-trash"></i> Delete
            </a>
            </td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="8">No users found</td></tr>';
}
$stmt->close();
$conn->close();
?>