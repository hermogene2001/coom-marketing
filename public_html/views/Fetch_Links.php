<?php
// Include database connection
include '../includes/db.php'; 

// Query to fetch social media links
$sql = "SELECT facebook, twitter, whatsapp, telegram FROM social_links LIMIT 1"; // Ensure only one record is fetched
$stmt = $conn->prepare($sql);
$stmt->execute();
$stmt->bind_result($facebookLink, $twitterLink, $whatsappLink, $telegramLink);
$stmt->fetch();
$stmt->close();

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media Links</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .social-media-links a {
            margin: 0 10px;
            font-size: 24px;
            color: inherit;
            text-decoration: none;
        }
        .social-media-links a:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="social-media-links">
        <?php if (!empty($facebookLink)): ?>
            <a href="<?php echo htmlspecialchars($facebookLink); ?>" target="_blank" aria-label="Facebook">
                <i class="fab fa-facebook-f" style="color: #3b5998;"></i>
            </a>
        <?php endif; ?>

        <?php if (!empty($twitterLink)): ?>
            <a href="<?php echo htmlspecialchars($twitterLink); ?>" target="_blank" aria-label="Twitter">
                <i class="fab fa-twitter" style="color: #1da1f2;"></i>
            </a>
        <?php endif; ?>

        <?php if (!empty($whatsappLink)): ?>
            <a href="<?php echo htmlspecialchars($whatsappLink); ?>" target="_blank" aria-label="WhatsApp">
                <i class="fab fa-whatsapp" style="color: green;"></i>
            </a>
        <?php endif; ?>

        <?php if (!empty($telegramLink)): ?>
            <a href="<?php echo htmlspecialchars($telegramLink); ?>" target="_blank" aria-label="Telegram">
                <i class="fab fa-telegram" style="color: #0088cc;"></i>
            </a>
        <?php endif; ?>
    </div>
</body>
</html>
