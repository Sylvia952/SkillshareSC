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

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    /* --- STYLE NAVBAR --- */
    .navbar {
      background: linear-gradient(90deg, #007bff, #00b894);
      box-shadow: 0 3px 10px rgba(0,0,0,0.15);
    }
    .navbar-brand {
      font-size: 1.5rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .navbar-brand i {
      font-size: 1.6rem;
    }
    .nav-link {
      color: #f8f9fa !important;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s;
    }
    .nav-link:hover {
      color: #ffeaa7 !important;
      transform: translateY(-2px);
    }
    .dropdown-menu {
      border-radius: 12px;
      border: none;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    .dropdown-item {
      display: flex;
      align-items: center;
      gap: 8px;
      transition: 0.2s;
    }
    .dropdown-item:hover {
      background: #f1f1f1;
      transform: translateX(3px);
    }
    .profile-btn {
      color: #fff !important;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .profile-btn img {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      object-fit: cover;
    }
  </style>
</head>

<body>

<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand text-light" href="index.php">
      <i class="bi bi-mortarboard-fill"></i> UniTok
    </a>
    <button class="navbar-toggler text-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <i class="bi bi-list fs-2"></i>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a href="index.php" class="nav-link"><i class="bi bi-house-door-fill"></i> Accueil</a></li>
        <li class="nav-item"><a href="education.php" class="nav-link"><i class="bi bi-journal-bookmark-fill"></i> Éducation</a></li>

        <?php if(isset($_SESSION['user_id'])): ?>
          <li class="nav-item"><a href="upload.php" class="nav-link"><i class="bi bi-cloud-arrow-up-fill"></i> Publier</a></li>
          <li class="nav-item"><a href="lives.php" class="nav-link"><i class="bi bi-broadcast-pin"></i> Lives</a></li>

          <!-- Dropdown Profil -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle profile-btn" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <img src="assets\videos\img\default.png" alt="profil">
              <span><?php echo $_SESSION['user_name'] ?? 'Moi'; ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person-circle"></i> Mon profil</a></li>
              <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear-fill"></i> Paramètres</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a href="login.php" class="nav-link"><i class="bi bi-box-arrow-in-right"></i> Connexion</a></li>
          <li class="nav-item"><a href="register.php" class="nav-link"><i class="bi bi-person-plus-fill"></i> Inscription</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<main class="container my-4">
