<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <title>Bottom Navigation Bar with Social Links</title> -->
    <style>
        
        
        .content {
            flex: 1;
            padding: 20px;
        }
        
        .top-icons {
            position: fixed;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 15px;
            z-index: 100;
        }
        
        .icon-button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #2d2f33;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
        }
        
        .social-popup {
            position: absolute;
            top: 50px;
            right: 10px;
            background-color: #2d2f33;
            border-radius: 10px;
            padding: 15px;
            display: none;
            width: 200px;
        }
        
        .social-popup.active {
            display: block;
        }
        
        .social-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            color: white;
            text-decoration: none;
        }
        
        .social-item:hover {
            background-color: #3e4045;
            border-radius: 5px;
        }
        
        .nav-container {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #2d2f33;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            
        }
        
        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: #a0a0a0;
            text-decoration: none;
            font-size: 12px;
            padding: 8px 0;
            width: 20%;
            text-align: center;
        }
        
        .nav-item.active {
            color: #ffffff;
        }
        
        .nav-icon {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .vip-icon {
            color: #ffd700;
        }
    </style>
</head>
<body>
    <div class="top-icons">
        <div class="icon-button" id="headsetButton">🎧</div>
        <div class="icon-button">✉️</div>
    </div>
    
    <div class="social-popup" id="socialPopup">
        <a href="#" class="social-item">
            <span>📱</span>
            <span>WhatsApp</span>
        </a>
        <a href="#" class="social-item">
            <span>🐦</span>
            <span>Twitter</span>
        </a>
        <a href="#" class="social-item">
            <span>👥</span>
            <span>Facebook</span>
        </a>
        <a href="#" class="social-item">
            <span>📸</span>
            <span>Instagram</span>
        </a>
    </div>
    <nav class="nav-container">
        <a href="index.php" class="nav-item active">
            <div class="nav-icon">
                <i class="fas fa-hme">🏠</i>
            </div>
            Home
        </a>
        <a href="profile.php" class="nav-item">
            <div class="nav-icon">
                <i class="fas fa-tsks">📋</i>
            </div>
            Profile
        </a>
        <a href="team.php" class="nav-item">
            <div class="nav-icon">
                <i class="fas fa-usrs">⚙️</i>
            </div>
            Team
        </a>
        <a href="products.php" class="nav-item">
            <div class="nav-icon vip-icon">
                <i class="fas fa-crwn">👑</i>
            </div>
            VIP
        </a>
        <a href="myaccount.php" class="nav-item">
            <div class="nav-icon">
                <i class="fas fa-usr">👤</i>
            </div>
            Me
        </a>
    </nav>
    
    <script>
        // JavaScript to toggle the social popup
        document.getElementById('headsetButton').addEventListener('click', function() {
            document.getElementById('socialPopup').classList.toggle('active');
        });
        
        // Close the popup when clicking elsewhere
        document.addEventListener('click', function(event) {
            const popup = document.getElementById('socialPopup');
            const headsetButton = document.getElementById('headsetButton');
            
            if (!popup.contains(event.target) && event.target !== headsetButton) {
                popup.classList.remove('active');
            }
        });
    </script>
</body>
</html>