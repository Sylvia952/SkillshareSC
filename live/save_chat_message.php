<?php
include 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stream_key = $_POST['stream_key'] ?? '';
    $user_id = intval($_POST['user_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    
    if (!empty($stream_key) && !empty($message) && $user_id > 0) {
        // Créer la table live_chats si elle n'existe pas
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS live_chats (
                id INT PRIMARY KEY AUTO_INCREMENT,
                stream_key VARCHAR(100) NOT NULL,
                user_id INT NOT NULL,
                message TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        
        $stmt = $pdo->prepare("INSERT INTO live_chats (stream_key, user_id, message) VALUES (:stream_key, :user_id, :message)");
        $stmt->execute([
            'stream_key' => $stream_key,
            'user_id' => $user_id,
            'message' => $message
        ]);
        
        echo json_encode(['success' => true, 'message_id' => $pdo->lastInsertId()]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Données manquantes']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
}
?>