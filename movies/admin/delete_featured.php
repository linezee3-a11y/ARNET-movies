<?php
include("../db.php");
session_start();

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $movie_id = $_POST['id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM featured_movies WHERE id = ?");
        $stmt->execute([$movie_id]);
        
        header("Location: dashboard.php?success=Featured+movie+deleted");
        exit();
    } catch(PDOException $e) {
        header("Location: dashboard.php?error=Delete+failed");
        exit();
    }
} else {
    header("Location: dashboard.php");
    exit();
}
?>