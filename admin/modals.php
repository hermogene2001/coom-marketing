<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="changePasswordModalLabel"><i class="fas fa-key me-2"></i>Change Password</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../actions/change_password.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <input type="password" class="form-control bg-secondary text-light" id="currentPassword" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control bg-secondary text-light" id="newPassword" name="new_password" required>
                        <div class="form-text text-muted">Minimum 8 characters with at least 1 number</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control bg-secondary text-light" id="confirmPassword" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Agent Modal -->
<div class="modal fade" id="createAgentModal" tabindex="-1" aria-labelledby="createAgentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="createAgentModalLabel"><i class="fas fa-user-plus me-2"></i>Create New Agent</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../actions/create_agent.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="agentPhone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control bg-secondary text-light" id="agentPhone" name="phone_number" pattern="[0-9]{10}" required>
                        <div class="form-text text-muted">Format: 07XXXXXXXX</div>
                    </div>
                    <div class="mb-3">
                        <label for="agentPassword" class="form-label">Password</label>
                        <input type="password" class="form-control bg-secondary text-light" id="agentPassword" name="password" required>
                        <div class="form-text text-muted">Default password for the agent</div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Create Agent</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="addProductModalLabel"><i class="fas fa-box-open me-2"></i>Add New Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="../actions/add_product.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productName" class="form-label">Product Name</label>
                            <input type="text" class="form-control bg-secondary text-light" id="productName" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="productImage" class="form-label">Product Image</label>
                            <input type="file" class="form-control bg-secondary text-light" id="productImage" name="image" accept="image/*" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Price (RWF)</label>
                            <input type="number" class="form-control bg-secondary text-light" id="price" name="price" min="1" step="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="profitRate" class="form-label">Profit Rate (%)</label>
                            <input type="number" class="form-control bg-secondary text-light" id="profitRate" name="profit_rate" min="1" max="100" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="dailyEarning" class="form-label">Daily Earning (RWF)</label>
                            <input type="number" class="form-control bg-secondary text-light" id="dailyEarning" name="daily_earning" readonly>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="cycle" class="form-label">Cycle (Days)</label>
                            <input type="number" class="form-control bg-secondary text-light" id="cycle" name="cycle" min="1" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="minWithdraw" class="form-label">Min Withdrawal (RWF)</label>
                            <input type="number" class="form-control bg-secondary text-light" id="minWithdraw" name="min_withdraw" min="3000" step="0.01" value="3000.00">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="productStatus" class="form-label">Status</label>
                            <select class="form-select bg-secondary text-light" id="productStatus" name="status" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="referralLevel1" class="form-label">Level 1 Referral (%)</label>
                            <input type="number" class="form-control bg-secondary text-light" id="referralLevel1" name="referral_level1_percentage" min="0" max="100" step="0.01" value="3.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="referralLevel2" class="form-label">Level 2 Referral (%)</label>
                            <input type="number" class="form-control bg-secondary text-light" id="referralLevel2" name="referral_level2_percentage" min="0" max="100" step="0.01" value="1.00">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Description</label>
                        <textarea class="form-control bg-secondary text-light" id="productDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const priceInput = document.getElementById('price');
    const profitRateInput = document.getElementById('profitRate');
    const dailyEarningInput = document.getElementById('dailyEarning');
    
    function calculateDailyEarning() {
        const price = parseFloat(priceInput.value) || 0;
        const rate = parseFloat(profitRateInput.value) || 0;
        const dailyEarning = (price * rate / 100).toFixed(2);
        dailyEarningInput.value = dailyEarning;
    }
    
    priceInput.addEventListener('input', calculateDailyEarning);
    profitRateInput.addEventListener('input', calculateDailyEarning);
    
    // Initialize calculation on page load
    calculateDailyEarning();
});
</script>