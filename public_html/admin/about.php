<?php
session_start();
require_once('../includes/db.php');

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
} else {
    $msg = $error = '';
    $about_data = null;

    // Handle form submission
    if (isset($_POST['submit'])) {
        $content = $_POST['content'];

        $check_sql = "SELECT * FROM about_page";
        $check_query = $conn->query($check_sql);
        $existing_entry = $check_query->fetch_assoc();

        if ($existing_entry) {
            $sql = "UPDATE about_page SET content = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $content, $existing_entry['id']);
            if ($stmt->execute()) {
                $msg = "About page content updated successfully!";
            } else {
                $error = "Failed to update about page content.";
            }
            $stmt->close();
        } else {
            $sql = "INSERT INTO about_page (content) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $content);
            if ($stmt->execute()) {
                $msg = "About page content created successfully!";
            } else {
                $error = "Failed to create about page content.";
            }
            $stmt->close();
        }
    }

    // Fetch existing content
    $sql = "SELECT * FROM about_page";
    $query = $conn->query($sql);
    if ($query) {
        $about_data = $query->fetch_assoc();
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Harbor Investment | Admin About Page</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.2/css/all.min.css" rel="stylesheet">
    <!-- TinyMCE Editor -->
    <script src="https://cdn.tiny.cloud/1/mrts9a751j3cffkb18zq8kucj2uw1btmykp5yodw337q1mw2/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
    <style>
        :root {
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
            --card-border: #334155;
            --text-color: #e2e8f0;
            --text-muted: #94a3b8;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
            min-height: 100vh;
        }
        
        .navbar {
            background-color: var(--card-bg) !important;
            border-bottom: 1px solid var(--card-border);
        }
        
        .content-wrapper {
            padding: 20px;
            background-color: var(--dark-bg);
        }
        
        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: rgba(30, 41, 59, 0.8);
            border-bottom: 1px solid var(--card-border);
            font-weight: 600;
            padding: 15px 20px;
        }
        
        .form-control, .form-select {
            background-color: #1e293b;
            border: 1px solid #334155;
            color: var(--text-color);
        }
        
        .form-control:focus {
            background-color: #1e293b;
            color: var(--text-color);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        
        .alert-success {
            background-color: rgba(21, 128, 61, 0.2);
            border-color: rgba(21, 128, 61, 0.3);
            color: #86efac;
        }
        
        .alert-danger {
            background-color: rgba(220, 38, 38, 0.2);
            border-color: rgba(220, 38, 38, 0.3);
            color: #fca5a5;
        }
        
        .page-title {
            color: var(--text-color);
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        /* TinyMCE Overrides */
        .tox-tinymce {
            border-radius: 6px !important;
            border: 1px solid var(--card-border) !important;
        }
        
        .tox .tox-toolbar, .tox .tox-toolbar__overflow, .tox .tox-toolbar__primary {
            background: var(--card-bg) !important;
            border-bottom: 1px solid var(--card-border) !important;
        }
    </style>
</head>

<body>
    <?php include('../includes/menu.php'); ?>
    
    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h2 class="page-title">
                        <i class="fas fa-info-circle me-2"></i>About Page Content
                    </h2>
                    
                    <div class="card">
                        <div class="card-header">
                            Edit About Page Content
                        </div>
                        <div class="card-body">
                            <?php if ($error) : ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <?php echo htmlentities($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php elseif ($msg) : ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo htmlentities($msg); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="post" name="aboutForm">
                                <div class="mb-3">
                                    <label for="content" class="form-label">Content</label>
                                    <textarea class="form-control" name="content" id="content" rows="10" required><?php echo htmlentities($about_data['content'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button class="btn btn-primary" name="submit" type="submit">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- TinyMCE Initialization -->
    <script>
        tinymce.init({
            selector: '#content',
            plugins: 'advlist autolink lists link image charmap preview anchor',
            toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image',
            skin: 'oxide-dark',
            content_css: 'dark',
            height: 500,
            menubar: false,
            statusbar: false
        });
    </script>
</body>

</html>