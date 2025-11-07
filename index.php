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
    header('Content-Type: application/json');

    if ($_POST['action'] === 'like') {
        $video_id = intval($_POST['video_id']);
        $stmt = $pdo->prepare("UPDATE videos SET likes = COALESCE(likes,0)+1 WHERE id = ?");
        $stmt->execute([$video_id]);
        $stmt = $pdo->prepare("SELECT likes FROM videos WHERE id = ?");
        $stmt->execute([$video_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'likes' => $result['likes']]);
        exit;
    }

    if ($_POST['action'] === 'comment_like') {
        $comment_id = intval($_POST['comment_id']);
        $stmt = $pdo->prepare("UPDATE comments SET likes = COALESCE(likes,0)+1 WHERE id = ?");
        $stmt->execute([$comment_id]);
        $stmt = $pdo->prepare("SELECT likes FROM comments WHERE id = ?");
        $stmt->execute([$comment_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'likes' => $result['likes']]);
        exit;
    }

    if ($_POST['action'] === 'comment' && isset($_POST['video_id'], $_POST['comment'])) {
        $video_id = intval($_POST['video_id']);
        $comment = htmlspecialchars(trim($_POST['comment']));
        $stmt = $pdo->prepare("INSERT INTO comments (video_id, user_id, comment, created_at, likes) VALUES (?, ?, ?, NOW(), 0)");
        // Ici, user_id du commentateur : à ajuster selon ta logique de session
        $user_id = 1; // temporaire si pas de session
        $stmt->execute([$video_id, $user_id, $comment]);
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
    <title>UniTok - Découvre les vidéos étudiantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #000;
            color: #fff;
            font-family: "Poppins", sans-serif;
        }

        .video-card {
            position: relative;
            width: 400px;
            margin: 30px auto;
            border-radius: 20px;
            overflow: hidden;
            background: #111;
        }

        .video-card video {
            width: 400px;
            height: 700px;
            border-radius: 15px;
        }

        .video-info {
            position: absolute;
            bottom: 20px;
            left: 15px;
            z-index: 2;
            color: #fff;
            text-shadow: 1px 1px 5px #000;
        }

        .video-actions {
            position: absolute;
            right: 15px;
            bottom: 100px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .tiktok-btn {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            border: none;
            background: rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            cursor: pointer;
        }

        .tiktok-btn:hover {
            transform: scale(1.1);
        }

        .action-count {
            font-size: 13px;
            font-weight: 600;
            text-align: center;
        }

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

        .modal-content {
            background: #000;
            width: 100%;
            max-width: 500px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
        }

        .modal-header {
            padding: 15px;
            border-bottom: 1px solid #222;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
        }

        .close-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 28px;
            cursor: pointer;
        }

        .comments-list {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }

        .comment-item {
            display: flex;
            gap: 10px;
            padding: 10px;
            border-bottom: 1px solid #222;
        }

        .comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #333;
            display: flex;
            align-items: center;
            justify-content: center;
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
            color: #fff;
        }

        .comment-text {
            color: #fff;
        }

        .comment-like-btn {
            background: none;
            border: none;
            color: #888;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .comment-like-btn.liked {
            color: #ff4757;
        }

        .comment-form-container {
            padding: 10px;
            border-top: 1px solid #222;
        }

        .comment-input {
            flex: 1;
            padding: 10px;
            border-radius: 25px;
            background: #333;
            border: none;
            color: #fff;
            resize: none;
        }

        .submit-btn {
            padding: 10px 20px;
            border-radius: 25px;
            background: #00b894;
            color: #fff;
            border: none;
        }
    </style>
</head>

<body>

    <div class="text-center my-4">
        <h1 class="text-primary"><i class="bi bi-camera-reels-fill"></i> UniTok</h1>
        <p>Découvre et partage les vidéos étudiantes 🎓</p>
    </div>

    <?php foreach ($videos as $video): ?>
        <div class="video-card">
            <video controls>
                <source src="<?= htmlspecialchars($video['fichier']) ?>" type="video/mp4">
            </video>
            <div class="video-info">
                <h5>@<?= htmlspecialchars($video['pseudo']) ?></h5>
                <p><?= htmlspecialchars($video['titre']) ?></p>
                <?php if ($video['description']): ?>
                    <small><?= htmlspecialchars($video['description']) ?></small>
                <?php endif; ?>
            </div>
            <div class="video-actions">
                <div class="text-center">
                    <button class="tiktok-btn" onclick="likeVideo(<?= $video['id'] ?>, this)">❤️</button>
                    <div class="action-count" id="like-count-<?= $video['id'] ?>"><?= $video['likes'] ?? 0 ?></div>
                </div>
                <div class="text-center">
                    <button class="tiktok-btn" onclick="openCommentModal(<?= $video['id'] ?>)">💬</button>
                    <div class="action-count" id="comment-count-<?= $video['id'] ?>"><?= $video['comment_count'] ?? 0 ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Modal Commentaires -->
    <div id="commentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div>Commentaires</div>
                <button class="close-btn" onclick="closeCommentModal()">&times;</button>
            </div>
            <div class="comments-list" id="commentsList">
                <div class="text-center text-secondary py-4">Chargement...</div>
            </div>
            <div class="comment-form-container">
                <form id="commentForm" onsubmit="submitComment(event)">
                    <input type="hidden" name="action" value="comment">
                    <input type="hidden" name="video_id" id="commentVideoId">
                    <div class="d-flex gap-2">
                        <textarea name="comment" class="comment-input" placeholder="Écris un commentaire..." rows="1" required></textarea>
                        <button class="submit-btn">Publier</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        async function likeVideo(videoId, btn) {
            const form = new FormData();
            form.append('action', 'like');
            form.append('video_id', videoId);
            const resp = await fetch('', {
                method: 'POST',
                body: form
            });
            const res = await resp.json();
            if (res.success) {
                document.getElementById('like-count-' + videoId).textContent = res.likes;
                btn.classList.add('liked');
            }
        }

        function openCommentModal(videoId) {
            document.getElementById('commentVideoId').value = videoId;
            document.getElementById('commentModal').style.display = 'flex';
            loadComments(videoId);
        }

        function closeCommentModal() {
            document.getElementById('commentModal').style.display = 'none';
        }

        async function loadComments(videoId) {
            const res = await fetch(`get_comments.php?video_id=${videoId}`);
            const comments = await res.json();
            const list = document.getElementById('commentsList');
            list.innerHTML = '';
            if (comments.length === 0) {
                list.innerHTML = '<div class="text-center text-secondary py-4">Aucun commentaire</div>';
                return;
            }
            comments.forEach(c => {
                const div = document.createElement('div');
                div.className = 'comment-item';
                div.innerHTML = `<div class="comment-avatar">${c.profile_picture_url?'<img src="'+c.profile_picture_url+'">':'<span>'+c.pseudo[0].toUpperCase()+'</span>'}</div>
        <div class="comment-content"><div class="comment-author">@${c.pseudo}</div><div class="comment-text">${c.comment}</div>
        <div class="comment-like-btn" onclick="likeComment(${c.id}, this)">❤️ <span>${c.likes}</span></div></div>`;
                list.appendChild(div);
            });
        }

        async function likeComment(commentId, btn) {
            const form = new FormData();
            form.append('action', 'comment_like');
            form.append('comment_id', commentId);
            const resp = await fetch('', {
                method: 'POST',
                body: form
            });
            const res = await resp.json();
            if (res.success) {
                btn.querySelector('span').textContent = res.likes;
                btn.classList.add('liked');
            }
        }

        async function submitComment(e) {
            e.preventDefault();
            const form = document.getElementById('commentForm');
            const fd = new FormData(form);
            const resp = await fetch('', {
                method: 'POST',
                body: fd
            });
            if (resp.ok) {
                loadComments(document.getElementById('commentVideoId').value);
                form.querySelector('textarea').value = '';
                const countElem = document.getElementById('comment-count-' + document.getElementById('commentVideoId').value);
                countElem.textContent = parseInt(countElem.textContent) + 1;
            }
        }

        window.onclick = function(e) {
            if (e.target === document.getElementById('commentModal')) closeCommentModal();
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCommentModal();
            }
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>

</html>