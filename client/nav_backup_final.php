<?php
// Modern Navigation Bar for COOM Trading
// This is included in the main pages to provide consistent navigation
?>

<style>
    .nav-container {
        position: relative;
        top: 0;
        width: 100%;
        background-color: var(--card-bg);
        display: flex;
        justify-content: space-around;
        padding: 12px 0;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.2);
        z-index: 1000;
        border-bottom: 1px solid var(--border-color);
        height: 50px;
    }
    
    .nav-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 11px;
        padding: 8px 0;
        width: 20%;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .nav-item.active {
        color: var(--accent-color-light);
    }
    
    .nav-item:hover {
        color: var(--accent-color-light);
        transform: translateY(-2px);
    }
    
    .nav-icon {
        font-size: 22px;
        margin-bottom: 4px;
        transition: all 0.3s ease;
    }
    
    .nav-item:hover .nav-icon {
        transform: scale(1.1);
    }
    
    .nav-badge {
        position: absolute;
        top: -5px;
        right: 35%;
        background-color: var(--accent-color);
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Top right icons */
    .top-right-icons {
        position: fixed;
        top: 20px;
        right: 20px;
        display: flex;
        gap: 12px;
        z-index: 1001;
    }
    
    .top-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background-color: var(--card-bg);
        color: var(--text-color);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px;
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }
    
    .top-icon:hover {
        background-color: var(--accent-color-light);
        color: white;
        transform: scale(1.1);
    }
    
    .social-popup {
        position: absolute;
        top: 55px;
        right: 0;
        background-color: var(--card-bg);
        border-radius: 12px;
        padding: 15px;
        display: none;
        width: 220px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        border: 1px solid var(--border-color);
        z-index: 1002;
    }
    
    .social-popup.active {
        display: block;
    }
    
    .social-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px;
        color: var(--text-color);
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.2s ease;
    }
    
    .social-item:hover {
        background-color: var(--secondary-bg);
        color: var(--accent-color-light);
    }
    
    /* Responsive design */
    @media (max-width: 768px) {
        .nav-container {
            padding: 10px 0;
        }
        
        .nav-icon {
            font-size: 20px;
        }
        
        .nav-item {
            font-size: 10px;
        }
    }
</style>

<div class="top-right-icons">
    <div class="top-icon" id="supportButton">🎧</div>
    <div class="top-icon" id="messageButton">✉️</div>
</div>

<div class="social-popup" id="socialPopup">
    <a href="#" class="social-item">
        <span>📱</span>
        <span>WhatsApp Support</span>
    </a>
    <a href="#" class="social-item">
        <span>🐦</span>
        <span>Twitter</span>
    </a>
    <a href="#" class="social-item">
        <span>👥</span>
        <span>Facebook Group</span>
    </a>
    <a href="#" class="social-item">
        <span>📸</span>
        <span>Instagram</span>
    </a>
</div>

<nav class="nav-container">
    <a href="index.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">
        <div class="nav-icon">🏠</div>
        <div>Home</div>
    </a>
    <a href="products.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : ''; ?>">
        <div class="nav-icon">📊</div>
        <div>Invest</div>
    </a>
    <a href="recharge.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'recharge.php') ? 'active' : ''; ?>">
        <div class="nav-icon">💰</div>
        <div>Deposit</div>
    </a>
    <a href="withdraw.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'withdraw.php') ? 'active' : ''; ?>">
        <div class="nav-icon">💸</div>
        <div>Withdraw</div>
    </a>
    <a href="account.php" class="nav-item <?php echo (basename($_SERVER['PHP_SELF']) == 'account.php') ? 'active' : ''; ?>">
        <div class="nav-icon">👤</div>
        <div>Account</div>
    </a>
</nav>

<script>
    // JavaScript to toggle the social popup
    document.getElementById('supportButton').addEventListener('click', function() {
        document.getElementById('socialPopup').classList.toggle('active');
    });
    
    // Close the popup when clicking elsewhere
    document.addEventListener('click', function(event) {
        const popup = document.getElementById('socialPopup');
        const supportButton = document.getElementById('supportButton');
        
        if (!popup.contains(event.target) && event.target !== supportButton) {
            popup.classList.remove('active');
        }
    });
    
    // Add ripple effect to navigation items
    const navItems = document.querySelectorAll('.nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Remove active class from all items
            navItems.forEach(nav => nav.classList.remove('active'));
            // Add active class to clicked item
            this.classList.add('active');
        });
    });
</script>