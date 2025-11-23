<?php 
session_start();
if(!isset($_SESSION['admin_logged_in'])) {
    $_SESSION['admin_logged_in'] = true;
}

include("../db.php");

// Initialize variables
$success_message = '';
$error_message = '';

// Create uploads directory if it doesn't exist
$upload_dir = "../uploads/";
$dirs = ['posters/', 'videos/', 'thumbnails/'];
foreach($dirs as $dir) {
    if (!file_exists($upload_dir . $dir)) {
        mkdir($upload_dir . $dir, 0777, true);
    }
}

// Handle featured movie upload
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_featured'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? 'Movie');
    
    $poster_path = '';
    $video_path = '';
    $error = '';

    // Handle file upload or URL
    if(!empty($_FILES['poster']['name'])) {
        $poster_name = uniqid() . '_' . basename($_FILES['poster']['name']);
        $poster_path = 'uploads/posters/' . $poster_name;
        if(!move_uploaded_file($_FILES['poster']['tmp_name'], '../' . $poster_path)) {
            $error = "Failed to upload poster";
        }
    } elseif(!empty($_POST['poster_url'])) {
        $poster_path = trim($_POST['poster_url']);
    }

    if(!empty($_FILES['video']['name'])) {
        $video_name = uniqid() . '_' . basename($_FILES['video']['name']);
        $video_path = 'uploads/videos/' . $video_name;
        if(!move_uploaded_file($_FILES['video']['tmp_name'], '../' . $video_path)) {
            $error = "Failed to upload video";
        }
    } elseif(!empty($_POST['video_url'])) {
        $video_path = trim($_POST['video_url']);
    }

    if(empty($error) && !empty($title) && !empty($poster_path) && !empty($video_path)) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM featured_movies")->fetchColumn();
            if($count >= 5) {
                $error_message = "Maximum 5 featured movies allowed.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO featured_movies (title, description, poster_url, video_url, category) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $poster_path, $video_path, $category]);
                $success_message = "🎬 Featured movie added successfully!";
            }
        } catch (PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    } else {
        $error_message = $error ?: "Please provide all required files or URLs";
    }
}

// Handle trailer upload
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_trailer'])) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    $thumbnail_path = '';
    $video_path = '';
    $error = '';

    if(!empty($_FILES['thumbnail']['name'])) {
        $thumbnail_name = uniqid() . '_' . basename($_FILES['thumbnail']['name']);
        $thumbnail_path = 'uploads/thumbnails/' . $thumbnail_name;
        if(!move_uploaded_file($_FILES['thumbnail']['tmp_name'], '../' . $thumbnail_path)) {
            $error = "Failed to upload thumbnail";
        }
    } elseif(!empty($_POST['thumbnail_url'])) {
        $thumbnail_path = trim($_POST['thumbnail_url']);
    }

    if(!empty($_FILES['trailer_video']['name'])) {
        $video_name = uniqid() . '_' . basename($_FILES['trailer_video']['name']);
        $video_path = 'uploads/videos/' . $video_name;
        if(!move_uploaded_file($_FILES['trailer_video']['tmp_name'], '../' . $video_path)) {
            $error = "Failed to upload video";
        }
    } elseif(!empty($_POST['trailer_video_url'])) {
        $video_path = trim($_POST['trailer_video_url']);
    }

    if(empty($error) && !empty($title) && !empty($thumbnail_path) && !empty($video_path)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO trailers (title, description, thumbnail_url, video_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $description, $thumbnail_path, $video_path]);
            $success_message = "🎥 Trailer uploaded successfully!";
        } catch (PDOException $e) {
            $error_message = "Error: " . $e->getMessage();
        }
    } else {
        $error_message = $error ?: "Please provide all required files or URLs";
    }
}

// Get data
try {
    $featured_count = $pdo->query("SELECT COUNT(*) FROM featured_movies")->fetchColumn();
    $trailers_count = $pdo->query("SELECT COUNT(*) FROM trailers")->fetchColumn();
    $total_views = $pdo->query("SELECT SUM(views) FROM featured_movies")->fetchColumn() ?? 0;
    $total_downloads = $pdo->query("SELECT SUM(downloads) FROM featured_movies")->fetchColumn() ?? 0;
    $featured_movies = $pdo->query("SELECT * FROM featured_movies ORDER BY created_at DESC")->fetchAll();
    $all_trailers = $pdo->query("SELECT * FROM trailers ORDER BY uploaded_at DESC")->fetchAll();
} catch (PDOException $e) {
    $featured_count = $trailers_count = $total_views = $total_downloads = 0;
    $featured_movies = $all_trailers = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ARNET Admin Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #e63600;
      --secondary-color: #2c3e50;
      --success-color: #28a745;
      --danger-color: #dc3545;
      --warning-color: #ffc107;
      --info-color: #17a2b8;
      --light-bg: #f8f9fa;
      --dark-bg: #343a40;
    }

    .dashboard-container {
      padding: 20px;
      background: var(--light-bg);
      min-height: 100vh;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      margin: 25px 0;
    }

    .stat-card {
      background: white;
      padding: 25px;
      border-radius: 15px;
      text-align: center;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      border-left: 5px solid var(--primary-color);
      transition: transform 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-5px);
    }

    .stat-number {
      font-size: 2.8em;
      font-weight: bold;
      color: var(--primary-color);
      margin-bottom: 10px;
    }

    .stat-label {
      color: var(--secondary-color);
      font-size: 1.1em;
      font-weight: 500;
    }

    .stat-icon {
      font-size: 2em;
      margin-bottom: 15px;
      color: var(--primary-color);
    }

    .management-section {
      background: white;
      padding: 30px;
      margin: 30px 0;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .section-title {
      color: var(--secondary-color);
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 3px solid var(--primary-color);
      font-size: 1.8em;
    }

    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 30px;
      margin-bottom: 30px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: var(--secondary-color);
      font-weight: 600;
      font-size: 1.1em;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
      width: 100%;
      padding: 15px;
      border: 2px solid #e9ecef;
      border-radius: 10px;
      font-size: 16px;
      box-sizing: border-box;
      transition: border-color 0.3s;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
      border-color: var(--primary-color);
      outline: none;
    }

    .form-group textarea {
      height: 120px;
      resize: vertical;
    }

    .upload-option {
      background: var(--light-bg);
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 15px;
      border: 2px dashed #ddd;
    }

    .upload-option.active {
      border-color: var(--primary-color);
      background: #fff8f6;
    }

    .btn {
      padding: 15px 30px;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      font-size: 16px;
      font-weight: 600;
      transition: all 0.3s;
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }

    .btn-primary {
      background: var(--primary-color);
      color: white;
    }

    .btn-primary:hover {
      background: #cc2f00;
      transform: translateY(-2px);
    }

    .btn-success {
      background: var(--success-color);
      color: white;
      padding: 10px 20px;
      text-decoration: none;
    }

    .btn-danger {
      background: var(--danger-color);
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }

    .btn-warning {
      background: var(--warning-color);
      color: var(--dark-bg);
      padding: 10px 20px;
      text-decoration: none;
    }

    .content-table {
      width: 100%;
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
      margin-top: 20px;
    }

    .content-table table {
      width: 100%;
      border-collapse: collapse;
    }

    .content-table th {
      background: var(--secondary-color);
      color: white;
      padding: 20px;
      text-align: left;
      font-weight: 600;
    }

    .content-table td {
      padding: 18px 20px;
      border-bottom: 1px solid #eee;
      vertical-align: middle;
    }

    .content-table tr:hover {
      background: #f8f9fa;
    }

    .thumbnail-cell {
      width: 80px;
    }

    .thumbnail-cell img {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 8px;
    }

    .action-cell {
      width: 200px;
    }

    .action-buttons {
      display: flex;
      gap: 8px;
    }

    .stats-cell {
      text-align: center;
      font-weight: 600;
    }

    .views-count {
      color: var(--info-color);
    }

    .downloads-count {
      color: var(--success-color);
    }

    .message {
      padding: 15px 20px;
      margin: 20px 0;
      border-radius: 10px;
      font-weight: 500;
      font-size: 1.1em;
    }

    .message.success {
      background: #d4edda;
      color: #155724;
      border: 2px solid #c3e6cb;
    }

    .message.error {
      background: #f8d7da;
      color: #721c24;
      border: 2px solid #f5c6cb;
    }

    .quick-actions {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      margin: 25px 0;
    }

    .quick-action-btn {
      background: white;
      padding: 25px;
      border-radius: 15px;
      text-align: center;
      text-decoration: none;
      color: var(--secondary-color);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      transition: all 0.3s;
      border: 3px solid transparent;
    }

    .quick-action-btn:hover {
      transform: translateY(-5px);
      border-color: var(--primary-color);
      color: var(--primary-color);
    }

    .action-icon {
      font-size: 2.5em;
      margin-bottom: 15px;
      color: var(--primary-color);
    }

    .action-title {
      font-size: 1.3em;
      font-weight: 600;
      margin-bottom: 10px;
    }

    .upload-toggle {
      display: flex;
      gap: 10px;
      margin-bottom: 15px;
    }

    .toggle-btn {
      padding: 12px 20px;
      background: #e9ecef;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.3s;
    }

    .toggle-btn.active {
      background: var(--primary-color);
      color: white;
    }

    .file-input {
      padding: 15px;
      border: 2px dashed #ddd;
      border-radius: 8px;
      text-align: center;
      background: #f8f9fa;
      transition: border-color 0.3s;
    }

    .file-input:hover {
      border-color: var(--primary-color);
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #6c757d;
    }

    .empty-state i {
      font-size: 4em;
      margin-bottom: 20px;
      color: #dee2e6;
    }

    .empty-state h3 {
      font-size: 1.5em;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
<div class="sidebar">
  <ul>
    <div class="logo"><a href="">ARNET Admin</a></div>
    <div class="list">
        <li><a href="dashboard.php" style="color: var(--primary-color);"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="#trailers-section"><i class="fas fa-film"></i> Trailers</a></li>
        <li><a href="#movies-section"><i class="fas fa-star"></i> Movies</a></li>
        <li><a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a></li>
    </div>
    <div class="btn"><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
  </ul>
</div>

<div class="intros">
    <div class="dashboard-container">
        <h1 style="color: var(--secondary-color); margin-bottom: 10px;">ARNET Admin Dashboard</h1>
        <p style="color: #6c757d; font-size: 1.2em; margin-bottom: 30px;">Manage your movies and trailers with ease</p>
        
        <!-- Messages -->
        <?php if($success_message): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if($error_message): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="#trailers-section" class="quick-action-btn">
                <div class="action-icon"><i class="fas fa-video"></i></div>
                <div class="action-title">Upload Trailer</div>
                <p>Add new trailer content</p>
            </a>
            <a href="#movies-section" class="quick-action-btn">
                <div class="action-icon"><i class="fas fa-film"></i></div>
                <div class="action-title">Add Movie</div>
                <p>Upload featured movie</p>
            </a>
            <a href="../populate_sample_data.php" class="quick-action-btn">
                <div class="action-icon"><i class="fas fa-database"></i></div>
                <div class="action-title">Sample Data</div>
                <p>Add demo content</p>
            </a>
        </div>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-star"></i></div>
                <div class="stat-number"><?php echo $featured_count; ?>/5</div>
                <div class="stat-label">Featured Movies</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-film"></i></div>
                <div class="stat-number"><?php echo $trailers_count; ?></div>
                <div class="stat-label">Total Trailers</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-eye"></i></div>
                <div class="stat-number"><?php echo $total_views; ?></div>
                <div class="stat-label">Total Views</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-download"></i></div>
                <div class="stat-number"><?php echo $total_downloads; ?></div>
                <div class="stat-label">Total Downloads</div>
            </div>
        </div>
        
        <!-- Trailers Section -->
        <div class="management-section" id="trailers-section">
            <h2 class="section-title"><i class="fas fa-video"></i> Trailers Management</h2>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div>
                        <h3 style="color: var(--secondary-color); margin-bottom: 20px;">Trailer Information</h3>
                        <div class="form-group">
                            <label><i class="fas fa-heading"></i> Trailer Title *</label>
                            <input type="text" name="title" placeholder="Enter trailer title" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-align-left"></i> Description *</label>
                            <textarea name="description" placeholder="Describe the trailer content" required></textarea>
                        </div>
                    </div>
                    <div>
                        <h3 style="color: var(--secondary-color); margin-bottom: 20px;">Media Files</h3>
                        
                        <div class="form-group">
                            <label><i class="fas fa-image"></i> Thumbnail *</label>
                            <div class="upload-toggle">
                                <button type="button" class="toggle-btn active" onclick="toggleUpload('thumbnail', 'file')">Upload File</button>
                                <button type="button" class="toggle-btn" onclick="toggleUpload('thumbnail', 'url')">Use URL</button>
                            </div>
                            <div id="thumbnail-file" class="upload-option active">
                                <div class="file-input">
                                    <input type="file" name="thumbnail" accept="image/*">
                                    <small>Select thumbnail image (JPG, PNG)</small>
                                </div>
                            </div>
                            <div id="thumbnail-url" class="upload-option" style="display: none;">
                                <input type="url" name="thumbnail_url" placeholder="https://example.com/thumbnail.jpg">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-video"></i> Video File *</label>
                            <div class="upload-toggle">
                                <button type="button" class="toggle-btn active" onclick="toggleUpload('trailer', 'file')">Upload File</button>
                                <button type="button" class="toggle-btn" onclick="toggleUpload('trailer', 'url')">Use URL</button>
                            </div>
                            <div id="trailer-file" class="upload-option active">
                                <div class="file-input">
                                    <input type="file" name="trailer_video" accept="video/*">
                                    <small>Select video file (MP4, AVI, etc.)</small>
                                </div>
                            </div>
                            <div id="trailer-url" class="upload-option" style="display: none;">
                                <input type="url" name="trailer_video_url" placeholder="https://example.com/trailer.mp4">
                            </div>
                        </div>
                        
                        <button type="submit" name="upload_trailer" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Trailer
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Trailers Table -->
            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>Thumbnail</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Upload Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($all_trailers)): ?>
                            <?php foreach($all_trailers as $trailer): ?>
                            <tr>
                                <td class="thumbnail-cell">
                                    <img src="../<?php echo htmlspecialchars($trailer['thumbnail_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($trailer['title']); ?>"
                                         onerror="this.src='https://via.placeholder.com/60x60/666666/ffffff?text=No+Image'">
                                </td>
                                <td><strong><?php echo htmlspecialchars($trailer['title']); ?></strong></td>
                                <td><?php echo htmlspecialchars(substr($trailer['description'], 0, 100) . (strlen($trailer['description']) > 100 ? '...' : '')); ?></td>
                                <td><?php echo date('M d, Y', strtotime($trailer['uploaded_at'])); ?></td>
                                <td class="action-cell">
                                    <div class="action-buttons">
                                        <a href="../watch.php?id=<?php echo $trailer['id']; ?>&type=trailer" 
                                           class="btn-success" target="_blank">
                                            <i class="fas fa-play"></i> View
                                        </a>
                                        <form method="POST" action="delete_trailer.php" style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo $trailer['id']; ?>">
                                            <button type="submit" class="btn-danger" 
                                                    onclick="return confirm('Delete this trailer?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="fas fa-film"></i>
                                    <h3>No Trailers Yet</h3>
                                    <p>Upload your first trailer to get started</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Featured Movies Section -->
        <div class="management-section" id="movies-section">
            <h2 class="section-title"><i class="fas fa-star"></i> Featured Movies Management</h2>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div>
                        <h3 style="color: var(--secondary-color); margin-bottom: 20px;">Movie Information</h3>
                        <div class="form-group">
                            <label><i class="fas fa-heading"></i> Movie Title *</label>
                            <input type="text" name="title" placeholder="Enter movie title" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-align-left"></i> Description *</label>
                            <textarea name="description" placeholder="Describe the movie plot" required></textarea>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-tags"></i> Category</label>
                            <select name="category">
                                <option value="Movie">Movie</option>
                                <option value="Series">Series</option>
                                <option value="Documentary">Documentary</option>
                                <option value="Animation">Animation</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <h3 style="color: var(--secondary-color); margin-bottom: 20px;">Media Files</h3>
                        
                        <div class="form-group">
                            <label><i class="fas fa-image"></i> Poster Image *</label>
                            <div class="upload-toggle">
                                <button type="button" class="toggle-btn active" onclick="toggleUpload('poster', 'file')">Upload File</button>
                                <button type="button" class="toggle-btn" onclick="toggleUpload('poster', 'url')">Use URL</button>
                            </div>
                            <div id="poster-file" class="upload-option active">
                                <div class="file-input">
                                    <input type="file" name="poster" accept="image/*">
                                    <small>Select poster image (JPG, PNG)</small>
                                </div>
                            </div>
                            <div id="poster-url" class="upload-option" style="display: none;">
                                <input type="url" name="poster_url" placeholder="https://example.com/poster.jpg">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-video"></i> Video File *</label>
                            <div class="upload-toggle">
                                <button type="button" class="toggle-btn active" onclick="toggleUpload('video', 'file')">Upload File</button>
                                <button type="button" class="toggle-btn" onclick="toggleUpload('video', 'url')">Use URL</button>
                            </div>
                            <div id="video-file" class="upload-option active">
                                <div class="file-input">
                                    <input type="file" name="video" accept="video/*">
                                    <small>Select video file (MP4, AVI, etc.)</small>
                                </div>
                            </div>
                            <div id="video-url" class="upload-option" style="display: none;">
                                <input type="url" name="video_url" placeholder="https://example.com/movie.mp4">
                            </div>
                        </div>
                        
                        <button type="submit" name="add_featured" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Add Featured Movie
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Movies Table -->
            <div class="content-table">
                <table>
                    <thead>
                        <tr>
                            <th>Poster</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Views</th>
                            <th>Downloads</th>
                            <th>Added Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($featured_movies)): ?>
                            <?php foreach($featured_movies as $movie): ?>
                            <tr>
                                <td class="thumbnail-cell">
                                    <img src="../<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($movie['title']); ?>"
                                         onerror="this.src='https://via.placeholder.com/60x60/666666/ffffff?text=No+Image'">
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($movie['title']); ?></strong>
                                    <br><small style="color: #666;"><?php echo htmlspecialchars(substr($movie['description'], 0, 50) . '...'); ?></small>
                                </td>
                                <td><span style="background: #e9ecef; padding: 5px 10px; border-radius: 15px; font-size: 0.9em;"><?php echo htmlspecialchars($movie['category']); ?></span></td>
                                <td class="stats-cell views-count"><?php echo $movie['views'] ?? 0; ?></td>
                                <td class="stats-cell downloads-count"><?php echo $movie['downloads'] ?? 0; ?></td>
                                <td><?php echo date('M d, Y', strtotime($movie['created_at'])); ?></td>
                                <td class="action-cell">
                                    <div class="action-buttons">
                                        <a href="../watch.php?id=<?php echo $movie['id']; ?>&type=movie" 
                                           class="btn-success" target="_blank">
                                            <i class="fas fa-play"></i> Watch
                                        </a>
                                        <a href="#" class="btn-warning">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                        <form method="POST" action="delete_featured.php" style="display: inline;">
                                            <input type="hidden" name="id" value="<?php echo $movie['id']; ?>">
                                            <button type="submit" class="btn-danger" 
                                                    onclick="return confirm('Delete this movie?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <i class="fas fa-star"></i>
                                    <h3>No Featured Movies</h3>
                                    <p>Add your first featured movie above</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function toggleUpload(type, method) {
    // Update toggle buttons
    document.querySelectorAll(`#${type}-file, #${type}-url`).forEach(el => {
        el.style.display = 'none';
    });
    document.querySelectorAll(`.toggle-btn`).forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected method
    document.getElementById(`${type}-${method}`).style.display = 'block';
    event.target.classList.add('active');
}

// Add views and downloads tracking to your database tables
// You'll need to update your db.php to include these columns:
// ALTER TABLE featured_movies ADD COLUMN views INT DEFAULT 0;
// ALTER TABLE featured_movies ADD COLUMN downloads INT DEFAULT 0;
</script>
</body>
</html>