<?php
include 'config.php';
include 'includes/header.php';

$search = trim($_GET['search'] ?? '');

// Requête selon recherche ou pas
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM education WHERE titre LIKE :kw OR contenu LIKE :kw ORDER BY date_publication DESC");
    $stmt->execute(['kw' => "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM education ORDER BY date_publication DESC");
}
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Éducation - UniTok</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
/* --- Isolation des styles pour le feed --- */
.ut-feed-body {
    background: radial-gradient(circle,#0d1117,#000);
    color:#fff;
    font-family:Poppins,Arial,sans-serif;
    margin:0;
    padding:0;
    overflow-x:hidden;
}
.ut-feed-container { max-width:600px; margin:auto; padding:20px 0; }
.ut-feed-search { margin-bottom:15px; }
.ut-feed-search input {
    background:#222; color:#fff; border:none; border-radius:50px;
    padding:10px 15px; width:calc(100% - 50px);
}
.ut-feed-search button {
    background:#00ff99; border:none; color:#000; border-radius:50px;
    padding:10px 15px; margin-left:5px;
}
.ut-feed-article {
    background:#111; border-radius:15px; overflow:hidden; margin-bottom:20px;
    box-shadow:0 0 20px rgba(0,255,153,0.2); transition:transform 0.3s;
    padding-bottom:10px;
}
.ut-feed-article:hover { transform:translateY(-5px); }
.ut-feed-article img {
    width:100%; max-height:400px; object-fit:cover;
    display:block;
}
.ut-feed-article-content { padding:15px; }
.ut-feed-article-content h5 { font-weight:bold; color:#00ff99; margin-bottom:5px; }
.ut-feed-article-content p { color:#ccc; font-size:0.9rem; margin:0; }
.ut-feed-readmore {
    display:block; text-align:right; color:#007bff; font-weight:bold;
    padding:0 15px 10px; text-decoration:none; cursor:pointer;
}
.ut-feed-readmore:hover { text-decoration:underline; }
.ut-feed-reactions { display:flex; gap:15px; padding:0 15px; margin-top:10px; font-size:0.9rem; }
.ut-feed-reactions span { cursor:pointer; transition:0.2s; }
.ut-feed-reactions span:hover { transform:scale(1.2); }
</style>
</head>
<body class="ut-feed-body">

<div class="ut-feed-container">
    <div class="text-center mb-3">
        <h1 class="fw-bold text-info"><i class="bi bi-journal-bookmark-fill"></i> Éducation</h1>
        <p class="text-secondary">Informez-vous et apprenez de manière interactive</p>
    </div>

    <!-- Barre de recherche -->
    <form class="ut-feed-search d-flex" method="GET">
        <input type="text" name="search" placeholder="Rechercher par mot-clé..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit"><i class="bi bi-search"></i></button>
    </form>

    <!-- Feed vertical -->
    <?php if(count($articles) > 0): ?>
        <?php foreach($articles as $article): ?>
        <div class="ut-feed-article" data-article-id="<?= $article['id'] ?>">
            <img src="<?= !empty($article['image']) ? $article['image'] : 'assets/img/education-default.jpg' ?>" alt="Image article">
            <div class="ut-feed-article-content">
                <h5><?= htmlspecialchars($article['titre']) ?></h5>
                <p class="ut-article-preview"><?= nl2br(substr(htmlspecialchars($article['contenu']), 0, 200)) ?><?= strlen($article['contenu']) > 200 ? "..." : "" ?></p>
                <p class="ut-article-full" style="display:none;"><?= nl2br(htmlspecialchars($article['contenu'])) ?></p>
            </div>
            <span class="ut-feed-readmore">Lire plus <i class="bi bi-arrow-down"></i></span>

            <!-- Reactions statiques -->
            <div class="ut-feed-reactions">
                <span>👍 5</span>
                <span>❤️ 15</span>
                <span>😮 0</span>
                <span>😂 0</span>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="alert alert-info text-center">Aucun article trouvé pour cette recherche 😴</div>
    <?php endif; ?>
</div>

<script>
// Toggle contenu complet
document.querySelectorAll('.ut-feed-readmore').forEach(btn => {
    btn.addEventListener('click', function(){
        const article = this.closest('.ut-feed-article');
        const preview = article.querySelector('.ut-article-preview');
        const full = article.querySelector('.ut-article-full');
        if(preview.style.display !== 'none'){
            preview.style.display = 'none';
            full.style.display = 'block';
            this.innerHTML = 'Réduire <i class="bi bi-arrow-up"></i>';
        } else {
            preview.style.display = 'block';
            full.style.display = 'none';
            this.innerHTML = 'Lire plus <i class="bi bi-arrow-down"></i>';
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
