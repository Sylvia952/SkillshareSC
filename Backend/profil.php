<?php
// profil.php - Récupère les détails d'un utilisateur

require 'db_config.php'; 
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

// Récupérer l'ID utilisateur soit depuis l'URL (GET), soit depuis le corps (POST)
$user_id = $_GET['user_id'] ?? ($_POST['user_id'] ?? null);

if (!$user_id) {
    http_response_code(400); 
    die(json_encode(['success' => false, 'message' => 'ID utilisateur manquant.']));
}

try {
    // 1. RÉCUPÉRATION DES DÉTAILS DE L'UTILISATEUR
    // (Exclure le password_hash par sécurité !)
    $stmt = $pdo->prepare("
        SELECT 
            user_id, matricule_number, last_name, first_name, 
            filiere, email, username, profile_picture_url, bio, created_at
        FROM 
            users 
        WHERE 
            user_id = :user_id
    ");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        die(json_encode(['success' => false, 'message' => 'Utilisateur non trouvé.']));
    }

    // 2. RÉCUPÉRATION DES VIDÉOS DE CET UTILISATEUR
    $stmt_videos = $pdo->prepare("
        SELECT video_id, title, video_url, uploaded_at, views 
        FROM videos 
        WHERE user_id = :user_id
        ORDER BY uploaded_at DESC
    ");
    $stmt_videos->execute(['user_id' => $user_id]);
    $user_videos = $stmt_videos->fetchAll(PDO::FETCH_ASSOC);

    // 3. COMBINER ET ENVOYER LA RÉPONSE
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'user_details' => $user,
        'user_videos' => $user_videos,
        'message' => 'Profil utilisateur récupéré avec succès.'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "Erreur DB: " . $e->getMessage()]);
}
?>