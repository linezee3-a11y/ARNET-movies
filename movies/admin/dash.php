<?php 
// Start session first
session_start();

// Simple authentication bypass for testing
$_SESSION['admin_logged_in'] = true;

include("../db.php");

// Safe database queries with error handling
try {
    // Get featured movies count
    $featured_count = $pdo->query("SELECT COUNT(*) FROM featured_movies")->fetchColumn();
} catch (PDOException $e) {
    $featured_count = 0;
}

try {
    // Get trailers count
    $trailers_count = $pdo->query("SELECT COUNT(*) FROM trailers")->fetchColumn();
} catch (PDOException $e) {
    $trailers_count = 0;
}

try {
    // Get total views
    $total_views = $pdo->query("SELECT SUM(views) FROM videos")->fetchColumn() ?? 0;
} catch (PDOException $e) {
    $total_views = 0;
}

try {
    // Get latest featured movies
    $featured_movies = $pdo->query("SELECT * FROM featured_movies ORDER BY created_at DESC LIMIT 5")->fetchAll();
} catch (PDOException $e) {
    $featured_movies = [];
}

try {
    // Get videos
    $videos = $pdo->query("SELECT * FROM videos ORDER BY uploaded_at DESC")->fetchAll();
} catch (PDOException $e) {
    $videos = [];
}

try {
    // Get all trailers for management
    $all_trailers = $pdo->query("SELECT * FROM trailers ORDER BY uploaded_at DESC")->fetchAll();
} catch (PDOException $e) {
    $all_trailers = [];
}

// Handle featured movie upload
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_featured'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $poster_url = $_POST['poster_url'] ?? '';
    $video_url = $_POST['video_url'] ?? '';
    $omdb_id = $_POST['omdb_id'] ?? '';
    
    if(!empty($title) && !empty($poster_url) && !empty($video_url)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO featured_movies (title, description, poster_url, video_url, omdb_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $poster_url, $video_url, $omdb_id]);
            $success_message = "Featured movie added successfully!";
            
            // Refresh the page to show new movie
            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            $error_message = "Error adding movie: " . $e->getMessage();
        }
    } else {
        $error_message = "Please fill all required fields!";
    }
}

// Handle trailer upload via URL
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_trailer'])) {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $thumbnail_url = $_POST['thumbnail_url'] ?? '';
    $video_url = $_POST['video_url'] ?? '';
    
    if(!empty($title) && !empty($thumbnail_url) && !empty($video_url)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO trailers (title, description, thumbnail_url, video_url) VALUES (?, ?, ?, ?)");
            $stmt->execute([$title, $description, $thumbnail_url, $video_url]);
            $success_message = "Trailer uploaded successfully!";
            
            // Refresh the page to show new trailer
            header("Location: dashboard.php");
            exit();
        } catch (PDOException $e) {
            $error_message = "Error uploading trailer: " . $e->getMessage();
        }
    } else {
        $error_message = "Please fill all required fields!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard | ARNET</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .featured-management {
        background: white;
        padding: 20px;
        margin: 20px 0;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    .featured-form {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .featured-form input, .featured-form textarea {
        width: 100%;
        padding: 10px;
        margin: 5px 0;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    
    .featured-form textarea {
        height: 100px;
    }
    
    .current-featured {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
    
    .featured-item {
        background: #f5f5f5;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        border: 1px solid #ddd;
    }
    
    .featured-item img {
        max-width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 5px;
        background: #eee;
    }
    
    .db-status {
        background: #e7f3ff;
        padding: 15px;
        border-radius: 8px;
        margin: 10px 0;
        border-left: 4px solid #1890ff;
    }
    
    .message {
        padding: 10px;
        margin: 10px 0;
        border-radius: 5px;
    }
    
    .success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    /* Upload Modal Styles */
    .uploads {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    
    .upload {
        background: white;
        padding: 30px;
        border-radius: 10px;
        width: 500px;
        max-width: 90%;
    }
    
    .upload h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }
    
    .upload input, .upload textarea {
        width: 100%;
        padding: 12px;
        margin: 8px 0;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-sizing: border-box;
    }
    
    .upload textarea {
        height: 80px;
        resize: vertical;
    }
    
    .upload-buttons {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }
    
    .upload-buttons button {
        flex: 1;
        padding: 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
    }
    
    .cancel-btn {
        background: #6c757d;
        color: white;
    }
    
    .upload-btn {
        background: var(--primary-color);
        color: white;
    }
    
    .file-inputs {
        margin: 15px 0;
    }
    
    .file-inputs label {
        display: block;
        margin: 10px 0 5px 0;
        color: #333;
        font-weight: 500;
    }
    
    .file-inputs small {
        color: #666;
        font-size: 12px;
    }
    
    /* Trailer Table Styles */
    .trailer-table {
        width: 100%;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        margin: 20px 0;
    }
    
    .trailer-table table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .trailer-table th {
        background: var(--primary-color);
        color: white;
        padding: 15px;
        text-align: left;
    }
    
    .trailer-table td {
        padding: 12px 15px;
        border-bottom: 1px solid #eee;
    }
    
    .trailer-table tr:hover {
        background: #f9f9f9;
    }
    
    .thumbnail-cell {
        width: 100px;
    }
    
    .thumbnail-cell img {
        width: 80px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
    }
    
    .action-cell {
        width: 150px;
    }
    
    .action-buttons {
        display: flex;
        gap: 5px;
    }
    
    .action-buttons button {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }
    
    .delete-btn {
        background: #dc3545;
        color: white;
    }
    
    .view-btn {
        background: #007bff;
        color: white;
        text-decoration: none;
        padding: 6px 12px;
        border-radius: 4px;
        display: inline-block;
    }
  </style>
</head>
<body>
<div class="sidebar">
  <ul>
    <div class="logo"><a href="">ARNET Admin</a></div>
    <div class="list">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="#" id="upb">Upload Trailer</a></li>
        <li><a href="#featured-management">Manage Featured</a></li>
    </div>
    <div class="btn"><a href="logout.php">Logout</a></div>
  </ul>
</div>

<div class="intros">
    <h2>Hello, Admin!!</h2>
    <p>Now, you can Manage your <a href="../index.php">ARNET MOVIES</a> & control all data</p>
    
    <!-- Display Messages -->
    <?php if(isset($success_message)): ?>
        <div class="message success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if(isset($error_message)): ?>
        <div class="message error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    
    <!-- Database Status -->
    <div class="db-status">
        <h3>📊 Database Status</h3>
        <p>Featured Movies: <?php echo $featured_count; ?> | Trailers: <?php echo $trailers_count; ?> | Total Views: <?php echo $total_views; ?></p>
    </div>
    
    <div class="bt5">
        <a href="#" id="upb">Upload New Trailer</a>
        <a href="#featured-management">Manage Featured</a>
    </div>
    
    <div class="intro">
        <div class="in">
            <div class="for">
                <h2><div class="log"></div> <?php echo $featured_count; ?></h2>
                <p>Featured Movies</p>
            </div>
        </div>
        <div class="in">
            <div class="for">
                <h2><div class="log"></div> <?php echo $total_views; ?></h2>
                <p>Total Views</p>
            </div>
        </div>
        <div class="in">
            <div class="for">
                <h2><div class="log"></div> <?php echo $trailers_count; ?></h2>
                <p>Total Trailers</p>
            </div>
        </div>
        <div class="in">
            <div class="for">
                <h2><div class="log"></div> 150</h2>
                <p>Downloads</p>
            </div>
        </div>
        <div class="in">
            <div class="for">
                <h2><div class="log"></div> 12</h2>
                <p>Feedback</p>
            </div>
        </div>
        <div class="in">
            <div class="for">
                <h2><div class="log"></div> 3</h2>
                <p>Comments</p>
            </div>
        </div>
    </div>

    <!-- Trailer Management Section - TABLE VIEW -->
    <div class="trailer-table">
        <h2 style="padding: 20px; margin: 0; color: #333;">Manage Trailers (<?php echo count($all_trailers); ?>)</h2>
        
        <?php if(!empty($all_trailers)): ?>
        <table>
            <thead>
                <tr>
                    <th class="thumbnail-cell">Thumbnail</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Uploaded Date</th>
                    <th class="action-cell">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($all_trailers as $trailer): ?>
                <tr>
                    <td class="thumbnail-cell">
                        <img src="<?php echo htmlspecialchars($trailer['thumbnail_url']); ?>" 
                             alt="<?php echo htmlspecialchars($trailer['title']); ?>"
                             onerror="this.src='https://via.placeholder.com/80x60/666666/ffffff?text=No+Image'">
                    </td>
                    <td><?php echo htmlspecialchars($trailer['title']); ?></td>
                    <td><?php echo htmlspecialchars(substr($trailer['description'], 0, 100) . (strlen($trailer['description']) > 100 ? '...' : '')); ?></td>
                    <td><?php echo date('M d, Y H:i', strtotime($trailer['uploaded_at'])); ?></td>
                    <td class="action-cell">
                        <div class="action-buttons">
                            <button class="delete-trailer-btn" data-id="<?php echo $trailer['id']; ?>" data-title="<?php echo htmlspecialchars($trailer['title']); ?>">Delete</button>
                            <a href="../watch.php?id=<?php echo $trailer['id']; ?>&type=trailer" class="view-btn" target="_blank">View</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div style="padding: 40px; text-align: center; color: #666;">
            <p>No trailers uploaded yet. Use the "Upload New Trailer" button above.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Featured Movies Management -->
    <div class="featured-management" id="featured-management">
        <h2>Manage Featured Movies</h2>
        
        <form method="POST" class="featured-form">
            <div>
                <h3>Add New Featured Movie</h3>
                <input type="text" name="title" placeholder="Movie Title" required>
                <textarea name="description" placeholder="Movie Description"></textarea>
                <input type="text" name="omdb_id" placeholder="OMDB ID (optional)">
            </div>
            <div>
                <h3>Media URLs</h3>
                <input type="url" name="poster_url" placeholder="Poster Image URL" required>
                <input type="url" name="video_url" placeholder="Video URL" required>
                <button type="submit" name="add_featured" style="padding: 15px 30px; background: var(--primary-color); color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 10px;">Add Featured Movie</button>
            </div>
        </form>
        
        <h3>Current Featured Movies (<?php echo count($featured_movies); ?>)</h3>
        <div class="current-featured">
            <?php if(!empty($featured_movies)): ?>
                <?php foreach($featured_movies as $movie): ?>
                <div class="featured-item">
                    <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>" 
                         alt="<?php echo htmlspecialchars($movie['title']); ?>" 
                         onerror="this.src='https://via.placeholder.com/300x450/666666/ffffff?text=No+Image'">
                    <h4><?php echo htmlspecialchars($movie['title']); ?></h4>
                    <form method="POST" action="delete_featured.php" style="margin-top: 10px;">
                        <input type="hidden" name="id" value="<?php echo $movie['id']; ?>">
                        <button type="submit" style="padding: 5px 10px; background: red; color: white; border: none; border-radius: 3px; cursor: pointer;">Delete</button>
                    </form>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No featured movies found. Add some above!</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="tables">
        <h2>Videos (<?php echo count($videos); ?>)</h2>
        <table>
            <tr>
                <th>id</th>
                <th>Name</th>
                <th>category</th>
                <th>Size</th>
                <th>Uploaded at</th>
                <th>views</th>
                <th>actions</th>
            </tr>
            <?php if(!empty($videos)): ?>
                <?php foreach($videos as $video): ?>
                <tr>
                    <td><?php echo $video['id']; ?></td>
                    <td><?php echo htmlspecialchars($video['title']); ?></td>
                    <td><?php echo htmlspecialchars($video['category']); ?></td>
                    <td><?php echo htmlspecialchars($video['size']); ?></td>
                    <td><?php echo date('M d, Y', strtotime($video['uploaded_at'])); ?></td>
                    <td><?php echo $video['views']; ?></td>
                    <td id="td">
                    <div class="b">
                        <a href="#" class="copy-link" data-url="<?php echo $video['video_url']; ?>">copy link</a>
                        <a href="delete_video.php?id=<?php echo $video['id']; ?>">Delete</a>
                    </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center;">No videos found</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<!-- Upload Modal -->
<div class="uploads" id="uploadModal">
    <div class="upload">
        <div class="up" id="up1">
            <h2>Upload New Trailer</h2>
            <form method="POST" id="uploadForm">
                <input type="text" name="title" placeholder="Trailer Title" required>
                <textarea name="description" placeholder="Trailer Description" required></textarea>
                
                <div class="file-inputs">
                    <label>Thumbnail Image URL:</label>
                    <input type="url" name="thumbnail_url" placeholder="https://example.com/thumbnail.jpg" required>
                    <small>Use a direct image URL like: https://via.placeholder.com/300x450</small>
                    
                    <label>Video URL:</label>
                    <input type="url" name="video_url" placeholder="https://example.com/video.mp4" required>
                    <small>Use a direct video URL</small>
                </div>
                
                <div class="upload-buttons">
                    <button type="button" id="cancelUpload" class="cancel-btn">Cancel</button>
                    <button type="submit" name="upload_trailer" class="upload-btn">Upload Trailer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Upload Modal Functionality
document.getElementById('upb').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('uploadModal').style.display = 'flex';
});

document.getElementById('cancelUpload').addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('uploadModal').style.display = 'none';
});

// Close modal when clicking outside
document.getElementById('uploadModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.style.display = 'none';
    }
});

// Delete trailer functionality
document.querySelectorAll('.delete-trailer-btn').forEach(button => {
    button.addEventListener('click', function() {
        const trailerId = this.getAttribute('data-id');
        const trailerTitle = this.getAttribute('data-title');
        
        if(confirm('Are you sure you want to delete "' + trailerTitle + '"?')) {
            fetch('delete_trailer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + trailerId
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Trailer deleted successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('Delete failed: ' + error);
            });
        }
    });
});

// Copy link functionality
document.querySelectorAll('.copy-link').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const url = this.getAttribute('data-url');
        navigator.clipboard.writeText(url).then(() => {
            alert('Link copied to clipboard!');
        });
    });
});

// Auto-focus on first input when modal opens
document.getElementById('upb').addEventListener('click', function() {
    setTimeout(() => {
        document.querySelector('#uploadForm input[name="title"]').focus();
    }, 100);
});
</script>
</body>
</html>