<?php
// Démarrage sécurisé de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UniTok - Plateforme étudiante</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand fw-bold text-light" href="index.php">🎓 UniTok</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a href="index.php" class="nav-link text-light">Accueil</a></li>
        <li class="nav-item"><a href="education.php" class="nav-link text-light">Éducation</a></li>
        <li class="nav-item"><a href="lives.php" class="nav-link text-light">Lives</a></li>

        <?php if(isset($_SESSION['user_id'])): ?>
            <li class="nav-item"><a href="upload.php" class="nav-link text-light">Publier</a></li>
            <li class="nav-item"><a href="profile.php" class="nav-link text-light">Profil</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link text-light">Déconnexion</a></li>
        <?php else: ?>
            <li class="nav-item"><a href="login.php" class="nav-link text-light">Connexion</a></li>
            <li class="nav-item"><a href="register.php" class="nav-link text-light">Inscription</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container my-4">
