<?php
// session_start();
// if ($_SESSION['role'] !== 'client') {
//     header("Location: ../index.php");
//     exit;
// }
// Include your database connection
include('../includes/db.php');

// Fetch data from the database
$sql = "SELECT * FROM about_page";
$result = $conn->query($sql);
$aboutData = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Harbor Investment</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
            --card-border: #334155;
            --text-color: #e2e8f0;
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --accent-color: #38bdf8;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--dark-bg);
            color: var(--text-color);
        }
        
        .about-section {
            padding: 100px 0;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            position: relative;
            overflow: hidden;
        }
        
        .about-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 30%, rgba(56, 189, 248, 0.1) 0%, transparent 50%);
        }
        
        .about-section h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 25px;
            background: linear-gradient(to right, var(--accent-color), var(--primary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .about-section p {
            font-size: 1.2rem;
            line-height: 1.8;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
        }
        
        .features-section {
            padding: 80px 0;
            background-color: var(--dark-bg);
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 50px;
            text-align: center;
            color: var(--text-color);
        }
        
        .feature-card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 30px;
            height: 100%;
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            border-color: var(--primary-color);
        }
        
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 20px;
            color: var(--accent-color);
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--text-color);
        }
        
        .feature-card p {
            font-size: 1rem;
            line-height: 1.7;
            opacity: 0.8;
        }
        
        @media (max-width: 768px) {
            .about-section {
                padding: 60px 0;
            }
            
            .about-section h1 {
                font-size: 2.2rem;
            }
            
            .about-section p {
                font-size: 1rem;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php //include 'nav.php'; ?>
    
    <!-- About Section -->
    <section class="about-section py-5" style="background-color: var(--dark-bg);">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <h1 class="text-light mb-4">
                        <i class="fas fa-info-circle text-primary me-2"></i><?php echo _('About Harbor Investment'); ?>
                    </h1>
                    
                    <div class="about-content p-4 rounded" style="background-color: var(--card-bg); border-left: 4px solid var(--primary-color);">
                        <?php if ($aboutData): ?>
                            <div class="lead text-light">
                                <?php 
                                // Check if content contains HTML tags
                                if ($aboutData['content'] !== strip_tags($aboutData['content'])) {
                                    echo _($aboutData['content']); // Output with HTML and translation
                                } else {
                                    echo nl2br(_(htmlspecialchars($aboutData['content']))); // Escape and translate plain text
                                }
                                ?>
                            </div>
                        <?php else: ?>
                            <div class="lead text-muted">
                                <p><?php echo _("We're building the future of investment opportunities."); ?></p>
                                <p><?php echo _("Our platform empowers investors with cutting-edge tools and secure, profitable investment options."); ?></p>
                                <p><?php echo _("Join us on this journey to financial growth and stability."); ?></p>
                                <p style="text-align: center;"><strong><em><?php echo _("Patience and positivity matter. The best are yet to come; never give up"); ?></em></strong></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- Features Section -->
    <!--<section id="features" class="features-section">-->
    <!--    <div class="container">-->
    <!--        <h2 class="section-title">Why Choose Harbor Investment</h2>-->
    <!--        <div class="row g-4">-->
    <!--            <div class="col-md-4">-->
    <!--                <div class="feature-card">-->
    <!--                    <div class="feature-icon">-->
    <!--                        <i class="fas fa-shield-alt"></i>-->
    <!--                    </div>-->
    <!--                    <h3>Secure Investments</h3>-->
    <!--                    <p>Your capital is protected with our advanced security measures and risk management strategies.</p>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--            <div class="col-md-4">-->
    <!--                <div class="feature-card">-->
    <!--                    <div class="feature-icon">-->
    <!--                        <i class="fas fa-chart-line"></i>-->
    <!--                    </div>-->
    <!--                    <h3>Daily Returns</h3>-->
    <!--                    <p>Experience consistent daily returns on your investments with our proven strategies.</p>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--            <div class="col-md-4">-->
    <!--                <div class="feature-card">-->
    <!--                    <div class="feature-icon">-->
    <!--                        <i class="fas fa-headset"></i>-->
    <!--                    </div>-->
    <!--                    <h3>24/7 Support</h3>-->
    <!--                    <p>Our dedicated support team is available around the clock to assist you with any inquiries.</p>-->
    <!--                </div>-->
    <!--            </div>-->
    <!--        </div>-->
    <!--    </div>-->
    <!--</section>-->
    
    <?php // include 'footer.php'; ?>
    
    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
