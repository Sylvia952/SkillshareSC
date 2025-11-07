<?php
include 'config.php';
if(session_status()===PHP_SESSION_NONE) session_start();

$stmt = $pdo->query("SELECT l.id, l.titre, l.date_debut, l.user_id, u.last_name AS pseudo 
                     FROM lives l 
                     JOIN users u ON l.user_id = u.id 
                     WHERE l.active=1 
                     ORDER BY l.date_debut DESC");
$lives = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ajouter l'id de l'utilisateur connecté pour vérifier qui peut arrêter
$response = [
    'user_id' => $_SESSION['user_id'] ?? 0,
    'lives' => $lives
];

header('Content-Type: application/json');
echo json_encode($response);
