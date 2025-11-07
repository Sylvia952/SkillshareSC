<?php
include 'config.php';
include 'includes/header.php';

$article_id = intval($_GET['id'] ?? 0);

if(!$article_id){
    die("Article introuvable.");
}

// Récupération de l'article
$stmt = $pdo->prepare("SELECT * FROM education WHERE id = ?");
$stmt->execute([$article_id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$article){
    die("Article introuvable.");
}

// Récupération des commentaires
$stmt = $pdo->prepare("SELECT c.*, u.last_name AS pseudo FROM education_comments c JOIN users u ON c.user_id = u.id WHERE c.article_id = ? ORDER BY c.date_post DESC");
$stmt->execute([$article_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($article['titre']) ?> - UniTok</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
.ut-feed-body {
    background: radial-gradient(circle,#0d1117,#000);
    color:#fff;
    font-family:Poppins,Arial,sans-serif;
    margin:0;
    padding:0;
}
.ut-detail-container { max-width:600px; margin:auto; padding:20px 0; }
.ut-detail-article { background:#111; border-radius:15px; overflow:hidden; padding:15px; box-shadow:0 0 20px rgba(0,255,153,0.2); }
.ut-detail-article img { width:100%; max-height:400px; object-fit:cover; border-radius:10px; margin-bottom:10px; }
.ut-detail-title { font-weight:bold; color:#00ff99; font-size:1.5rem; margin-bottom:10px; }
.ut-detail-date { font-size:0.85rem; color:#aaa; margin-bottom:15px; }
.ut-detail-content { font-size:0.95rem; color:#ccc; line-height:1.5; white-space:pre-wrap; }
.ut-comments { margin-top:25px; }
.ut-comment { background:#111; border-radius:10px; padding:10px; margin-bottom:10px; box-shadow:0 0 10px rgba(0,255,153,0.2); }
.ut-comment .pseudo { font-weight:bold; color:#00ff99; margin-bottom:5px; display:block; }
.ut-comment input, .ut-comment button { border-radius:50px; }
.ut-back { display:block; margin-bottom:15px; color:#007bff; text-decoration:none; font-weight:bold; }
.ut-back:hover { text-decoration:underline; }
</style>
</head>
<body class="ut-feed-body">

<div class="ut-detail-container">
    <a href="education.php" class="ut-back"><i class="bi bi-arrow-left"></i> Retour aux articles</a>
    <div class="ut-detail-article">
        <img src="<?= !empty($article['image']) ? $article['image'] : 'assets/img/education-default.jpg' ?>" alt="Image article">
        <div class="ut-detail-title"><?= htmlspecialchars($article['titre']) ?></div>
        <div class="ut-detail-date">Publié le <?= date("d/m/Y H:i", strtotime($article['date_publication'])) ?></div>
        <div class="ut-detail-content"><?= nl2br(htmlspecialchars($article['contenu'])) ?></div>
    </div>

    <!-- Section commentaires -->
    <div class="ut-comments">
        <h5 class="mt-3">Commentaires</h5>
        <?php if(isset($_SESSION['user_id'])): ?>
        <div class="input-group mb-3 ut-comment">
            <input type="text" class="form-control" id="commentInput" placeholder="Écrire un commentaire...">
            <button class="btn btn-success" id="sendComment"><i class="bi bi-send"></i> Envoyer</button>
        </div>
        <?php else: ?>
        <p class="text-secondary">Connectez-vous pour commenter.</p>
        <?php endif; ?>

        <div id="commentList">
            <?php foreach($comments as $c): ?>
            <div class="ut-comment">
                <span class="pseudo">@<?= htmlspecialchars($c['pseudo']) ?></span>
                <span><?= nl2br(htmlspecialchars($c['message'])) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
<?php if(isset($_SESSION['user_id'])): ?>
const commentInput = document.getElementById('commentInput');
const sendComment = document.getElementById('sendComment');
const commentList = document.getElementById('commentList');
const articleId = <?= $article_id ?>;

async function loadComments(){
    const res = await fetch('education_comment.php?article_id='+articleId);
    const data = await res.text();
    commentList.innerHTML = data;
}
loadComments();
setInterval(loadComments, 3000);

sendComment.addEventListener('click', async()=>{
    const msg = commentInput.value.trim();
    if(!msg) return;
    await fetch('education_comment.php', {
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`article_id=${articleId}&message=${encodeURIComponent(msg)}`
    });
    commentInput.value='';
    loadComments();
});
commentInput.addEventListener('keypress', function(e){
    if(e.key==='Enter') sendComment.click();
});
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
