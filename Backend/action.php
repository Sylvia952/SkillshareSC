<?php
// action.php - Gère les likes et les commentaires

require 'db_config.php'; 
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

$data = json_decode(file_get_contents("php://input"), true);

// 1. VÉRIFICATION DE L'AUTHENTIFICATION ET DES DONNÉES
if (!isset($data['user_id'], $data['action'], $data['video_id'])) {
    http_response_code(400); 
    die(json_encode(['success' => false, 'message' => 'Données d\'interaction incomplètes (user_id, action, video_id).']));
}

$user_id = $data['user_id']; // ID de l'utilisateur connecté (simulé par le Frontend)
$video_id = $data['video_id'];
$action = strtolower($data['action']); // 'like', 'unlike', ou 'comment'

try {
    // -----------------------------------------------------
    // A. ACTION LIKE / UNLIKE
    // -----------------------------------------------------
    if ($action === 'like' || $action === 'unlike') {
        
        if ($action === 'like') {
            // Tente d'ajouter un like (INSERT IGNORE évite les doublons grâce à la clé unique dans la table 'likes')
            $sql = "INSERT IGNORE INTO likes (user_id, video_id) VALUES (:user_id, :video_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'video_id' => $video_id]);
            
            $response_message = ($stmt->rowCount() > 0) ? 'Vidéo likée.' : 'Déjà likée.';
        
        } else { // action est 'unlike'
            // Supprime le like
            $sql = "DELETE FROM likes WHERE user_id = :user_id AND video_id = :video_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'video_id' => $video_id]);
            $response_message = 'Like retiré.';
        }

        http_response_code(200);
        echo json_encode(['success' => true, 'message' => $response_message]);
        
    // -----------------------------------------------------
    // B. ACTION COMMENT
    // -----------------------------------------------------
    } elseif ($action === 'comment') {
        
        if (!isset($data['content']) || empty(trim($data['content']))) {
             http_response_code(400); 
             die(json_encode(['success' => false, 'message' => 'Contenu du commentaire manquant.']));
        }
        $content = trim($data['content']);

        // Ajout du commentaire dans la table 'comments'
        $sql = "INSERT INTO comments (video_id, user_id, content) VALUES (:video_id, :user_id, :content)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['video_id' => $video_id, 'user_id' => $user_id, 'content' => $content]);

        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Commentaire ajouté.', 'comment_id' => $pdo->lastInsertId()]);
        
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action non reconnue.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "Erreur DB: " . $e->getMessage()]);
}
?>