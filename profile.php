<?php
include 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Récupérer les infos de l'utilisateur
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer les vidéos de cet utilisateur
$stmt = $pdo->prepare("SELECT * FROM videos WHERE user_id = :id ORDER BY date_publication DESC");
$stmt->execute(['id' => $user_id]);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile</title>
</head>
<body>
  <?php include 'includes/header.php'; ?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center">
          <img src="<?= !empty($user['photo']) ? $user['photo'] : 'assets/img/default-avatar.png' ?>" 
               alt="Photo de profil" class="rounded-circle mb-3" width="120" height="120">
          <h3 class="fw-bold text-primary"><?= htmlspecialchars($user['last_name']) ?></h3>
          <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
          <a href="logout.php" class="btn btn-danger btn-sm">Se déconnecter</a>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <h4 class="fw-bold text-success mb-3">📽️ Mes vidéos publiées</h4>

          <?php if (count($videos) > 0): ?>
            <div class="row">
              <?php foreach ($videos as $video): ?>
                <div class="col-md-4 mb-4">
                  <div class="card h-100 border-0 shadow-sm">
                    <video class="card-img-top rounded" controls>
                      <source src="<?= htmlspecialchars($video['fichier']) ?>" type="video/mp4">
                      Votre navigateur ne supporte pas la lecture de vidéos.
                    </video>
                    <div class="card-body">
                      <h6 class="fw-bold text-primary"><?= htmlspecialchars($video['titre']) ?></h6>
                      <p class="small text-muted mb-1"><?= htmlspecialchars($video['description']) ?></p>
                      <p class="text-muted small"><i class="bi bi-clock"></i> <?= $video['date_publication'] ?></p>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <div class="alert alert-info text-center">Vous n'avez encore publié aucune vidéo.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>