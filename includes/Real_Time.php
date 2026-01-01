<style>
        :root {
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
            --card-border: #334155;
            --text-color: #e2e8f0;
            --primary-color: #3b82f6;
            --morning-color: #f59e0b;
            --afternoon-color: #10b981;
            --evening-color: #8b5cf6;
            --night-color: #6366f1;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin-right: 0;
            padding: 20px;
            /* display: flex; */
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .time-container {
            text-align: center;
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
            border: 1px solid var(--card-border);
            max-width: 500px;
            width: 100%;
        }
        
        h3 {
            font-size: 1.5rem;
            margin-bottom: 15px;
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        #current-time {
            font-size: 2.5rem;
            color: var(--primary-color);
            font-weight: bold;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }
        
        .message {
            margin-top: 20px;
            font-size: 1.1rem;
            padding: 12px;
            border-radius: 8px;
            background-color: rgba(59, 130, 246, 0.1);
        }
        
        .morning {
            color: var(--morning-color);
            background-color: rgba(245, 158, 11, 0.1);
        }
        
        .afternoon {
            color: var(--afternoon-color);
            background-color: rgba(16, 185, 129, 0.1);
        }
        
        .evening {
            color: var(--evening-color);
            background-color: rgba(139, 92, 246, 0.1);
        }
        
        .night {
            color: var(--night-color);
            background-color: rgba(99, 102, 241, 0.1);
        }
        
        .logo {
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
    <script>
        function updateTime(initialTime) {
            let now = new Date(initialTime);
            setInterval(() => {
                now.setSeconds(now.getSeconds() + 1);
                const timeString = now.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit',
                    hour12: true 
                });
                document.getElementById('current-time').textContent = timeString;
            }, 1000);
        }

        function displayGreeting() {
            const hours = new Date().getHours();
            const message = document.querySelector('.message');
            let greeting, timeClass;
            
            if (hours >= 5 && hours < 12) {
                greeting = "Good Morning! Start your investment journey with positivity!";
                timeClass = "morning";
            } else if (hours >= 12 && hours < 17) {
                greeting = "Good Afternoon! Your investments are working hard for you!";
                timeClass = "afternoon";
            } else if (hours >= 17 && hours < 21) {
                greeting = "Good Evening! Review your portfolio's daily progress!";
                timeClass = "evening";
            } else {
                greeting = "Good Night! Your investments continue growing while you rest!";
                timeClass = "night";
            }
            
            message.textContent = greeting;
            message.className = `message ${timeClass}`;
        }
    </script>
</head>
<body onload="displayGreeting()">
    <div class="time-container">
        <div class="logo">Coom Marketing</div>
        <h3>
            <i class="fas fa-clock"></i>
            Current Time: <span id="current-time"></span>
        </h3>
        <div class="message"></div>
    </div>

    <!-- Font Awesome for icons -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script> -->
    
    <script>
        updateTime(<?php echo json_encode(date('Y-m-d\TH:i:s')); ?>);
    </script>
