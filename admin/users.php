<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
require_once('../includes/db.php');
include('../includes/menu.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Coom Marketing</title>
    <!-- Add Bootstrap and jQuery CDNs -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
            --success-hover: #059669;
            --danger-color: #ef4444;
            --danger-hover: #dc2626;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
        }
        
        .container {
            padding-top: 2rem;
        }
        
        h2 {
            color: var(--text-color);
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 0.5rem;
        }
        
        .table {
            color: var(--text-color);
            background-color: var(--card-bg);
            border-color: var(--card-border);
        }
        
        .table th {
            background-color: #1a2a3a;
            border-color: var(--card-border);
        }
        
        .table td {
            border-color: var(--card-border);
            vertical-align: middle;
        }
        
        .form-control {
            background-color: #2d3748;
            border-color: var(--card-border);
            color: var(--text-color);
        }
        
        .form-control:focus {
            background-color: #2d3748;
            color: var(--text-color);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        
        .modal-content {
            background-color: var(--card-bg);
            color: var(--text-color);
            border: 1px solid var(--card-border);
        }
        
        .modal-header {
            border-bottom: 1px solid var(--card-border);
        }
        
        .modal-footer {
            border-top: 1px solid var(--card-border);
        }
        
        .btn-close {
            filter: invert(1);
        }
        
        .edit-btn, .password-btn {
            display: inline-flex;
            align-items: center;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-right: 5px;
        }
        
        .edit-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        
        .edit-btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            color: white;
        }
        
        .password-btn {
            background-color: var(--success-color);
            color: white;
            border: none;
        }
        
        .password-btn:hover {
            background-color: var(--success-hover);
            transform: translateY(-2px);
            color: white;
        }
        
        .edit-btn i, .password-btn i {
            margin-right: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
            border-radius: 8px;
        }
        
        .btn-success:hover {
            background-color: var(--success-hover);
            border-color: var(--success-hover);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
            border-radius: 8px;
        }
        
        .btn-danger:hover {
            background-color: var(--danger-hover);
            border-color: var(--danger-hover);
        }
        
        .status-active {
            color: var(--success-color);
            font-weight: bold;
        }
        
        .status-inactive {
            color: var(--danger-color);
            font-weight: bold;
        }
        
        .form-check-input {
            background-color: #2d3748;
            border-color: var(--card-border);
        }
        
        .form-check-input:checked {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-users-cog me-2"></i>Manage Users</h2>
        <div class="mb-4">
            <div class="input-group">
                <span class="input-group-text bg-dark border-dark text-light">
                    <i class="fas fa-search"></i>
                </span>
                <input type="text" id="searchPhone" class="form-control" placeholder="Search by phone number...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Names</th>
                        <th>Phone Number</th>
                        <th>Referral Code</th>
                        <th>Balance</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="userTable">
                    <!-- Dynamic content will be loaded here -->
                </tbody>
            </table>
        </div>

        <!-- Edit User Modal -->
        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel"><i class="fas fa-user-edit me-2"></i>Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="../actions/update_user.php" method="POST">
                            <input type="hidden" name="id" value="">
                            <div class="mb-3">
                                <label for="editPhoneNumber" class="form-label">Phone Number</label>
                                <input type="text" class="form-control" id="editPhoneNumber" name="phone_number" required>
                            </div>
                            <div class="mb-3">
                                <label for="editRole" class="form-label">Role</label>
                                <select class="form-select" id="editRole" name="role" required>
                                    <option value="client">Client</option>
                                    <option value="agent">Agent</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editBalance" class="form-label">Balance</label>
                                <input type="number" step="0.01" class="form-control" id="editBalance" name="balance" required>
                            </div>
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="editActive" name="active" value="1">
                                <label class="form-check-label" for="editActive">Account Active</label>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Update User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Change Password Modal -->
        <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="changePasswordModalLabel"><i class="fas fa-key me-2"></i>Change User Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="../actions/change_user_password.php" method="POST">
                            <input type="hidden" name="id" value="">
                            <div class="mb-3">
                                <label for="userIdentifier" class="form-label">User</label>
                                <input type="text" class="form-control" id="userIdentifier" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="newPassword" class="form-label">New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="newPassword" name="new_password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                                <div class="invalid-feedback" id="passwordMismatch">
                                    Passwords do not match!
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success" id="changePasswordBtn">Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Load all users initially
        loadUsers('');

        // Fetch users dynamically as the admin types
        $('#searchPhone').on('keyup', function() {
            const query = $(this).val();
            loadUsers(query);
        });

        function loadUsers(query) {
            $.ajax({
                url: '../actions/search_users.php',
                method: 'GET',
                data: { phone: query },
                success: function(data) {
                    $('#userTable').html(data);
                }
            });
        }

        // Open Edit User Modal
        $(document).on('click', '.edit-btn', function() {
            var id = $(this).data('id');
            var phone = $(this).data('phone');
            var role = $(this).data('role');
            var balance = $(this).data('balance');
            var active = $(this).data('active');
            
            $('#editUserModal input[name="id"]').val(id);
            $('#editUserModal input[name="phone_number"]').val(phone);
            $('#editUserModal select[name="role"]').val(role);
            $('#editUserModal input[name="balance"]').val(balance);
            
            // Set the active switch state
            if (active === 1) {
                $('#editActive').prop('checked', true);
            } else {
                $('#editActive').prop('checked', false);
            }
            
            $('#editUserModal').modal('show');
        });
        
        // Open Change Password Modal
        $(document).on('click', '.password-btn', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var phone = $(this).data('phone');
            
            $('#changePasswordModal input[name="id"]').val(id);
            $('#changePasswordModal #userIdentifier').val(name + ' (' + phone + ')');
            
            $('#changePasswordModal').modal('show');
        });
        
        // Toggle password visibility
        $('#togglePassword').on('click', function() {
            const passwordField = $('#newPassword');
            const passwordFieldType = passwordField.attr('type');
            const eyeIcon = $(this).find('i');
            
            if (passwordFieldType === 'password') {
                passwordField.attr('type', 'text');
                eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordField.attr('type', 'password');
                eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
        
        // Check if passwords match
        $('#confirmPassword').on('input', function() {
            const newPassword = $('#newPassword').val();
            const confirmPassword = $(this).val();
            
            if (newPassword !== confirmPassword) {
                $(this).addClass('is-invalid');
                $('#passwordMismatch').show();
                $('#changePasswordBtn').prop('disabled', true);
            } else {
                $(this).removeClass('is-invalid');
                $('#passwordMismatch').hide();
                $('#changePasswordBtn').prop('disabled', false);
            }
        });
        
        $('#newPassword').on('input', function() {
            const newPassword = $(this).val();
            const confirmPassword = $('#confirmPassword').val();
            
            if (confirmPassword !== '' && newPassword !== confirmPassword) {
                $('#confirmPassword').addClass('is-invalid');
                $('#passwordMismatch').show();
                $('#changePasswordBtn').prop('disabled', true);
            } else if (confirmPassword !== '') {
                $('#confirmPassword').removeClass('is-invalid');
                $('#passwordMismatch').hide();
                $('#changePasswordBtn').prop('disabled', false);
            }
        });
    });
    </script>
</body>
</html>