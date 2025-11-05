<?php
// media.php - Gère l'upload de Photos de profil et de Vidéos

// REMPLACER par le nouveau nom de fichier de configuration
require 'config.php'; 

// Définir les headers pour la communication API (CORS)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestion de la requête OPTIONS (nécessaire pour CORS avant POST)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 1. VÉRIFICATION DE L'AUTHENTIFICATION (SIMPLIFIÉE)
if (!isset($_POST['user_id']) || empty($_POST['user_id'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Erreur: Identifiant utilisateur (user_id) manquant.']));
}
$user_id = (int)$_POST['user_id'];
$media_type = $_POST['media_type'] ?? 'video'; // Attendu: 'video' ou 'profile'

// 2. VÉRIFICATION DU FICHIER REÇU
if (!isset($_FILES['media']) || $_FILES['media']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Aucun fichier reçu ou erreur d\'upload: ' . $_FILES['media']['error']]));
}

$file = $_FILES['media'];
$file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$file_extension = strtolower($file_extension);

// Définir les extensions acceptées
$allowed_images = ['jpg', 'jpeg', 'png'];
$allowed_videos = ['mp4', 'mov', 'avi'];

// 3. DÉTERMINER LE CHEMIN ET VÉRIFIER L'EXTENSION
$unique_filename = uniqid('media_') . '.' . $file_extension;

if ($media_type === 'profile') {
    if (!in_array($file_extension, $allowed_images)) {
        http_response_code(400); die(json_encode(['success' => false, 'message' => 'Format photo de profil non supporté.']));
    }
    $upload_dir = '../uploads/profiles/';
    $db_column = 'profile_picture_url';
    $db_table = 'users';
    
} else { // media_type est 'video' par défaut
    if (!in_array($file_extension, $allowed_videos)) {
        http_response_code(400); die(json_encode(['success' => false, 'message' => 'Format vidéo non supporté.']));
    }
    $upload_dir = '../uploads/videos/';
    $db_table = 'videos';
    // Champ Description est requis pour les vidéos
    $title = $_POST['title'] ?? 'Vidéo sans titre';
    $description = $_POST['description'] ?? '';
}

// Chemin absolu pour l'enregistrement du fichier
$target_path = __DIR__ . '/' . $upload_dir . $unique_filename; 
// L'URL d'accès public (utilisée par le Frontend)
$public_url = "http://localhost/skillsharesc/backend/uploads/" . ($media_type === 'profile' ? 'profiles/' : 'videos/') . $unique_filename; 


// 4. DÉPLACEMENT DU FICHIER TÉLÉVERSÉ
if (move_uploaded_file($file['tmp_name'], $target_path)) {

    try {
        // 5. ENREGISTREMENT DANS LA BASE DE DONNÉES
        if ($media_type === 'profile') {
            // Mise à jour de la photo de profil
            $stmt = $pdo->prepare("UPDATE users SET profile_picture_url = :url WHERE user_id = :user_id");
            $stmt->execute(['url' => $public_url, 'user_id' => $user_id]);
            $message = 'Photo de profil mise à jour.';
            
        } else {
            // Création d'une nouvelle entrée vidéo
            $stmt = $pdo->prepare("
                INSERT INTO videos (user_id, title, description, video_url) 
                VALUES (:user_id, :title, :description, :url)
            ");
            $stmt->execute([
                'user_id' => $user_id, 
                'title' => $title, 
                'description' => $description,
                'url' => $public_url
            ]);
            $message = 'Vidéo uploadée et enregistrée !';
        }

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'url' => $public_url
        ]);

    } catch (PDOException $e) {
        // En cas d'échec DB, supprimer le fichier du serveur
        unlink($target_path); 
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => 'Erreur DB après upload : ' . $e->getMessage()]));
    }

} else {
    // Échec du déplacement (problème de permissions de dossier)
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Erreur serveur : impossible de déplacer le fichier. Vérifiez les permissions du dossier uploads.']));
}
?>