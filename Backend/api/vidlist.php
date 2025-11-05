<?php
// vidlist.php - Le script qui alimente le flux vidéo pour SkillShareSC

require 'config.php'; 
// Assurez-vous que le chemin vers db_config.php est correct

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

try {
    // 1. REQUÊTE SQL AVEC JOINTURE
    // On sélectionne les champs critiques nécessaires pour l'affichage du Feed.
    // L.id est une jointure pour compter le nombre de likes de chaque vidéo.
    $stmt = $pdo->prepare("
        SELECT 
            v.video_id, 
            v.title, 
            v.description, 
            v.video_url, 
            v.uploaded_at,
            v.views,
            u.username,
            u.profile_picture_url,
            u.filiere,
            COUNT(l.like_id) AS like_count  -- Compte le nombre de likes
        FROM 
            videos v
        JOIN 
            users u ON v.user_id = u.user_id
        LEFT JOIN 
            likes l ON v.video_id = l.video_id  -- Jointure LEFT pour inclure les vidéos sans likes
        GROUP BY 
            v.video_id, v.title, v.description, v.video_url, v.uploaded_at, v.views, 
            u.username, u.profile_picture_url, u.filiere
        ORDER BY 
            v.uploaded_at DESC -- Les plus récentes en premier
        LIMIT 50 -- Limiter pour le MVP
    ");
    
    $stmt->execute();
    
    // 2. RÉCUPÉRATION DES RÉSULTATS
    $videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. SUCCÈS : ENVOI DU FLUX EN JSON
    http_response_code(200); // OK
    echo json_encode([
        'success' => true,
        'message' => 'Flux de vidéos récupéré avec succès.',
        'feed' => $videos
    ]);

} catch (PDOException $e) {
    // 4. GESTION DES ERREURS
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Erreur serveur lors de la récupération du flux." . $e->getMessage()
    ]);
}
?>