<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$stream_key = $_GET['stream_key'] ?? '';

if (empty($stream_key)) {
    header("Location: lives.php");
    exit;
}

// Récupérer les infos du live
$stmt = $pdo->prepare("
    SELECT l.*, u.last_name, u.first_name, u.profile_picture, u.username
    FROM lives l 
    JOIN users u ON l.user_id = u.id 
    WHERE l.stream_key = :stream_key AND l.is_live = TRUE
");
$stmt->execute(['stream_key' => $stream_key]);
$live = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$live) {
    header("Location: lives.php");
    exit;
}

// Incrémenter le compteur de spectateurs
$stmt = $pdo->prepare("UPDATE lives SET viewers_count = viewers_count + 1 WHERE stream_key = :stream_key");
$stmt->execute(['stream_key' => $stream_key]);

// Récupérer les messages du chat pour ce live
$stmt = $pdo->prepare("
    SELECT c.*, u.last_name, u.profile_picture 
    FROM live_chats c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.stream_key = :stream_key 
    ORDER BY c.created_at ASC
");
$stmt->execute(['stream_key' => $stream_key]);
$chat_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($live['title']) ?> - UniTok Live</title>
    <style>
        body {
            background: #000;
            color: white;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .live-container {
            max-width: 100%;
            height: 100vh;
            display: flex;
        }
        .video-container {
            flex: 1;
            position: relative;
            background: #000;
        }
        #videoElement {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .chat-container {
            width: 400px;
            background: #1a1a1a;
            display: flex;
            flex-direction: column;
            border-left: 1px solid #333;
        }
        .chat-header {
            padding: 20px;
            background: #2a2a2a;
            border-bottom: 1px solid #333;
        }
        .chat-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .chat-input {
            padding: 20px;
            border-top: 1px solid #333;
            background: #2a2a2a;
        }
        .live-info {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(0,0,0,0.8);
            padding: 15px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            max-width: 400px;
        }
        .message {
            padding: 10px 15px;
            border-radius: 18px;
            background: #2a2a2a;
            margin-bottom: 8px;
            animation: fadeIn 0.3s ease;
        }
        .message.own {
            background: #dc3545;
            margin-left: 20px;
        }
        .message.other {
            background: #2a2a2a;
            margin-right: 20px;
        }
        .message-header {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }
        .user-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .username {
            font-weight: bold;
            font-size: 0.9em;
        }
        .message-content {
            font-size: 0.95em;
            line-height: 1.4;
        }
        .message-time {
            font-size: 0.7em;
            opacity: 0.7;
            margin-top: 3px;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .viewer-count {
            background: rgba(220, 53, 69, 0.9);
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.9em;
            margin-top: 10px;
        }
        .live-title {
            font-size: 1.3em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .streamer-info {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .streamer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="live-container">
        <div class="video-container">
            <video id="videoElement" controls autoplay playsinline>
                <!-- Le flux vidéo sera injecté via JavaScript -->
            </video>
            
            <div class="live-info">
                <div class="streamer-info">
                    <img src="<?= !empty($live['profile_picture']) ? htmlspecialchars($live['profile_picture']) : 'assets/img/default-avatar.png' ?>" 
                         alt="Streamer" class="streamer-avatar">
                    <div>
                        <div class="fw-bold">@<?= htmlspecialchars($live['last_name']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($live['first_name']) ?></small>
                    </div>
                </div>
                <div class="live-title"><?= htmlspecialchars($live['title']) ?></div>
                <?php if($live['description']): ?>
                    <div class="live-description small text-muted">
                        <?= htmlspecialchars($live['description']) ?>
                    </div>
                <?php endif; ?>
                <div class="viewer-count">
                    <i class="fas fa-users me-1"></i> 
                    <span id="viewerCount"><?= $live['viewers_count'] ?></span> spectateurs
                </div>
            </div>
        </div>

        <div class="chat-container">
            <div class="chat-header">
                <h5 class="mb-0">
                    <i class="fas fa-comments me-2"></i>Chat en direct
                    <span class="badge bg-danger ms-2" id="onlineCount">0</span>
                </h5>
            </div>
            
            <div class="chat-messages" id="chatMessages">
                <!-- Messages de bienvenue -->
                <div class="message system-message text-center text-muted">
                    <small>Bienvenue dans le chat live !</small>
                </div>
                <?php foreach($chat_messages as $message): ?>
                    <div class="message <?= ($_SESSION['user_id'] ?? null) == $message['user_id'] ? 'own' : 'other' ?>">
                        <div class="message-header">
                            <img src="<?= !empty($message['profile_picture']) ? htmlspecialchars($message['profile_picture']) : 'assets/img/default-avatar.png' ?>" 
                                 class="user-avatar" alt="User">
                            <span class="username">@<?= htmlspecialchars($message['last_name']) ?></span>
                        </div>
                        <div class="message-content"><?= htmlspecialchars($message['message']) ?></div>
                        <div class="message-time">
                            <?= date('H:i', strtotime($message['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if(isset($_SESSION['user_id'])): ?>
            <div class="chat-input">
                <div class="input-group">
                    <input type="text" id="messageInput" class="form-control bg-dark text-light border-dark" 
                           placeholder="Tapez votre message..." autocomplete="off">
                    <button class="btn btn-danger" id="sendMessage">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> Appuyez sur Entrée pour envoyer
                    </small>
                </div>
            </div>
            <?php else: ?>
            <div class="chat-input text-center">
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    <a href="login.php" class="alert-link">Connectez-vous</a> pour participer au chat
                </div>
                <a href="login.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-in-alt me-1"></i> Se connecter
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Simulation de flux vidéo live
        const videoElement = document.getElementById('videoElement');
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendMessage');
        const viewerCount = document.getElementById('viewerCount');
        const onlineCount = document.getElementById('onlineCount');

        // Fonction pour simuler un flux vidéo (dans un vrai système, utiliser WebRTC ou HLS)
        function initializeVideoStream() {
            // Dans un vrai système, vous connecteriez à un serveur WebRTC ou HLS
            // Pour cette démo, nous affichons un message
            videoElement.innerHTML = `
                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#000;">
                    <div class="text-center text-light">
                        <i class="fas fa-broadcast-tower fa-3x mb-3 text-danger"></i>
                        <h4>Live en cours</h4>
                        <p>Flux vidéo de @<?= htmlspecialchars($live['last_name']) ?></p>
                        <small class="text-muted">Simulation de streaming live</small>
                    </div>
                </div>
            `;
            
            // Simuler des changements de qualité
            simulateStreamQuality();
        }

        function simulateStreamQuality() {
            const qualities = [
                { resolution: '720p', bitrate: '2500kbps' },
                { resolution: '480p', bitrate: '1500kbps' },
                { resolution: '360p', bitrate: '800kbps' }
            ];
            
            setInterval(() => {
                const randomQuality = qualities[Math.floor(Math.random() * qualities.length)];
                // Simuler un changement de qualité
            }, 15000);
        }

        // Gestion du chat
        function addMessage(username, message, isOwn = false, avatar = null) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isOwn ? 'own' : 'other'}`;
            messageDiv.innerHTML = `
                <div class="message-header">
                    <img src="${avatar || 'assets/img/default-avatar.png'}" class="user-avatar" alt="User">
                    <span class="username">@${username}</span>
                </div>
                <div class="message-content">${message}</div>
                <div class="message-time">
                    ${new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}
                </div>
            `;
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            // Envoyer le message au serveur si c'est le propre message de l'utilisateur
            if (isOwn && <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>) {
                saveMessageToDatabase(message);
            }
        }

        function saveMessageToDatabase(message) {
            fetch('save_chat_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `stream_key=<?= $stream_key ?>&user_id=<?= $_SESSION['user_id'] ?? 0 ?>&message=${encodeURIComponent(message)}`
            });
        }

        // Messages automatiques simulés (autres utilisateurs)
        const sampleUsers = [
            { name: "Étudiant123", avatar: "assets/img/default-avatar.png" },
            { name: "CampusLife", avatar: "assets/img/default-avatar.png" },
            { name: "UniFan", avatar: "assets/img/default-avatar.png" },
            { name: "LiveViewer", avatar: "assets/img/default-avatar.png" }
        ];

        const sampleMessages = [
            "Super live ! 🎉",
            "Bonjour à tous ! 👋",
            "Quelqu'un peut expliquer le sujet ?",
            "Très intéressant !",
            "Merci pour ce live ! 🙏",
            "Je suis nouveau ici, salut !",
            "La qualité est excellente 👍",
            "Vous venez de quelle filière ?",
            "Quel est le prochain sujet ?",
            "Génial ! 😄"
        ];

        function simulateOtherUsersMessages() {
            setInterval(() => {
                if (Math.random() > 0.6) { // 40% de chance d'ajouter un message
                    const user = sampleUsers[Math.floor(Math.random() * sampleUsers.length)];
                    const message = sampleMessages[Math.floor(Math.random() * sampleMessages.length)];
                    addMessage(user.name, message, false, user.avatar);
                    updateOnlineCount();
                }
            }, 8000 + Math.random() * 12000); // Entre 8 et 20 secondes
        }

        function updateOnlineCount() {
            const currentCount = parseInt(onlineCount.textContent);
            const change = Math.random() > 0.5 ? 1 : -1;
            const newCount = Math.max(1, currentCount + change);
            onlineCount.textContent = newCount;
        }

        function updateViewerCount() {
            const currentCount = parseInt(viewerCount.textContent);
            const change = Math.floor(Math.random() * 3) - 1; // -1, 0, ou 1
            const newCount = Math.max(1, currentCount + change);
            viewerCount.textContent = newCount;
            
            // Mettre à jour périodiquement
            setTimeout(updateViewerCount, 10000 + Math.random() * 10000);
        }

        // Envoi de message
        function sendMessage() {
            const message = messageInput.value.trim();
            if (message) {
                addMessage("<?= $_SESSION['user_id'] ? htmlspecialchars($_SESSION['user_name'] ?? 'Vous') : 'Vous' ?>", message, true);
                messageInput.value = '';
                
                // Simulation de réponse après un délai
                setTimeout(() => {
                    if (Math.random() > 0.7) {
                        const randomUser = sampleUsers[Math.floor(Math.random() * sampleUsers.length)];
                        const responses = ["D'accord !", "Intéressant", "Merci !", "Je vois", "👍", "Génial !"];
                        addMessage(randomUser.name, responses[Math.floor(Math.random() * responses.length)], false, randomUser.avatar);
                    }
                }, 2000 + Math.random() * 3000);
            }
        }

        sendButton?.addEventListener('click', sendMessage);
        
        messageInput?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        // Initialisation
        initializeVideoStream();
        simulateOtherUsersMessages();
        updateViewerCount();
        
        // Mettre à jour le compteur en ligne initial
        onlineCount.textContent = Math.floor(Math.random() * 10) + 5;
        setInterval(updateOnlineCount, 15000);

        // Charger les nouveaux messages périodiquement
        setInterval(() => {
            // Dans un vrai système, vous feriez un appel AJAX pour récupérer les nouveaux messages
        }, 5000);

        // Plein écran
        videoElement.addEventListener('dblclick', function() {
            if (videoElement.requestFullscreen) {
                videoElement.requestFullscreen();
            }
        });
    </script>
</body>
</html>