<!-- Admin Panel - Banner Slider Management -->
<?php
// Set page title
$page_title = "Banner Slider Management";

// Check if admin is logged in
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: admin_login.php");
//     exit;
// }
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

require_once('../includes/db.php');
include('../includes/menu.php');
// Define upload directory
$upload_dir = "../uploads/banners/";

// Create directory if it doesn't exist
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Add new banner
    if (isset($_POST['add_slider'])) {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $position = $_POST['position'] ?? 'main';
        $order_number = $_POST['order_number'] ?? 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Handle file upload
        $image_name = '';
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['banner_image']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                // Generate unique filename
                $image_name = time() . '_' . basename($_FILES['banner_image']['name']);
                $target_file = $upload_dir . $image_name;
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $target_file)) {
                    // File uploaded successfully
                    $insert_query = "INSERT INTO banner_sliders (title, description, image_name, position, order_number, is_active) 
                                    VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insert_query);
                    $stmt->bind_param("ssssii", $title, $description, $image_name, $position, $order_number, $is_active);
                    
                    if ($stmt->execute()) {
                        $success_message = "Banner slider added successfully!";
                    } else {
                        $error_message = "Error adding banner slider: " . $conn->error;
                    }
                    $stmt->close();
                } else {
                    $error_message = "Error uploading image.";
                }
            } else {
                $error_message = "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
            }
        } else {
            $error_message = "Please select an image for the banner.";
        }
    }
    
    // Update existing banner
    if (isset($_POST['update_slider'])) {
        $id = $_POST['id'] ?? 0;
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $position = $_POST['position'] ?? 'main';
        $order_number = $_POST['order_number'] ?? 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Check if we need to update the image
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] == 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['banner_image']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                // Get the old image to delete
                $old_image_query = "SELECT image_name FROM banner_sliders WHERE id = ?";
                $stmt = $conn->prepare($old_image_query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($old_image);
                $stmt->fetch();
                $stmt->close();
                
                // Delete old image if it exists
                if (!empty($old_image) && file_exists($upload_dir . $old_image)) {
                    unlink($upload_dir . $old_image);
                }
                
                // Generate unique filename for new image
                $image_name = time() . '_' . basename($_FILES['banner_image']['name']);
                $target_file = $upload_dir . $image_name;
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $target_file)) {
                    // Update record with new image
                    $update_query = "UPDATE banner_sliders SET 
                                    title = ?, 
                                    description = ?, 
                                    image_name = ?,
                                    position = ?, 
                                    order_number = ?, 
                                    is_active = ? 
                                    WHERE id = ?";
                    $stmt = $conn->prepare($update_query);
                    $stmt->bind_param("ssssiis", $title, $description, $image_name, $position, $order_number, $is_active, $id);
                } else {
                    $error_message = "Error uploading image.";
                }
            } else {
                $error_message = "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed.";
            }
        } else {
            // Update record without changing the image
            $update_query = "UPDATE banner_sliders SET 
                            title = ?, 
                            description = ?, 
                            position = ?, 
                            order_number = ?, 
                            is_active = ? 
                            WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("sssiii", $title, $description, $position, $order_number, $is_active, $id);
        }
        
        // Execute the update query
        if (isset($stmt) && $stmt->execute()) {
            $success_message = "Banner slider updated successfully!";
        } else {
            $error_message = "Error updating banner slider: " . ($stmt->error ?? $conn->error);
        }
        if (isset($stmt)) $stmt->close();
    }
    
    // Delete banner
    if (isset($_POST['delete_slider'])) {
        $id = $_POST['id'] ?? 0;
        
        // Get the image to delete
        $image_query = "SELECT image_name FROM banner_sliders WHERE id = ?";
        $stmt = $conn->prepare($image_query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($image_name);
        $stmt->fetch();
        $stmt->close();
        
        // Delete the record
        $delete_query = "DELETE FROM banner_sliders WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Delete the image file
            if (!empty($image_name) && file_exists($upload_dir . $image_name)) {
                unlink($upload_dir . $image_name);
            }
            $success_message = "Banner slider deleted successfully!";
        } else {
            $error_message = "Error deleting banner slider: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch all banner sliders for display
$sliders_query = "SELECT id, title, description, image_name, position, order_number, is_active 
                 FROM banner_sliders 
                 ORDER BY position, order_number";
$sliders_result = $conn->query($sliders_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?php echo $page_title; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts - Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/banner-management.css">
</head>
<body>

<div class="container-fluid px-4">
    <h1 class="mt-4"><?php echo $page_title; ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="breadcrumb-item active"><?php echo $page_title; ?></li>
    </ol>
    
    <!-- Display success/error messages -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $success_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?php echo $error_message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-lg-12">
            <!-- Add New Banner Slider Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-plus-circle me-1"></i>
                    Add New Banner Slider
                </div>
                <div class="card-body">
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required placeholder="Enter banner title">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter banner description"></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="position" class="form-label">Position</label>
                                    <select class="form-select" id="position" name="position">
                                        <option value="main">Main Slider</option>
                                        <option value="secondary">Secondary Banner</option>
                                        <option value="sidebar">Sidebar Banner</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="banner_image" class="form-label">Banner Image</label>
                                    <input type="file" class="form-control" id="banner_image" name="banner_image" required accept="image/jpeg,image/png,image/gif,image/webp">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i> Recommended size: 1200x300 pixels. Max file size: 2MB.
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="order_number" class="form-label">Display Order</label>
                                    <input type="number" class="form-control" id="order_number" name="order_number" value="0" min="0" placeholder="Lower numbers display first">
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" checked>
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="add_slider" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-1"></i> Add Banner Slider
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Existing Banner Sliders Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-images me-1"></i>
                    Existing Banner Sliders
                </div>
                <div class="card-body">
                    <?php if ($sliders_result && $sliders_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Position</th>
                                        <th>Order</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($slider = $sliders_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $slider['id']; ?></td>
                                            <td>
                                                <img src="<?php echo '../uploads/banners/' . $slider['image_name']; ?>" 
                                                     alt="<?php echo htmlspecialchars($slider['title']); ?>" 
                                                     style="max-width: 100px; max-height: 60px;">
                                            </td>
                                            <td><?php echo htmlspecialchars($slider['title']); ?></td>
                                            <td>
                                                <span class="position-indicator position-<?php echo $slider['position']; ?>"></span>
                                                <?php 
                                                $position_label = 'Unknown';
                                                switch($slider['position']) {
                                                    case 'main': $position_label = 'Main Slider'; break;
                                                    case 'secondary': $position_label = 'Secondary'; break;
                                                    case 'sidebar': $position_label = 'Sidebar'; break;
                                                }
                                                echo $position_label;
                                                ?>
                                            </td>
                                            <td><?php echo $slider['order_number']; ?></td>
                                            <td>
                                                <?php if ($slider['is_active']): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i> Active
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-ban me-1"></i> Inactive
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary edit-slider" 
                                                        data-bs-toggle="modal" data-bs-target="#editSliderModal" 
                                                        data-id="<?php echo $slider['id']; ?>"
                                                        data-title="<?php echo htmlspecialchars($slider['title']); ?>"
                                                        data-description="<?php echo htmlspecialchars($slider['description']); ?>"
                                                        data-position="<?php echo $slider['position']; ?>"
                                                        data-order="<?php echo $slider['order_number']; ?>"
                                                        data-active="<?php echo $slider['is_active']; ?>"
                                                        data-image="<?php echo $slider['image_name']; ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                
                                                <button type="button" class="btn btn-sm btn-danger delete-slider"
                                                        data-bs-toggle="modal" data-bs-target="#deleteSliderModal"
                                                        data-id="<?php echo $slider['id']; ?>"
                                                        data-title="<?php echo htmlspecialchars($slider['title']); ?>">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            No banner sliders found. Add your first one using the form above.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Slider Modal -->
<div class="modal fade" id="editSliderModal" tabindex="-1" aria-labelledby="editSliderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSliderModalLabel"><i class="fas fa-edit me-2"></i>Edit Banner Slider</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="edit_title" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_description" class="form-label">Description</label>
                                <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_position" class="form-label">Position</label>
                                <select class="form-select" id="edit_position" name="position">
                                    <option value="main">Main Slider</option>
                                    <option value="secondary">Secondary Banner</option>
                                    <option value="sidebar">Sidebar Banner</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_banner_image" class="form-label">Banner Image</label>
                                <input type="file" class="form-control" id="edit_banner_image" name="banner_image" accept="image/jpeg,image/png,image/gif,image/webp">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i> Leave empty to keep current image. Recommended size: 1200x300 pixels.
                                </div>
                                <div class="mt-2" id="current_image_container">
                                    <p><i class="fas fa-image me-1"></i> Current image:</p>
                                    <img id="current_image" src="" alt="Current banner" style="max-width: 100%; max-height: 150px;">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="edit_order_number" class="form-label">Display Order</label>
                                <input type="number" class="form-control" id="edit_order_number" name="order_number" value="0" min="0">
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="edit_is_active" name="is_active">
                                <label class="form-check-label" for="edit_is_active">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" name="update_slider" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Slider Modal -->
<div class="modal fade" id="deleteSliderModal" tabindex="-1" aria-labelledby="deleteSliderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteSliderModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the banner slider "<strong><span id="delete_slider_title"></span></strong>"?</p>
                <div class="alert alert-danger mb-0">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    This action cannot be undone! All data associated with this banner will be permanently removed.
                </div>
            </div>
            <div class="modal-footer">
                <form action="" method="post">
                    <input type="hidden" name="id" id="delete_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" name="delete_slider" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Delete Permanently
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap and jQuery Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript for Modals and UI Enhancements -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable Bootstrap tooltips and popovers
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Auto-close alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Edit slider modal
    const editButtons = document.querySelectorAll('.edit-slider');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const title = this.getAttribute('data-title');
            const description = this.getAttribute('data-description');
            const position = this.getAttribute('data-position');
            const order = this.getAttribute('data-order');
            const active = this.getAttribute('data-active');
            const image = this.getAttribute('data-image');
            
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_title').value = title;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_position').value = position;
            document.getElementById('edit_order_number').value = order;
            document.getElementById('edit_is_active').checked = active === '1';
            
            // Show current image
            const currentImageEl = document.getElementById('current_image');
            if (image) {
                currentImageEl.src = '../uploads/banners/' + image;
                document.getElementById('current_image_container').style.display = 'block';
            } else {
                document.getElementById('current_image_container').style.display = 'none';
            }
            
            // Update modal title with banner name
            document.getElementById('editSliderModalLabel').innerHTML = 
                '<i class="fas fa-edit me-2"></i>Edit Banner: ' + title;
        });
    });
    
    // Delete slider modal
    const deleteButtons = document.querySelectorAll('.delete-slider');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const title = this.getAttribute('data-title');
            
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_slider_title').textContent = title;
        });
    });
    
    // Preview image before upload for new banner
    document.getElementById('banner_image').addEventListener('change', function() {
        previewImage(this, 'new-image-preview');
    });
    
    // Preview image before upload for edit banner
    document.getElementById('edit_banner_image').addEventListener('change', function() {
        previewImage(this, 'current_image');
    });
    
    // Function to preview image
    function previewImage(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const preview = document.getElementById(previewId);
                if (preview) {
                    preview.src = e.target.result;
                    if (previewId === 'new-image-preview') {
                        preview.style.display = 'block';
                    }
                }
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Add image preview container for new banner if not exists
    if (!document.getElementById('new-image-preview-container')) {
        const bannerImageInput = document.getElementById('banner_image');
        if (bannerImageInput) {
            const container = document.createElement('div');
            container.id = 'new-image-preview-container';
            container.className = 'mt-2';
            container.style.display = 'none';
            container.innerHTML = `
                <p><i class="fas fa-image me-1"></i> Image Preview:</p>
                <img id="new-image-preview" src="" alt="Banner preview" style="max-width: 100%; max-height: 150px; display: none;">
            `;
            bannerImageInput.parentNode.appendChild(container);
            
            bannerImageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    container.style.display = 'block';
                } else {
                    container.style.display = 'none';
                }
            });
        }
    }
    
    // Add form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});
</script>
</body>
</html>