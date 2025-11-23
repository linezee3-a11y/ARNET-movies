<?php
include("db.php");

// Get movie ID from URL
$movie_id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? 'movie'; // movie or trailer

// Initialize variables
$movie = null;
$related_movies = [];
$video_url = '';
$title = '';
$description = '';

if ($movie_id) {
    if ($type === 'trailer') {
        // Get trailer data
        try {
            $stmt = $pdo->prepare("SELECT * FROM trailers WHERE id = ?");
            $stmt->execute([$movie_id]);
            $movie = $stmt->fetch();
            
            if ($movie) {
                $video_url = $movie['video_url'];
                $title = $movie['title'];
                $description = $movie['description'];
            }
        } catch (PDOException $e) {
            // Handle error silently
        }
    } else {
        // Get movie data from featured_movies or videos table
        try {
            $stmt = $pdo->prepare("SELECT * FROM featured_movies WHERE id = ?");
            $stmt->execute([$movie_id]);
            $movie = $stmt->fetch();
            
            if ($movie) {
                $video_url = $movie['video_url'];
                $title = $movie['title'];
                $description = $movie['description'];
            } else {
                // Try videos table
                $stmt = $pdo->prepare("SELECT * FROM videos WHERE id = ?");
                $stmt->execute([$movie_id]);
                $movie = $stmt->fetch();
                
                if ($movie) {
                    $video_url = $movie['video_url'];
                    $title = $movie['title'];
                    $description = $movie['description'];
                }
            }
        } catch (PDOException $e) {
            // Handle error silently
        }
    }
    
    // Get related movies (excluding current one)
    try {
        if ($type === 'trailer') {
            $related_movies = $pdo->query("SELECT * FROM trailers WHERE id != $movie_id ORDER BY uploaded_at DESC LIMIT 9")->fetchAll();
        } else {
            $related_movies = $pdo->query("SELECT * FROM featured_movies WHERE id != $movie_id ORDER BY created_at DESC LIMIT 9")->fetchAll();
        }
    } catch (PDOException $e) {
        $related_movies = [];
    }
}

// If no movie found, redirect to index
if (!$movie && $movie_id) {
    header("Location: index.php");
    exit();
}

// Handle OMDB API movies
$omdb_id = $_GET['omdb'] ?? null;
if ($omdb_id) {
    $api_key = '03acddc3-0751-4580-b715-789927c20fe0';
    $omdb_url = "https://www.omdbapi.com/?i=$omdb_id&apikey=$api_key";
    
    $omdb_data = @file_get_contents($omdb_url);
    if ($omdb_data) {
        $movie_data = json_decode($omdb_data, true);
        if ($movie_data['Response'] === 'True') {
            $title = $movie_data['Title'];
            $description = $movie_data['Plot'];
            // Use trailer from YouTube or placeholder
            $video_url = "https://www.youtube.com/embed/" . ($movie_data['imdbID'] ?? 'dQw4w9WgXcQ');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ? $title . ' | ARNET' : 'Watch | ARNET'); ?></title>
    <link rel="stylesheet" href="watch.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
</head>
<body>
    <nav>
        <ul>
            <div class="logo"><a href="index.php"><img src="pic/LOGO.png" style="max-width: 60%;"></a></div>
            <div class="list">
                <li id="sea"><a href="#">Search</a></li>
                <li><a href="trailer.php">Trailers</a></li>
                <li><a href="#">About</a></li>
            </div>
        </ul>
    </nav>
    
    <div class="sea">
        <div class="cl">close</div>
        <div class="input">
            <input type="text" placeholder="Search Movies, trailers ....">
            <i>S</i>
        </div>
        <div class="ses">
            <!-- Search results will go here -->
        </div>
    </div>
    
    <div class="intros">
        <?php if ($movie || $omdb_id): ?>
        <div class="watchs">
            <div class="video">
                <video id="video" src="<?php echo htmlspecialchars($video_url); ?>" controls poster="<?php echo htmlspecialchars($movie['poster_url'] ?? $movie['thumbnail_url'] ?? ''); ?>"></video>
                <div class="plays">
                    <div class="play" id="p1"><i class="fas fa-play"></i></div>
                    <div class="play" id="p2"><i class="fas fa-pause"></i></div>
                </div>
            </div>
            <div class="infos">
                <h2><?php echo htmlspecialchars($title); ?></h2>
                <p style="color: white; margin: 10px 0; line-height: 1.6;"><?php echo htmlspecialchars($description); ?></p>
                <div class="bt5">
                    <a href="<?php echo htmlspecialchars($video_url); ?>" download>Download video</a>
                    <a href="#">Save to playlist</a>
                </div>
            </div>
            
            <?php if ($type !== 'trailer'): ?>
            <div class="episodes">
                <h4>Episode</h4>
                <div class="eps">
                    <div class="ep">1A</div>
                    <div class="ep">1B</div>
                    <div class="ep">2A</div>
                    <div class="ep active">2B</div>
                </div>
            </div>
            <?php endif; ?>
            
            <form action="" method="POST">
                <h2>Comment</h2>
                <div class="in">
                    <input type="text" id="in1" name="username" placeholder="Enter your username" required>
                    <textarea name="comment" id="in2" placeholder="Enter your Message.." required></textarea>
                    <div class="bo">
                        <button type="submit" style="background: var(--primary-color); border: none; color: white; padding: 20px 400px; border-radius: 50px; cursor: pointer;">Send Message</button>
                    </div>
                </div>
            </form>
            
            <div class="comment-section">
                <div class="comments">
                    <div class="magg">MG</div>
                    <div class="message">Hello, i like this movie!!!!</div>
                </div>
                <div class="comments">
                    <div class="magg">AR</div>
                    <div class="message">Amazing content! Keep it up! 🎬</div>
                </div>
                <div class="comments">
                    <div class="magg">TV</div>
                    <div class="message">When is the next episode coming?</div>
                </div>
            </div>
        </div>
        
        <div class="pop">
            <h3 style="color: white; grid-column: 1 / -1; padding: 20px;">Related Videos</h3>
            <?php if (!empty($related_movies)): ?>
                <?php foreach ($related_movies as $related): ?>
                <div class="poper">
                    <div class="mage">     
                        <img src="<?php echo htmlspecialchars($related['poster_url'] ?? $related['thumbnail_url'] ?? 'https://via.placeholder.com/300x450/333333/ffffff?text=No+Image'); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>">               
                        <div class="over">
                            <div class="btn2">
                                <a href="watch.php?id=<?php echo $related['id']; ?>&type=<?php echo $type; ?>"><i class="fas fa-play"></i></a>
                                <a href="watch.php?id=<?php echo $related['id']; ?>&type=<?php echo $type; ?>">Watch</a>
                            </div>
                        </div>
                    </div>
                    <div class="te"><?php echo htmlspecialchars($related['title']); ?></div>
                    
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; color: white; padding: 20px;">
                    No related videos found.
                </div>
            <?php endif; ?>
        </div>
        
        <?php else: ?>
        <div class="watchs" style="width: 100%; text-align: center; color: white;">
            <h2>Movie Not Found</h2>
            <p>The movie you're looking for doesn't exist or has been removed.</p>
            <a href="index.php" style="color: var(--primary-color); text-decoration: underline;">Go back to homepage</a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Video play/pause functionality
        const p1 = document.getElementById('p1');
        const p2 = document.getElementById('p2');
        const video = document.getElementById('video');
        
        if (p1 && p2 && video) {
            p1.addEventListener('click', () => {
                video.play();
                p1.style.display = 'none';
                p2.style.display = 'flex';
            });
            
            p2.addEventListener('click', () => {
                video.pause();
                p1.style.display = 'flex';
                p2.style.display = 'none';
            });
            
            video.addEventListener('play', () => {
                p1.style.display = 'none';
                p2.style.display = 'flex';
            });
            
            video.addEventListener('pause', () => {
                p1.style.display = 'flex';
                p2.style.display = 'none';
            });
        }

        // Episode selection
        document.querySelectorAll('.ep').forEach(ep => {
            ep.addEventListener('click', function() {
                document.querySelectorAll('.ep').forEach(e => e.classList.remove('active'));
                this.classList.add('active');
                // Here you can add functionality to load different episodes
            });
        });

        // Search functionality
        document.querySelector('.sea input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            // Add search functionality here
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

        // Comment form submission
        document.querySelector('form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('in1').value;
            const comment = document.getElementById('in2').value;
            
            if (username && comment) {
                // Add comment to the section
                const commentSection = document.querySelector('.comment-section');
                const newComment = document.createElement('div');
                newComment.className = 'comments';
                newComment.innerHTML = `
                    <div class="magg">${username.charAt(0).toUpperCase()}</div>
                    <div class="message">${comment}</div>
                `;
                commentSection.insertBefore(newComment, commentSection.firstChild);
                
                // Clear form
                document.getElementById('in1').value = '';
                document.getElementById('in2').value = '';
                
                alert('Comment added successfully!');
            }
        });
    </script>
</body>
</html>