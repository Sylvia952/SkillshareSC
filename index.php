<?php
include 'config.php';
include 'includes/header.php';

// Récupérer toutes les vidéos avec le pseudo utilisateur ET le nombre de commentaires
$stmt = $pdo->query("
    SELECT v.*, 
           u.last_name AS pseudo, 
           u.id AS user_id,
           u.profile_picture_url,
           COUNT(c.id) AS comment_count
    FROM videos v 
    JOIN users u ON v.user_id = u.id 
    LEFT JOIN comments c ON v.id = c.video_id
    GROUP BY v.id
    ORDER BY v.id DESC
    LIMIT 20
");

$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Traitement AJAX des likes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'like') {
        $video_id = intval($_POST['video_id']);
        $stmt = $pdo->prepare("UPDATE videos SET likes = COALESCE(likes, 0) + 1 WHERE id = ?");
        $stmt->execute([$video_id]);
        
        // Récupérer le nouveau nombre de likes
        $stmt = $pdo->prepare("SELECT likes FROM videos WHERE id = ?");
        $stmt->execute([$video_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'likes' => $result['likes']]);
        exit;
    }
    
    if ($_POST['action'] === 'comment_like') {
        $comment_id = intval($_POST['comment_id']);
        
        try {
            // Vérifier si la colonne likes existe
            $checkStmt = $pdo->prepare("SHOW COLUMNS FROM comments LIKE 'likes'");
            $checkStmt->execute();
            $columnExists = $checkStmt->fetch();
            
            if (!$columnExists) {
                $pdo->exec("ALTER TABLE comments ADD COLUMN likes INT DEFAULT 0");
            }
            
            $stmt = $pdo->prepare("UPDATE comments SET likes = COALESCE(likes, 0) + 1 WHERE id = ?");
            $stmt->execute([$comment_id]);
            
            $stmt = $pdo->prepare("SELECT likes FROM comments WHERE id = ?");
            $stmt->execute([$comment_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'likes' => $result['likes']]);
            exit;
            
        } catch (PDOException $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    
    if ($_POST['action'] === 'comment' && isset($_POST['video_id']) && isset($_POST['comment'])) {
        $video_id = intval($_POST['video_id']);
        $comment = htmlspecialchars(trim($_POST['comment']));
        
        $stmt = $pdo->prepare("SELECT user_id FROM videos WHERE id = ?");
        $stmt->execute([$video_id]);
        $video = $stmt->fetch(PDO::FETCH_ASSOC);
        $user_id = $video['user_id'];
        
        if (!empty($comment) && $user_id) {
            $stmt = $pdo->prepare("INSERT INTO comments (video_id, user_id, comment, created_at, likes) VALUES (?, ?, ?, NOW(), 0)");
            $stmt->execute([$video_id, $user_id, $comment]);
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniTok</title>
    <style>
        body {
            background: #111;
            color: #fff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .feed-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 40px;
            padding-bottom: 40px;
        }

        .video-card {
            position: relative;
            width: 400px;
            height: 700px;
            overflow: hidden;
            border-radius: 15px;
            background: #000;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
        }

        .video-card video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 15px;
        }

        .video-info {
            position: absolute;
            bottom: 20px;
            left: 15px;
            color: white;
            z-index: 2;
        }

        .video-info h5 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .video-info p {
            margin: 2px 0;
            font-size: 14px;
        }

        .video-info small {
            font-size: 12px;
            color: #ccc;
        }

        /* BOUTONS TIKTOK STYLE AMÉLIORÉ */
        .video-actions {
            position: absolute;
            right: 15px;
            bottom: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            z-index: 3;
        }
        
        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .tiktok-btn {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            border: none;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 24px;
            position: relative;
            overflow: hidden;
        }

        .tiktok-btn:hover {
            transform: scale(1.1);
            background: rgba(255, 255, 255, 0.25);
        }

        .tiktok-btn:active {
            transform: scale(0.95);
        }

        .tiktok-btn.liked {
            background: rgba(255, 71, 87, 0.3);
        }

        .tiktok-btn.liked::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle, rgba(255, 71, 87, 0.4) 0%, transparent 70%);
            animation: pulse 0.6s ease-out;
        }

        @keyframes pulse {
            0% { transform: scale(0.8); opacity: 1; }
            100% { transform: scale(2); opacity: 0; }
        }

        .action-count {
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
        }

        .like-animation {
            position: absolute;
            font-size: 70px;
            opacity: 0;
            pointer-events: none;
            animation: likeBurst 1.2s ease-out;
            z-index: 10;
        }

        @keyframes likeBurst {
            0% {
                transform: scale(0) translateY(0) rotate(0deg);
                opacity: 1;
            }
            50% {
                opacity: 1;
                transform: scale(1.2) translateY(-30px) rotate(10deg);
            }
            100% {
                transform: scale(1.5) translateY(-80px) rotate(20deg);
                opacity: 0;
            }
        }

        .text-center {
            text-align: center;
            margin-top: 20px;
        }

        .text-primary {
            color: #0d6efd;
        }

        .text-secondary {
            color: #aaa;
        }

        /* MODAL TIKTOK AMÉLIORÉ */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .tiktok-modal {
            width: 100%;
            height: 100%;
            max-width: 500px;
            background: #000;
            display: flex;
            flex-direction: column;
            position: relative;
            border-radius: 0;
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #000;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .modal-title {
            font-size: 18px;
            font-weight: bold;
            color: white;
        }

        .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 28px;
            cursor: pointer;
            padding: 5px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.3s;
        }

        .close-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .comments-list {
            flex: 1;
            overflow-y: auto;
            padding: 0;
        }

        .comment-item {
            display: flex;
            gap: 12px;
            padding: 15px 20px;
            border-bottom: 1px solid #222;
            transition: background 0.3s;
        }

        .comment-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            flex-shrink: 0;
            overflow: hidden;
        }

        .comment-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .comment-content {
            flex: 1;
        }

        .comment-author {
            font-weight: bold;
            color: white;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .comment-text {
            color: #fff;
            line-height: 1.4;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .comment-time {
            font-size: 12px;
            color: #888;
            margin-bottom: 8px;
        }

        .comment-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .comment-like-btn {
            background: none;
            border: none;
            color: #888;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            padding: 6px 12px;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .comment-like-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .comment-like-btn.liked {
            color: #ff4757;
            background: rgba(255, 71, 87, 0.1);
        }

        .comment-form-container {
            border-top: 1px solid #333;
            padding: 15px 20px;
            background: #000;
            position: sticky;
            bottom: 0;
        }

        .input-group {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .comment-input {
            flex: 1;
            min-height: 45px;
            max-height: 120px;
            resize: none;
            padding: 12px 16px;
            border: 1px solid #444;
            border-radius: 25px;
            background: #333;
            color: white;
            font-family: inherit;
            font-size: 14px;
            outline: none;
            transition: border-color 0.3s;
        }

        .comment-input:focus {
            border-color: #00F2EA;
        }

        .submit-btn {
            background: linear-gradient(45deg, #FF0050, #00F2EA);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 12px 24px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: transform 0.3s;
        }

        .submit-btn:hover {
            transform: scale(1.05);
        }

        .comments-list::-webkit-scrollbar {
            width: 4px;
        }

        .comments-list::-webkit-scrollbar-track {
            background: #000;
        }

        .comments-list::-webkit-scrollbar-thumb {
            background: #555;
            border-radius: 2px;
        }

        .share-modal {
            max-width: 400px;
            background: #1a1a1a;
            border-radius: 15px;
            overflow: hidden;
        }

        .share-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            padding: 20px;
        }

        .share-option {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 15px;
            border-radius: 10px;
            transition: background 0.3s;
        }

        .share-option:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .share-icon {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .share-label {
            font-size: 12px;
        }

        /* Overlay de dégradé pour mieux voir les infos */
        .video-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 150px;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            pointer-events: none;
            z-index: 1;
            border-radius: 0 0 15px 15px;
        }
    </style>
</head>
<body>

<div class="text-center mb-4">
    <h1 class="fw-bold text-primary">🎬 Bienvenue sur UniTok</h1>
    <p class="text-secondary">Découvre les vidéos des étudiants de SONOU</p>
</div>

<div class="feed-container">
    <?php foreach ($videos as $video): ?>
        <div class="video-card">
            <!-- Overlay pour mieux voir les infos -->
            <div class="video-overlay"></div>
            
            <video controls>
                <source src="<?= htmlspecialchars($video['fichier']) ?>" type="video/mp4">
                Votre navigateur ne supporte pas la lecture vidéo.
            </video>
            
            <div class="video-info">
                <h5>@<?= htmlspecialchars($video['pseudo']) ?></h5>
                <p><?= htmlspecialchars($video['titre']) ?></p>
                <?php if($video['description']): ?>
                    <small><?= htmlspecialchars($video['description']) ?></small>
                <?php endif; ?>
            </div>

            <!-- BOUTONS TIKTOK AMÉLIORÉS -->
            <div class="video-actions">
                <!-- Bouton Like -->
                <div class="action-btn">
                    <button class="tiktok-btn like-btn" onclick="likeVideo(<?= $video['id'] ?>, this)" title="J'aime">
                        ❤️
                    </button>
                    <div class="action-count" id="like-count-<?= $video['id'] ?>"><?= $video['likes'] ?? 0 ?></div>
                </div>

                <!-- Bouton Commentaire -->
                <div class="action-btn">
                    <button class="tiktok-btn" onclick="openCommentModal(<?= $video['id'] ?>)" title="Commentaire">
                        💬
                    </button>
                    <div class="action-count" id="comment-count-<?= $video['id'] ?>"><?= $video['comment_count'] ?? 0 ?></div>
                </div>

                <!-- Bouton Partager -->
                <div class="action-btn">
                    <button class="tiktok-btn" onclick="openShareModal(<?= $video['id'] ?>, '<?= htmlspecialchars($video['titre']) ?>', '<?= htmlspecialchars($video['fichier']) ?>')" title="Partager">
                        🔁
                    </button>
                    <div class="action-count">Partager</div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal Commentaires TikTok -->
<div id="commentModal" class="modal">
    <div class="modal-content tiktok-modal">
        <div class="modal-header">
            <div class="modal-title">Commentaires</div>
            <button class="close-btn" onclick="closeCommentModal()">&times;</button>
        </div>
        
        <div class="comments-list" id="commentsList">
            <div style="text-align: center; color: #888; padding: 40px;">Chargement...</div>
        </div>
        
        <div class="comment-form-container">
            <form method="POST" class="comment-form" onsubmit="submitComment(event)">
                <input type="hidden" name="action" value="comment">
                <input type="hidden" name="video_id" id="commentVideoId">
                <div class="input-group">
                    <textarea class="comment-input" name="comment" placeholder="Ajoutez un commentaire..." rows="1" required></textarea>
                    <button type="submit" class="submit-btn">Publier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Partage -->
<div id="shareModal" class="modal">
    <div class="modal-content share-modal">
        <div class="modal-header">
            <div class="modal-title">Partager la vidéo</div>
            <button class="close-btn" onclick="closeShareModal()">&times;</button>
        </div>
        <div class="share-options">
            <button class="share-option" onclick="copyLink()">
                <div class="share-icon">📋</div>
                <div class="share-label">Copier</div>
            </button>
            <button class="share-option" onclick="shareOnWhatsApp()">
                <div class="share-icon">💬</div>
                <div class="share-label">WhatsApp</div>
            </button>
            <button class="share-option" onclick="shareOnFacebook()">
                <div class="share-icon">👥</div>
                <div class="share-label">Facebook</div>
            </button>
            <button class="share-option" onclick="shareOnTwitter()">
                <div class="share-icon">🐦</div>
                <div class="share-label">Twitter</div>
            </button>
            <button class="share-option" onclick="shareOnInstagram()">
                <div class="share-icon">📷</div>
                <div class="share-label">Instagram</div>
            </button>
            <button class="share-option" onclick="shareViaEmail()">
                <div class="share-icon">✉️</div>
                <div class="share-label">Email</div>
            </button>
        </div>
    </div>
</div>

<script>
    // Fonction pour liker une vidéo avec animation TikTok
    async function likeVideo(videoId, button) {
        // Créer l'animation de cœur
        const heart = document.createElement('div');
        heart.className = 'like-animation';
        heart.innerHTML = '❤️';
        heart.style.position = 'absolute';
        heart.style.left = (button.offsetLeft + 25) + 'px';
        heart.style.top = (button.offsetTop + 25) + 'px';
        button.parentElement.appendChild(heart);
        
        // Animation du bouton
        button.classList.add('liked');
        button.style.transform = 'scale(1.2)';
        
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 300);
        
        // Envoyer la requête AJAX
        try {
            const formData = new FormData();
            formData.append('action', 'like');
            formData.append('video_id', videoId);
            
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                const likeCount = document.getElementById(`like-count-${videoId}`);
                likeCount.textContent = result.likes;
            }
        } catch (error) {
            console.error('Erreur:', error);
        }
        
        // Nettoyer l'animation
        setTimeout(() => heart.remove(), 1200);
    }

    // Fonction pour liker un commentaire
    async function likeComment(commentId, button) {
        try {
            const formData = new FormData();
            formData.append('action', 'comment_like');
            formData.append('comment_id', commentId);
            
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                const likeCountElement = button.querySelector('span');
                if (likeCountElement) {
                    likeCountElement.textContent = result.likes;
                }
                
                button.classList.add('liked');
                button.style.transform = 'scale(1.1)';
                setTimeout(() => {
                    button.style.transform = 'scale(1)';
                }, 300);
            }
        } catch (error) {
            console.error('Erreur:', error);
        }
    }

    // Fonctions pour les modals
    async function openCommentModal(videoId) {
        document.getElementById('commentVideoId').value = videoId;
        document.getElementById('commentModal').style.display = 'flex';
        await loadComments(videoId);
    }

    function closeCommentModal() {
        document.getElementById('commentModal').style.display = 'none';
    }

    async function loadComments(videoId) {
        try {
            const response = await fetch(`get_comments.php?video_id=${videoId}`);
            const comments = await response.json();
            
            const commentsList = document.getElementById('commentsList');
            commentsList.innerHTML = '';
            
            if (comments.length === 0) {
                commentsList.innerHTML = '<div style="text-align: center; color: #888; padding: 40px;">Aucun commentaire pour le moment</div>';
                return;
            }
            
            comments.forEach(comment => {
                const commentElement = document.createElement('div');
                commentElement.className = 'comment-item';
                
                const timeAgo = getTimeAgo(comment.created_at);
                const avatarContent = comment.profile_picture_url 
                    ? `<img src="${comment.profile_picture_url}" alt="${comment.pseudo}" onerror="this.style.display='none'">`
                    : `<span>${comment.pseudo.charAt(0).toUpperCase()}</span>`;
                
                commentElement.innerHTML = `
                    <div class="comment-avatar">${avatarContent}</div>
                    <div class="comment-content">
                        <div class="comment-author">@${comment.pseudo}</div>
                        <div class="comment-text">${comment.comment}</div>
                        <div class="comment-time">${timeAgo}</div>
                        <div class="comment-actions">
                            <button class="comment-like-btn" onclick="likeComment(${comment.id}, this)" title="Like ce commentaire">
                                ❤️ <span>${comment.likes || 0}</span>
                            </button>
                        </div>
                    </div>
                `;
                
                commentsList.appendChild(commentElement);
            });
        } catch (error) {
            console.error('Erreur:', error);
            document.getElementById('commentsList').innerHTML = '<div style="text-align: center; color: red; padding: 40px;">Erreur de chargement</div>';
        }
    }

    async function submitComment(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        
        try {
            const response = await fetch('', {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                await loadComments(document.getElementById('commentVideoId').value);
                form.querySelector('.comment-input').value = '';
                
                // Mettre à jour le compteur de commentaires
                const videoId = document.getElementById('commentVideoId').value;
                const commentCount = document.getElementById(`comment-count-${videoId}`);
                const currentCount = parseInt(commentCount.textContent) || 0;
                commentCount.textContent = currentCount + 1;
            }
        } catch (error) {
            console.error('Erreur:', error);
        }
    }

    // Partage
    let currentVideoData = {};
    
    function openShareModal(videoId, title, videoUrl) {
        currentVideoData = {
            id: videoId,
            title: title,
            url: window.location.origin + '/' + videoUrl
        };
        document.getElementById('shareModal').style.display = 'flex';
    }

    function closeShareModal() {
        document.getElementById('shareModal').style.display = 'none';
    }

    async function copyLink() {
        try {
            await navigator.clipboard.writeText(currentVideoData.url);
            alert('✅ Lien copié !');
            closeShareModal();
        } catch (error) {
            const tempInput = document.createElement('input');
            tempInput.value = currentVideoData.url;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);
            alert('✅ Lien copié !');
            closeShareModal();
        }
    }

    function shareOnWhatsApp() {
        const text = `Regarde cette vidéo sur UniTok: ${currentVideoData.title} - ${currentVideoData.url}`;
        window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank');
        closeShareModal();
    }

    function shareOnFacebook() {
        const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentVideoData.url)}`;
        window.open(url, '_blank');
        closeShareModal();
    }

    function shareOnTwitter() {
        const text = `Regarde cette vidéo sur UniTok: ${currentVideoData.title}`;
        const url = `https://twitter.com/intent/tweet?text=${encodeURIComponent(text)}&url=${encodeURIComponent(currentVideoData.url)}`;
        window.open(url, '_blank');
        closeShareModal();
    }

    function shareOnInstagram() {
        alert('📱 Partage sur Instagram - Fonctionnalité à venir');
    }

    function shareViaEmail() {
        const subject = `Regarde cette vidéo sur UniTok: ${currentVideoData.title}`;
        const body = `Je te partage cette vidéo: ${currentVideoData.url}`;
        window.open(`mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`);
        closeShareModal();
    }

    // Utilitaires
    function getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMs / 3600000);
        const diffDays = Math.floor(diffMs / 86400000);
        
        if (diffMins < 1) return 'À l\'instant';
        if (diffMins < 60) return `Il y a ${diffMins} min`;
        if (diffHours < 24) return `Il y a ${diffHours} h`;
        if (diffDays < 7) return `Il y a ${diffDays} j`;
        
        return date.toLocaleDateString('fr-FR');
    }

    // Gestion des événements
    window.onclick = function(event) {
        if (event.target === document.getElementById('commentModal')) closeCommentModal();
        if (event.target === document.getElementById('shareModal')) closeShareModal();
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCommentModal();
            closeShareModal();
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>