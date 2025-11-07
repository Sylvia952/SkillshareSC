<?php
include 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stream_key = $_POST['stream_key'] ?? '';
    
    if (!empty($stream_key)) {
        $stmt = $pdo->prepare("UPDATE lives SET is_live = FALSE, ended_at = NOW() WHERE stream_key = :stream_key");
        $stmt->execute(['stream_key' => $stream_key]);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Stream key manquant']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
}
?>