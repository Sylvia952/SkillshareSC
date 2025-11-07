<?php
include 'config.php';
include 'includes/header.php';

// Récupérer toutes les vidéos avec le pseudo utilisateur
$stmt = $pdo->query("
    SELECT v.*, u.last_name AS pseudo
    FROM videos v 
    JOIN users u ON v.user_id = u.id 
    ORDER BY RAND()
");

$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UniTok - Découvre les vidéos étudiantes</title>

  <!-- Bootstrap & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    /* --- Style global --- */
    body {
      background: radial-gradient(circle at top, #0d1117, #000);
      color: #fff;
      font-family: "Poppins", Arial, sans-serif;
      margin: 0;
      padding: 0;
      overflow-x: hidden;
    }

    h1, h5, p {
      margin: 0;
    }

    .text-primary {
      color: #00b894 !important;
    }

    .text-secondary {
      color: #aaa;
    }

    /* --- Section d'en-tête --- */
    .intro {
      text-align: center;
      margin-top: 40px;
      margin-bottom: 30px;
    }

    .intro h1 {
      font-size: 2rem;
      font-weight: 700;
      letter-spacing: 1px;
    }

    .intro p {
      font-size: 1rem;
      color: #ccc;
    }

    /* --- Feed TikTok --- */
    .feed-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 60px;
      padding-bottom: 60px;
    }

    .video-card {
      position: relative;
      width: 400px;
      height: 700px;
      overflow: hidden;
      border-radius: 20px;
      background: #000;
      box-shadow: 0 6px 25px rgba(0, 0, 0, 0.6);
      transition: all 0.3s ease;
    }

    .video-card:hover {
      transform: scale(1.02);
      box-shadow: 0 8px 30px rgba(0, 255, 150, 0.25);
    }

    .video-card video {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 20px;
    }

    /* --- Infos vidéo --- */
    .video-info {
      position: absolute;
      bottom: 25px;
      left: 15px;
      color: white;
      text-shadow: 1px 1px 5px rgba(0,0,0,0.8);
      width: calc(100% - 80px);
    }

    .video-info h5 {
      font-size: 18px;
      font-weight: 600;
    }

    .video-info p {
      font-size: 14px;
      margin-top: 3px;
    }

    .video-info small {
      font-size: 12px;
      color: #ccc;
    }

    /* --- Avatar utilisateur --- */
    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #00b894;
      margin-right: 8px;
    }

    .user-info {
      display: flex;
      align-items: center;
      margin-bottom: 8px;
    }

    /* --- Boutons d'action --- */
    .video-actions {
      position: absolute;
      right: 15px;
      bottom: 110px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 18px;
    }

    .action-btn {
      background: rgba(255, 255, 255, 0.15);
      border: none;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      color: #fff;
      font-size: 22px;
      transition: 0.3s ease;
    }

    .action-btn:hover {
      background: #00b894;
      transform: scale(1.1);
    }

    .likes-count, .comments-count {
      font-size: 13px;
      margin-top: 5px;
      color: #ccc;
    }

    @media screen and (max-width: 600px) {
      .video-card {
        width: 90%;
        height: 600px;
      }
    }
  </style>
</head>

<body>

<div class="intro">
  <h1 class="fw-bold text-primary"><i class="bi bi-camera-reels-fill"></i> Bienvenue sur UniTok</h1>
  <p class="text-secondary">Découvre et partage les vidéos inspirantes des étudiants de SONOU 🎓</p>
</div>

<div class="feed-container">
  <?php foreach ($videos as $video): ?>
    <div class="video-card">
      <video autoplay muted loop controls>
        <source src="<?= htmlspecialchars($video['fichier']) ?>" type="video/mp4">
        Votre navigateur ne supporte pas la lecture vidéo.
      </video>

      <div class="video-info">
        <div class="user-info">
         <img src="assets\videos\img\default.png" alt="Avatar" class="user-avatar">

          <h5>@<?= htmlspecialchars($video['pseudo']) ?></h5>
        </div>
        <p><?= htmlspecialchars($video['titre']) ?></p>
        <?php if($video['description']): ?>
          <small><?= htmlspecialchars($video['description']) ?></small>
        <?php endif; ?>
      </div>

      <div class="video-actions">
        <button class="action-btn" title="J’aime"><i class="bi bi-heart-fill text-danger"></i></button>
        <span class="likes-count">123</span>

        <button class="action-btn" title="Commenter"><i class="bi bi-chat-dots-fill"></i></button>
        <span class="comments-count">45</span>

        <button class="action-btn" title="Partager"><i class="bi bi-share-fill"></i></button>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>
