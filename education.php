<?php
include 'config.php';
include 'includes/header.php';

// Récupération des articles depuis la base de données
$stmt = $pdo->query("SELECT * FROM education ORDER BY date_publication DESC");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-5">
  <div class="text-center mb-5">
    <h1 class="fw-bold text-primary">🧠 Éducation sexuelle & sociale</h1>
    <p class="text-secondary">Informez-vous, apprenez et partagez des connaissances pour un campus plus conscient et responsable.</p>
  </div>

  <div class="row">
    <?php if (count($articles) > 0): ?>
      <?php foreach ($articles as $article): ?>
        <div class="col-md-4 mb-4">
          <div class="card border-0 shadow-sm h-100">
            <img src="<?= !empty($article['image']) ? $article['image'] : 'assets/img/education-default.jpg' ?>" 
                 class="card-img-top" alt="Image de l'article">
            <div class="card-body">
              <h5 class="card-title text-success"><?= htmlspecialchars($article['titre']) ?></h5>
              <p class="card-text text-muted small"><?= substr(htmlspecialchars($article['contenu']), 0, 120) ?>...</p>
            </div>
            <div class="card-footer bg-white border-0">
              <a href="education-detail.php?id=<?= $article['id'] ?>" class="btn btn-outline-primary btn-sm">Lire plus</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="alert alert-info text-center">Aucun article disponible pour le moment.</div>
    <?php endif; ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
