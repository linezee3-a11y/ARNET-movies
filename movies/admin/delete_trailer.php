<?php
include("../db.php");
session_start();

// Simple auth check (you can remove this for testing)
// if(!isset($_SESSION['admin_logged_in'])) {
//     echo json_encode(['success' => false, 'message' => 'Not authorized']);
//     exit();
// }

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $trailer_id = $_POST['id'];
    
    try {
        // First get trailer info to delete files
        $stmt = $pdo->prepare("SELECT thumbnail_url, video_url FROM trailers WHERE id = ?");
        $stmt->execute([$trailer_id]);
        $trailer = $stmt->fetch();
        
        if($trailer) {
            // Delete files if they exist
            if(file_exists('../' . $trailer['thumbnail_url'])) {
                unlink('../' . $trailer['thumbnail_url']);
            }
            if(file_exists('../' . $trailer['video_url'])) {
                unlink('../' . $trailer['video_url']);
            }
            
            // Delete from database
            $stmt = $pdo->prepare("DELETE FROM trailers WHERE id = ?");
            $stmt->execute([$trailer_id]);
            
            echo json_encode(['success' => true, 'message' => 'Trailer deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Trailer not found']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>  