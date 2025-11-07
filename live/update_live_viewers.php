<?php
include 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stream_key = $_POST['stream_key'] ?? '';
    $viewers_count = intval($_POST['viewers_count'] ?? 0);
    
    if (!empty($stream_key)) {
        $stmt = $pdo->prepare("UPDATE lives SET viewers_count = :viewers WHERE stream_key = :stream_key");
        $stmt->execute([
            'viewers' => $viewers_count,
            'stream_key' => $stream_key
        ]);
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Stream key manquant']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
}
?>