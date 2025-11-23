<?php
// Increase PHP limits for large file uploads
ini_set('upload_max_filesize', '500M');
ini_set('post_max_size', '500M');
ini_set('max_execution_time', '300');
ini_set('max_input_time', '300');
ini_set('memory_limit', '512M');

include("../db.php");
session_start();

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$success_message = '';
$error_message = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['video_file'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    
    // File upload handling
    $upload_dir = '../uploads/videos/';
    $thumbnail_dir = '../uploads/thumbnails/';
    
    // Create directories if they don't exist
    if(!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    if(!is_dir($thumbnail_dir)) mkdir($thumbnail_dir, 0777, true);
    
    $video_file = $_FILES['video_file'];
    $thumbnail_file = $_FILES['thumbnail_file'];
    
    // Check for upload errors
    if($video_file['error'] !== UPLOAD_ERR_OK) {
        $error_message = "Video upload error: " . $video_file['error'];
    } elseif($video_file['size'] > 500 * 1024 * 1024) { // 500MB limit
        $error_message = "Video file too large. Maximum size is 500MB.";
    } else {
        // Generate unique filenames
        $video_filename = uniqid() . '_' . basename($video_file['name']);
        $thumbnail_filename = uniqid() . '_' . basename($thumbnail_file['name']);
        
        $video_path = $upload_dir . $video_filename;
        $thumbnail_path = $thumbnail_dir . $thumbnail_filename;
        
        // Move uploaded files
        if(move_uploaded_file($video_file['tmp_name'], $video_path) &&
           move_uploaded_file($thumbnail_file['tmp_name'], $thumbnail_path)) {
            
            // Save to database
            try {
                $video_url = 'uploads/videos/' . $video_filename;
                $thumbnail_url = 'uploads/thumbnails/' . $thumbnail_filename;
                
                $stmt = $pdo->prepare("INSERT INTO videos (title, description, thumbnail_url, video_url, category, size) VALUES (?, ?, ?, ?, ?, ?)");
                $file_size = round($video_file['size'] / (1024 * 1024), 2) . 'MB';
                $stmt->execute([$title, $description, $thumbnail_url, $video_url, 'Movie', $file_size]);
                
                $success_message = "Video uploaded successfully! Size: " . $file_size;
            } catch(PDOException $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        } else {
            $error_message = "Failed to move uploaded files.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Large Videos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0; }
        .message { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h2>Upload Large Videos (Up to 500MB)</h2>
    
    <?php if($success_message): ?>
        <div class="message success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if($error_message): ?>
        <div class="message error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Video Title</label>
            <input type="text" name="title" required>
        </div>
        
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" required></textarea>
        </div>
        
        <div class="form-group">
            <label>Video File (Max: 500MB)</label>
            <input type="file" name="video_file" accept="video/*" required>
        </div>
        
        <div class="form-group">
            <label>Thumbnail Image</label>
            <input type="file" name="thumbnail_file" accept="image/*" required>
        </div>
        
        <button type="submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px;">
            Upload Video
        </button>
    </form>
    
    <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
        <h3>📋 Upload Limits:</h3>
        <p>• Maximum file size: 500MB</p>
        <p>• Supported formats: MP4, AVI, MOV, WMV</p>
        <p>• Recommended: Use URL method for larger files</p>
    </div>
    
    <p><a href="dashboard.php">← Back to Dashboard</a></p>
</body>
</html>