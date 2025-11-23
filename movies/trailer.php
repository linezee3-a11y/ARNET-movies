<?php
include("db.php");

// Get all trailers from database
$trailers = $pdo->query("SELECT * FROM trailers ORDER BY uploaded_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trailers | ARNET</title>
    <link rel="stylesheet" href="trailer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>
    <nav>
        <ul>
            <div class="logo"><a href="index.php"><img src="pic/LOGO.png" style="max-width: 60%;"></a></div>
            <div class="list">
                <li id="sea"><a href="#">Search</a></li>
                <li><a href="trailer.php" style="color: var(--primary-color);">Trailers</a></li>
                <li><a href="admin/dashboard.php">Admin</a></li>
            </div>
        </ul>
    </nav>

    <div class="sea">
        <div class="cl">X</div>
        <div class="input">
            <input type="text" placeholder="Search Movies, trailers ....">
            <i class="fas fa-search">S</i>
        </div>
        <div class="ses">
            <!-- Search results will go here -->
        </div>
    </div>

    <div class="intros" id="trailerContainer">
        <?php if(!empty($trailers)): ?>
            <?php foreach($trailers as $index => $trailer): ?>
            <div class="shorts" id="trailer-<?php echo $trailer['id']; ?>">
                <div class="short">
                    <video 
                        src="<?php echo htmlspecialchars($trailer['video_url']); ?>" 
                        poster="<?php echo htmlspecialchars($trailer['thumbnail_url']); ?>"
                        controls
                        style="width: 100%; height: 100%; object-fit: cover;"
                    ></video>
                    <div class="over2">
                        <h2><?php echo htmlspecialchars($trailer['title']); ?></h2>
                        <p><?php echo htmlspecialchars($trailer['description']); ?></p>
                        <div class="btns">
                          <div class="b">  <a href="#"><i class="fas fa-heart"></i> ❤ Like</a>
                            <a href="#"><i class="fas fa-share"></i> 🎁 Share</a></div>
                            <p><?php echo date('M d, Y', strtotime($trailer['uploaded_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="shorts">
                <div class="short" style="display: flex; align-items: center; justify-content: center; color: white; flex-direction: column; gap: 20px;">
                    <h2>No trailers available yet</h2>
                    <a href="admin/dashboard.php" style="color: var(--primary-color); text-decoration: underline;">Go to Admin to upload trailers</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="arrows">
        <i class="fas fa-arrow-up" id="prevTrailer"></i>
        <i class="fas fa-arrow-down" id="nextTrailer"></i>
    </div>

    <script>
        // Smooth scrolling for trailer navigation
        const trailerContainer = document.getElementById('trailerContainer');
        const shorts = document.querySelectorAll('.shorts');
        let currentTrailer = 0;

        function scrollToTrailer(index) {
            if (shorts[index]) {
                shorts[index].scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'center'
                });
                currentTrailer = index;
                
                // Auto-play the current video
                const videos = document.querySelectorAll('video');
                videos.forEach(video => {
                    video.pause();
                    video.currentTime = 0;
                });
                
                const currentVideo = videos[currentTrailer];
                if (currentVideo) {
                    currentVideo.play().catch(e => {
                        console.log('Auto-play prevented:', e);
                    });
                }
            }
        }

        document.getElementById('nextTrailer').addEventListener('click', function() {
            if (currentTrailer < shorts.length - 1) {
                scrollToTrailer(currentTrailer + 1);
            }
        });

        document.getElementById('prevTrailer').addEventListener('click', function() {
            if (currentTrailer > 0) {
                scrollToTrailer(currentTrailer - 1);
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (currentTrailer < shorts.length - 1) {
                    scrollToTrailer(currentTrailer + 1);
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (currentTrailer > 0) {
                    scrollToTrailer(currentTrailer - 1);
                }
            }
        });

        // Delete trailer functionality
        document.querySelectorAll('.delete-trailer').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const trailerId = this.getAttribute('data-id');
                
                if(confirm('Are you sure you want to delete this trailer?')) {
                    fetch('admin/delete_trailer.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id=' + trailerId
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            document.getElementById('trailer-' + trailerId).remove();
                            alert('Trailer deleted successfully!');
                            // Reload if no trailers left
                            if(document.querySelectorAll('.shorts').length === 0) {
                                location.reload();
                            }
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
                }
            });
        });

        // Auto-play first video
        window.addEventListener('load', function() {
            const firstVideo = document.querySelector('video');
            if (firstVideo) {
                firstVideo.play().catch(e => {
                    console.log('Auto-play prevented, user interaction required');
                });
            }
        });

        // Search functionality
        document.querySelector('.sea input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            shorts.forEach(short => {
                const title = short.querySelector('h2').textContent.toLowerCase();
                if (title.includes(searchTerm)) {
                    short.style.display = 'flex';
                } else {
                    short.style.display = 'none';
                }
            });
        });

        // Close search
        document.querySelector('.sea .cl').addEventListener('click', function() {
            document.querySelector('.sea').style.transform = 'translateY(200%)';
        });

        // Open search
        document.getElementById('sea').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('.sea').style.transform = 'translateY(0)';
        });

        // Click to play/pause
        document.querySelectorAll('video').forEach(video => {
            video.addEventListener('click', function() {
                if (this.paused) {
                    this.play();
                } else {
                    this.pause();
                }
            });
        });
    </script>
</body>
</html>