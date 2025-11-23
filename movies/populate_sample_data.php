<?php
include("db.php");

echo "<h2>Populating Sample Data...</h2>";

try {
    // Clear existing data
    $pdo->exec("DELETE FROM featured_movies");
    $pdo->exec("DELETE FROM trailers");
    $pdo->exec("DELETE FROM videos");
    
    echo "Cleared existing data<br>";
    
    // Insert sample featured movies
    $featured_movies = [
        [
            'title' => 'Avengers: Endgame',
            'description' => 'The epic conclusion to the Infinity Saga where the Avengers take one final stand against Thanos.',
            'poster_url' => 'https://image.tmdb.org/t/p/w500/or06FN3Dka5tukK1e9sl16pB3iy.jpg',
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
            'omdb_id' => 'tt4154796'
        ],
        [
            'title' => 'Spider-Man: No Way Home',
            'description' => 'Spider-Man seeks the help of Doctor Strange when his secret identity is revealed to the world.',
            'poster_url' => 'https://image.tmdb.org/t/p/w500/1g0dhYtq4irTY1GPXvft6k4YLjm.jpg',
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
            'omdb_id' => 'tt10872600'
        ],
        [
            'title' => 'The Batman',
            'description' => 'Batman uncovers corruption in Gotham City while hunting the Riddler, a serial killer.',
            'poster_url' => 'https://image.tmdb.org/t/p/w500/74xTEgt7R36Fpooo50r9T25onhq.jpg',
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
            'omdb_id' => 'tt1877830'
        ],
        [
            'title' => 'Black Panther: Wakanda Forever',
            'description' => 'The nation of Wakanda fights to protect their home from intervening world powers.',
            'poster_url' => 'https://image.tmdb.org/t/p/w500/sv1xJUazXeYqALzczSZ3O6nkH75.jpg',
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerEscapes.mp4',
            'omdb_id' => 'tt9114286'
        ],
        [
            'title' => 'Avatar: The Way of Water',
            'description' => 'Jake Sully lives with his newfound family formed on the planet of Pandora.',
            'poster_url' => 'https://image.tmdb.org/t/p/w500/t6HIqrRAclMCA60NsSmeqe9RmNV.jpg',
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerFun.mp4',
            'omdb_id' => 'tt1630029'
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO featured_movies (title, description, poster_url, video_url, omdb_id) VALUES (?, ?, ?, ?, ?)");
    foreach($featured_movies as $movie) {
        $stmt->execute(array_values($movie));
    }
    echo "Added 5 featured movies<br>";
    
    // Insert sample trailers
    $trailers = [
        [
            'title' => 'Guardians of the Galaxy Vol. 3 Trailer',
            'description' => 'The final chapter of the Guardians of the Galaxy saga',
            'thumbnail_url' => 'https://image.tmdb.org/t/p/w500/r2J02Z2OpNTctfOSN1Ydgii51I3.jpg',
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4'
        ],
        [
            'title' => 'Ant-Man and The Wasp: Quantumania',
            'description' => 'Scott Lang and Hope Van Dyne explore the Quantum Realm',
            'thumbnail_url' => 'https://image.tmdb.org/t/p/w500/ngl2FKBlU4fhbdsrtdom9LVLBXw.jpg',
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerMeltdowns.mp4'
        ],
        [
            'title' => 'The Marvels Official Trailer',
            'description' => 'Carol Danvers teams up with Kamala Khan and Monica Rambeau',
            'thumbnail_url' => 'https://image.tmdb.org/t/p/w500/AgZZDOHm6O9zQ6ukSHMLiI4GaBd.jpg',
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/Sintel.mp4'
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO trailers (title, description, thumbnail_url, video_url) VALUES (?, ?, ?, ?)");
    foreach($trailers as $trailer) {
        $stmt->execute(array_values($trailer));
    }
    echo "Added 3 trailers<br>";
    
    // Insert sample videos
    $videos = [
        [
            'title' => 'Squid Game Season 2',
            'description' => 'The deadly games continue in this thrilling sequel',
            'thumbnail_url' => 'https://image.tmdb.org/t/p/w500/dDlEmu3EZ0Pgg93K2SVNLCjCSvE.jpg',
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/SubaruOutbackOnStreetAndDirt.mp4',
            'category' => 'Series',
            'size' => '1.2GB',
            'views' => 1500
        ],
        [
            'title' => 'Stranger Things Final Season',
            'description' => 'The epic conclusion to the Stranger Things saga',
            'thumbnail_url' => 'https://image.tmdb.org/t/p/w500/49WJfeN0moxb9IPfGn8AIqMGskD.jpg',
            'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
            'category' => 'Series',
            'size' => '1.5GB',
            'views' => 2300
        ]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO videos (title, description, thumbnail_url, video_url, category, size, views) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach($videos as $video) {
        $stmt->execute(array_values($video));
    }
    echo "Added 2 popular videos<br>";
    
    echo "<h3 style='color: green;'>Sample data populated successfully!</h3>";
    echo "<p><a href='index.php'>Go to Homepage</a> | <a href='admin/dashboard.php'>Go to Admin Dashboard</a></p>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'>Error: " . $e->getMessage() . "</h3>";
}
?>