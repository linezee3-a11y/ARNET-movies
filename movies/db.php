<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'arnet_db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables if they don't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS featured_movies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        poster_url VARCHAR(500),
        video_url VARCHAR(500),
        omdb_id VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS videos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        thumbnail_url VARCHAR(500),
        video_url VARCHAR(500),
        category VARCHAR(100),
        size VARCHAR(50),
        views INT DEFAULT 0,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS trailers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        thumbnail_url VARCHAR(500),
        video_url VARCHAR(500),
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Insert default admin user if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    if($stmt->fetchColumn() == 0) {
        $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password_hash, email) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $hashed_password, 'admin@arnet.com']);
    }
    
    // Insert sample featured movies if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM featured_movies");
    if($stmt->fetchColumn() == 0) {
        $sample_movies = [
            ['Squid Game 2', 'The thrilling sequel to the popular series', 'https://via.placeholder.com/300x450/FF0000/FFFFFF?text=Squid+Game+2', 'https://example.com/squidgame2.mp4', 'tt1234567'],
            ['Feel Me in Your Pain', 'A dramatic romance story about connection', 'https://via.placeholder.com/300x450/00FF00/FFFFFF?text=Feel+Me', 'https://example.com/feelme.mp4', 'tt2345678'],
            ['Avengers: Endgame', 'Epic superhero conclusion to the Infinity Saga', 'https://via.placeholder.com/300x450/0000FF/FFFFFF?text=Avengers', 'https://example.com/avengers.mp4', 'tt3456789']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO featured_movies (title, description, poster_url, video_url, omdb_id) VALUES (?, ?, ?, ?, ?)");
        foreach($sample_movies as $movie) {
            $stmt->execute($movie);
        }
    }
    
    // Insert sample videos if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM videos");
    if($stmt->fetchColumn() == 0) {
        $sample_videos = [
            ['Avatar: The Way of Water', 'Sequel to the epic blue people movie', 'https://via.placeholder.com/300x450/00FFFF/000000?text=Avatar', 'https://example.com/avatar2.mp4', 'Sci-Fi', '2.5GB'],
            ['Black Panther: Wakanda Forever', 'Tribute to Chadwick Boseman', 'https://via.placeholder.com/300x450/000000/FFFFFF?text=Black+Panther', 'https://example.com/blackpanther2.mp4', 'Action', '2.3GB']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO videos (title, description, thumbnail_url, video_url, category, size) VALUES (?, ?, ?, ?, ?, ?)");
        foreach($sample_videos as $video) {
            $stmt->execute($video);
        }
    }
    
    // Insert sample trailers if table is empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM trailers");
    if($stmt->fetchColumn() == 0) {
        $sample_trailers = [
            ['Squid Game Season 2 Trailer', 'The games continue in this thrilling sequel', 'https://via.placeholder.com/300x450/FF0000/FFFFFF?text=Squid+Game+Trailer', 'https://example.com/squidgame-trailer.mp4'],
            ['Avatar 3 Teaser', 'Return to Pandora for new adventures', 'https://via.placeholder.com/300x450/00FFFF/000000?text=Avatar+3', 'https://example.com/avatar3-teaser.mp4']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO trailers (title, description, thumbnail_url, video_url) VALUES (?, ?, ?, ?)");
        foreach($sample_trailers as $trailer) {
            $stmt->execute($trailer);
        }
    }
    
} catch(PDOException $e) {
    // Silent fail - don't show errors
}
?>