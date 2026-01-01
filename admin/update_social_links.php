<?php

// Include database connection
include '../includes/db.php';
// include '../includes/menu.php';

// Initialize variables
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $whatsapp = trim($_POST['whatsapp']);
    $telegram = trim($_POST['telegram']);
    $facebook = trim($_POST['facebook']);
    $twitter = trim($_POST['twitter']);
    $instagram = trim($_POST['instagram']);

    // Validate inputs
    if (empty($whatsapp)) {
        $error = 'WhatsApp link is required!';
    } else {
        $query = "UPDATE social_links SET whatsapp = ?, telegram = ?, facebook = ?, twitter = ?, instagram = ? WHERE id = 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sssss', $whatsapp, $telegram, $facebook, $twitter, $instagram);

        if ($stmt->execute()) {
            $success = 'Social links updated successfully!';
        } else {
            $error = 'Failed to update social links. Please try again.';
        }
    }
}

// Fetch current social links
$query = "SELECT * FROM social_links WHERE id = 1";
$result = $conn->query($query);
$currentLinks = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media - Coom Marketing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            --danger-color: #ef4444;
            --whatsapp-color: #25D366;
            --telegram-color: #0088cc;
            --facebook-color: #1877f2;
            --twitter-color: #1DA1F2;
            --instagram-color: #E1306C;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
        }
        
        .container {
            padding-top: 2rem;
            max-width: 800px;
        }
        
        h2 {
            color: var(--text-color);
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-control {
            background-color: #2d3748;
            border: 1px solid var(--card-border);
            color: var(--text-color);
            padding: 0.75rem;
            border-radius: 8px;
        }
        
        .form-control:focus {
            background-color: #2d3748;
            color: var(--text-color);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        
        label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            width: 100%;
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        
        .alert {
            border-radius: 8px;
        }
        
        .fa-whatsapp { color: var(--whatsapp-color); }
        .fa-telegram { color: var(--telegram-color); }
        .fa-facebook { color: var(--facebook-color); }
        .fa-twitter { color: var(--twitter-color); }
        .fa-instagram { color: var(--instagram-color); }
        
        @media (min-width: 768px) {
            .btn-primary {
                width: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-share-alt me-2"></i>Social Media Links</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp</label>
                <input type="url" id="whatsapp" name="whatsapp" class="form-control" 
                       value="<?php echo htmlspecialchars($currentLinks['whatsapp'] ?? ''); ?>" 
                       placeholder="https://wa.me/..." required>
            </div>

            <div class="form-group">
                <label for="telegram"><i class="fab fa-telegram"></i> Telegram</label>
                <input type="url" id="telegram" name="telegram" class="form-control" 
                       value="<?php echo htmlspecialchars($currentLinks['telegram'] ?? ''); ?>" 
                       placeholder="https://t.me/...">
            </div>

            <div class="form-group">
                <label for="facebook"><i class="fab fa-facebook"></i> Facebook</label>
                <input type="url" id="facebook" name="facebook" class="form-control" 
                       value="<?php echo htmlspecialchars($currentLinks['facebook'] ?? ''); ?>" 
                       placeholder="https://facebook.com/...">
            </div>

            <div class="form-group">
                <label for="twitter"><i class="fab fa-twitter"></i> Twitter</label>
                <input type="url" id="twitter" name="twitter" class="form-control" 
                       value="<?php echo htmlspecialchars($currentLinks['twitter'] ?? ''); ?>" 
                       placeholder="https://twitter.com/...">
            </div>

            <div class="form-group">
                <label for="instagram"><i class="fab fa-instagram"></i> Instagram</label>
                <input type="url" id="instagram" name="instagram" class="form-control" 
                       value="<?php echo htmlspecialchars($currentLinks['instagram'] ?? ''); ?>" 
                       placeholder="https://instagram.com/...">
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Links
                </button>
            </div>
        </form>
    </div>