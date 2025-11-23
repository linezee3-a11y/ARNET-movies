<?php
include("../db.php");
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple session check - remove for testing if needed
// if(!isset($_SESSION['admin_logged_in'])) {
//     echo json_encode(['success' => false, 'message' => 'Not authorized']);
//     exit();
// }

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    
    // Check if required fields are filled
    if(empty($title) || empty($description)) {
        echo json_encode(['success' => false, 'message' => 'Title and description are required']);
        exit();
    }
    
    // Handle file uploads
    $thumbnail_path = '';
    $video_path = '';
    
    // Upload thumbnail
    if(isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/thumbnails/';
        
        // Create directory if it doesn't exist
        if(!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $thumbnail_name = uniqid() . '_' . basename($_FILES['thumbnail']['name']);
        $thumbnail_path = 'uploads/thumbnails/' . $thumbnail_name;
        $full_thumbnail_path = '../' . $thumbnail_path;
        
        if(move_uploaded_file($_FILES['thumbnail']['tmp_name'], $full_thumbnail_path)) {
            // Thumbnail uploaded successfully
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload thumbnail']);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Thumbnail upload error: ' . $_FILES['thumbnail']['error']]);
        exit();
    }
    
    // Upload video
    if(isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/videos/';
        
        // Create directory if it doesn't exist
        if(!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $video_name = uniqid() . '_' . basename($_FILES['video']['name']);
        $video_path = 'uploads/videos/' . $video_name;
        $full_video_path = '../' . $video_path;
        
        if(move_uploaded_file($_FILES['video']['tmp_name'], $full_video_path)) {
            // Video uploaded successfully
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to upload video']);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Video upload error: ' . $_FILES['video']['error']]);
        exit();
    }
    
    // Insert into database
    try {
        $stmt = $pdo->prepare("INSERT INTO trailers (title, description, thumbnail_url, video_url) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $description, $thumbnail_path, $video_path]);
        
        echo json_encode(['success' => true, 'message' => 'Trailer uploaded successfully!']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>