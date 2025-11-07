<?php
include 'config.php';

header('Content-Type: application/json');

if (isset($_GET['video_id'])) {
    $video_id = intval($_GET['video_id']);
    
    try {
        // Vérifier si la colonne likes existe
        $checkStmt = $pdo->prepare("SHOW COLUMNS FROM comments LIKE 'likes'");
        $checkStmt->execute();
        $columnExists = $checkStmt->fetch();
        
        if (!$columnExists) {
            $pdo->exec("ALTER TABLE comments ADD COLUMN likes INT DEFAULT 0");
        }
        
        $stmt = $pdo->prepare("
            SELECT c.id, c.video_id, c.user_id, c.comment, c.created_at, 
                   COALESCE(c.likes, 0) as likes,
                   u.last_name AS pseudo, 
                   u.profile_picture_url
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.video_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$video_id]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($comments);
        
    } catch (PDOException $e) {
        error_log("Erreur get_comments: " . $e->getMessage());
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}
?>