<?php
include("db.php");

// Smart Time Ago Function
function timeAgo($timestamp) {
    // Handle null or empty timestamps
    if (empty($timestamp) || $timestamp == '0000-00-00 00:00:00') {
        return 'Just now';
    }
    
    $currentTime = time();
    $timeDiff = $currentTime - strtotime($timestamp);
    
    // If time is in future or invalid, return just now
    if ($timeDiff < 0) {
        return 'Just now';
    }
    
    // Time intervals in seconds
    $intervals = [
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    ];
    
    foreach ($intervals as $seconds => $label) {
        $div = $timeDiff / $seconds;
        
        if ($div >= 1) {
            $time = round($div);
            if ($time == 1) {
                return $time . ' ' . $label . ' ago';
            } else {
                return $time . ' ' . $label . 's ago';
            }
        }
    }
    
    return 'Just now';
}

// Get data with error handling
try {
    $featured_movies = $pdo->query("SELECT * FROM featured_movies ORDER BY created_at DESC LIMIT 5")->fetchAll();
} catch (Exception $e) {
    $featured_movies = [];
}

try {
    $popular_videos = $pdo->query("SELECT * FROM videos ORDER BY views DESC LIMIT 10")->fetchAll();
} catch (Exception $e) {
    $popular_videos = [];
}

try {
    $trailers = $pdo->query("SELECT * FROM trailers ORDER BY uploaded_at DESC LIMIT 6")->fetchAll();
} catch (Exception $e) {
    $trailers = [];
}

// Demo data for when no real data exists
$demo_titles = [
    'Avengers: Endgame Final Battle',
    'Spider-Man: No Way Home Portal Scene', 
    'The Batman Car Chase',
    'Black Panther Waterfall Fight',
    'Avatar 2 Underwater Scene',
    'Guardians of Galaxy Dance Off',
    'John Wick 4 Hotel Fight',
    'Mission Impossible Fallout',
    'Fast & Furious 10 Car Jump',
    'Transformers Final Battle'
];

$demo_trailers = [
    'Guardians 3 Official Trailer',
    'The Marvels Teaser',
    'Ant-Man 3 Trailer',
    'Flash Movie Trailer',
    'Blue Beetle Trailer',
    'Aquaman 2 Teaser'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARNET Movies</title>
    
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <style>
        /* Fix for play button centering and image display */
        .others .other {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #333;
        }
        
        .other a {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.6s ease;
            padding: 20px;
            background: var(--primary-color);
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .other:hover a {
            opacity: 1;
        }
        
        .other img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
        }
        
        /* Time ago styling */
        .te span {
            font-size: 0.8em;
            color: #ff4444;
            position: absolute;
            right: 10px;
            bottom: 5px;
            background: rgba(0,0,0,0.7);
            padding: 2px 6px;
            border-radius: 3px;
        }
        
        .popp h5 {
            position: absolute;
            right: 10px;
            bottom: 10px;
            color: white;
            background: rgba(0,0,0,0.7);
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 0.8em;
        }
        
        /* Ensure images always show */
        .mage img, .popp img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        /* Fix empty spaces */
        .mage, .popp .mage {
            background: #333;
            min-height: 200px;
        }
        
        /* Make sure third .other container stays positioned */
        .other:nth-child(3) {
            position: absolute;
            left: 72%;
            top: 130px;
            width: 15%;
            height: 370px;
            z-index: 10;
        }
        
        /* View count styling */
        .view-count {
            font-size: 0.75em;
            color: #ccc;
            margin-left: 5px;
        }
        

    </style>
</head>
<body>
    <div class="loader">
        <div class="loads">
            <h2>
                <i>A</i>
                <i>R</i>
                <i>N</i>
                <i>E</i>
                <i>T</i>
            </h2>
            <div class="load"><div class="loa"></div></div>
        </div>
    </div>
    
    <nav>
        <ul>
            <div class="logo">
                <a href="index.php">
                    <!-- Fallback text if image doesn't exist -->
                    <img src="pic/LOGO.png" alt="ARNET" onerror="this.style.display='none'; this.nextSibling.style.display='block';">
                    <span style="color:white; font-size:2em; font-weight:bold; display:none;">ARNET</span>
                </a>
            </div>
            <div class="list">
                <li id="sea"><a href="#">Search</a></li>
                <li><a href="trailer.php">Trailers</a></li>
                <li><a href="admin/dashboard.php">Admin</a></li>
            </div>
        </ul>
    </nav>
    
    <div class="sea">       
        <div class="cl">X</div>
        <div class="input">
            <input type="text" id="searchInput" placeholder="Search Movies, trailers ....">
            <i class="fas fa-search">S</i>
        </div>
        <div class="ses" id="searchResults">
            <!-- Search results will appear here -->
        </div>
    </div>
    
    <div class="intro">
        <?php if(!empty($featured_movies)): ?>
        <video src="<?php echo htmlspecialchars($featured_movies[0]['video_url']); ?>" muted autoplay loop poster="<?php echo htmlspecialchars($featured_movies[0]['poster_url']); ?>"></video>
        <?php else: ?>
        <div style="width:100%;height:100%;background:#222;display:flex;align-items:center;justify-content:center;color:white;">
            <p>No featured movies. <a href="populate_sample_data.php" style="color:var(--primary-color);">Add sample data</a></p>
        </div>
        <?php endif; ?>
        
        <div class="int">
            <div class="in">
                <div class="text">
                    <div class="feat">🌟 Featured</div>
                    <h2 id="change"><?php echo !empty($featured_movies) ? htmlspecialchars($featured_movies[0]['title']) : 'No Featured Movie'; ?></h2>
                    <p><?php echo !empty($featured_movies) ? htmlspecialchars($featured_movies[0]['description']) : 'Add featured movies through the admin panel to see them here.'; ?></p>
                    <div class="b">
                        <?php if(!empty($featured_movies)): ?>
                            <a href="watch.php?id=<?php echo $featured_movies[0]['id']; ?>&type=movie">Watch</a>
                        <?php else: ?>
                            <a href="admin/dashboard.php">Add Movies</a>
                        <?php endif; ?>
                        <a href="populate_sample_data.php">Add Sample Data</a>
                    </div>
                </div>
            </div>
            
            <div class="others">
                <?php for($i = 1; $i <= 5; $i++): ?>
                <div class="other" id="o<?php echo $i; ?>">
                    <?php if(isset($featured_movies[$i])): ?>
                        <img src="<?php echo htmlspecialchars($featured_movies[$i]['poster_url']); ?>" 
                             alt="<?php echo htmlspecialchars($featured_movies[$i]['title']); ?>"
                             onerror="this.style.display='none'">
                        <a href="watch.php?id=<?php echo $featured_movies[$i]['id']; ?>&type=movie">
                            <i class="fas fa-play"></i>
                        </a>
                    <?php else: ?>
                        <div style="width:100%;height:100%;background:#444;display:flex;align-items:center;justify-content:center;color:#888;">
                            <i class="fas fa-film" style="font-size:2em;"></i>
                        </div>
                        <a href="admin/dashboard.php">
                            <i class="fas fa-plus"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
    
    <div class="popular">
        <div class="t">
            <h2>Popular Videos</h2>
            <div class="arrow">
                <i class="fas fa-angle-left left"></i>
                <i class="fas fa-angle-right right"></i>
            </div>
        </div>
        
        <div class="pops">
            <div class="pop">
                <?php if(!empty($popular_videos)): ?>
                    <?php foreach($popular_videos as $index => $video): ?>
                    <div class="poper">
                        <div class="mage">     
                            <img src="<?php echo htmlspecialchars($video['thumbnail_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($video['title']); ?>"
                                 onerror="this.src='https://via.placeholder.com/300x450/333333/ffffff?text=No+Image'">               
                            <div class="over">
                                <div class="btn2">
                                    <a href="watch.php?id=<?php echo $video['id']; ?>&type=movie"><i class="fas fa-play"></i></a>
                                    <a href="watch.php?id=<?php echo $video['id']; ?>&type=movie">Watch</a>
                                </div>
                            </div>
                        </div>
                        <div class="te">
                            <?php echo htmlspecialchars($video['title']); ?> 
                            <span><?php echo timeAgo($video['uploaded_at']); ?></span>
                            <div class="view-count">👁️ <?php echo number_format($video['views']); ?> views</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Demo content with time ago -->
                    <?php for($i = 0; $i < 6; $i++): ?>
                    <div class="poper">
                        <div class="mage" style="background:#555;display:flex;align-items:center;justify-content:center;color:white;">
                            <i class="fas fa-play-circle" style="font-size:3em;opacity:0.5;"></i>
                        </div>
                        <div class="te">
                            <?php echo $demo_titles[$i] ?? 'Demo Movie ' . ($i + 1); ?>
                            <span>
                                <?php 
                                // Generate random time ago for demo
                                $demo_time = time() - rand(30, 2592000); // 30 seconds to 30 days
                                echo timeAgo(date('Y-m-d H:i:s', $demo_time));
                                ?>
                            </span>
                            <div class="view-count">👁️ <?php echo number_format(rand(1000, 50000)); ?> views</div>
                        </div>
                    </div>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="t">
            <h2>Trailers</h2>
            <a href="trailer.php">See more</a>
        </div>
        
        <div class="pop2">
            <?php if(!empty($trailers)): ?>
                <?php foreach($trailers as $trailer): ?>
                <div class="popp">
                    <div class="mage">
                        <img src="<?php echo htmlspecialchars($trailer['thumbnail_url']); ?>" 
                             alt="<?php echo htmlspecialchars($trailer['title']); ?>"
                             onerror="this.src='https://via.placeholder.com/300x450/333333/ffffff?text=No+Image'">
                        <div class="over">
                            <div class="btn4">
                                <a href="watch.php?id=<?php echo $trailer['id']; ?>&type=trailer"><i class="fas fa-play"></i></a>
                                <p><?php echo htmlspecialchars($trailer['title']); ?></p>
                            </div>
                        </div>
                    </div>
                    <h5><?php echo timeAgo($trailer['uploaded_at']); ?></h5>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Demo trailers with time ago -->
                <?php for($i = 0; $i < 4; $i++): ?>
                <div class="popp">
                    <div class="mage" style="background:#555;display:flex;align-items:center;justify-content:center;color:white;">
                        <i class="fas fa-play-circle" style="font-size:2em;opacity:0.5;"></i>
                    </div>
                    <h5>
                        <?php 
                        // Generate random time ago for demo trailers
                        $demo_time = time() - rand(60, 604800); // 1 minute to 1 week
                        echo timeAgo(date('Y-m-d H:i:s', $demo_time));
                        ?>
                    </h5>
                </div>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
        
        <footer>
            <!-- Your footer content -->
        </footer>
    </div>
    
<script>
    // Enhanced error-free JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // Hide loader when page loads
        const loader = document.querySelector('.loader');
        if (loader) {
            setTimeout(() => {
                loader.style.display = 'none';
            }, 1000);
        }

        // Fallback loader hide
        setTimeout(() => {
            if (loader && loader.style.display !== 'none') {
                loader.style.display = 'none';
            }
        }, 5000);

        // OMDB API integration
        const OMDB_API_KEY = '03acddc3-0751-4580-b715-789927c20fe0';
        
        async function searchMovies(query) {
            try {
                const response = await fetch(`https://www.omdbapi.com/?s=${encodeURIComponent(query)}&apikey=${OMDB_API_KEY}`);
                const data = await response.json();
                
                if(data.Search) {
                    displaySearchResults(data.Search);
                } else {
                    const resultsContainer = document.getElementById('searchResults');
                    if (resultsContainer) {
                        resultsContainer.innerHTML = '<p style="color: white; text-align: center; padding: 20px;">No movies found</p>';
                    }
                }
            } catch(error) {
                console.error('Error searching movies:', error);
                const resultsContainer = document.getElementById('searchResults');
                if (resultsContainer) {
                    resultsContainer.innerHTML = '<p style="color: white; text-align: center; padding: 20px;">Search error occurred</p>';
                }
            }
        }
        
        function displaySearchResults(movies) {
            const resultsContainer = document.getElementById('searchResults');
            if (!resultsContainer) return;
            
            resultsContainer.innerHTML = '';
            
            movies.forEach(movie => {
                const movieElement = document.createElement('div');
                movieElement.className = 'se';
                movieElement.innerHTML = `
                    <img src="${movie.Poster !== 'N/A' ? movie.Poster : 'https://via.placeholder.com/300x450/333/fff?text=No+Image'}" alt="${movie.Title}">
                    <h4 style="color: white; margin: 10px 0;">${movie.Title}</h4>
                    <p style="color: #ccc;">${movie.Year}</p>
                `;
                movieElement.addEventListener('click', () => {
                    window.location.href = `watch.php?omdb=${movie.imdbID}`;
                });
                resultsContainer.appendChild(movieElement);
            });
        }
        
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.trim();
                if(query.length > 2) {
                    searchMovies(query);
                } else {
                    const resultsContainer = document.getElementById('searchResults');
                    if (resultsContainer) {
                        resultsContainer.innerHTML = '';
                    }
                }
            });
        }

        // Search modal functionality
        const closeSearch = document.querySelector('.sea .cl');
        const openSearch = document.getElementById('sea');
        const searchModal = document.querySelector('.sea');

        if (closeSearch && searchModal) {
            closeSearch.addEventListener('click', function() {
                searchModal.style.transform = 'translateY(200%)';
            });
        }

        if (openSearch && searchModal) {
            openSearch.addEventListener('click', function(e) {
                e.preventDefault();
                searchModal.style.transform = 'translateY(0)';
                if (searchInput) searchInput.focus();
            });
        }

        // Popular videos carousel
        const leftArrow = document.querySelector('.left');
        const rightArrow = document.querySelector('.right');
        const popContainer = document.querySelector('.pop');

        if (leftArrow && rightArrow && popContainer) {
            leftArrow.addEventListener('click', () => {
                popContainer.scrollLeft -= 300;
            });

            rightArrow.addEventListener('click', () => {
                popContainer.scrollLeft += 300;
            });
        }

        // Fix for play button positioning
        document.querySelectorAll('.other a').forEach(link => {
            link.style.position = 'absolute';
            link.style.top = '50%';
            link.style.left = '50%';
            link.style.transform = 'translate(-50%, -50%)';
            link.style.zIndex = '10';
        });

        console.log('ARNET Movies loaded successfully');
    });
</script>
</body>
</html>