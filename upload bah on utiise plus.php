<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$maxFileSize = 50 * 1024 * 1024; // 50 Mo
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $titre = trim($_POST['titre'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $fichier = $_FILES['video'] ?? null;

  if (!$titre || !$fichier) {
    $error = "Tous les champs sont obligatoires.";
  } elseif ($fichier['error'] !== 0) {
    $error = "Erreur d'upload : code " . $fichier['error'];
  } elseif ($fichier['size'] > $maxFileSize) {
    $error = "Le fichier dépasse la limite de 50 Mo.";
  } else {
    $extensions = ['mp4','mov','avi','mkv'];
    $ext = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $extensions)) {
      $error = "Format non supporté. Formats acceptés : mp4, mov, avi, mkv.";
    } else {
      $dossier = "assets/videos/";
      if (!is_dir($dossier)) mkdir($dossier, 0777, true);

      $nom = uniqid("video_") . "." . $ext;
      $chemin = $dossier . $nom;

      if (move_uploaded_file($fichier['tmp_name'], $chemin)) {
        $sql = "INSERT INTO videos (user_id, titre, description, fichier) VALUES (:user_id, :titre, :description, :fichier)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
          'user_id' => $_SESSION['user_id'],
          'titre' => $titre,
          'description' => $description,
          'fichier' => $chemin
        ]);
        $success = "🎉 Votre vidéo a été publiée avec succès !";
      } else {
        $error = "Erreur lors du transfert de la vidéo.";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Publier une vidéo - UniTok</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(135deg, #007bff10, #28a74510);
      min-height: 100vh;
    }
    .upload-card {
      background: #fff;
      border-radius: 1rem;
      padding: 2rem;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      transition: all 0.3s;
    }
    .upload-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 35px rgba(0,0,0,0.12);
    }
    .progress {
      height: 25px;
      border-radius: 50px;
      overflow: hidden;
    }
    #videoPreview {
      border-radius: 10px;
      margin-top: 10px;
    }
  </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
      <div class="upload-card">
        <h3 class="text-center fw-bold mb-4 text-primary">
          <i class="bi bi-cloud-arrow-up-fill me-2"></i>Publier une vidéo
        </h3>

        <?php if ($error): ?>
          <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?= $success ?></div>
        <?php endif; ?>

        <form id="uploadForm" method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="titre" class="form-label fw-semibold">Titre de la vidéo</label>
            <input type="text" name="titre" id="titre" class="form-control" placeholder="Ex : Présentation du campus UniTok" required>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label fw-semibold">Description</label>
            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Ajoutez une description sympa..."></textarea>
          </div>

          <div class="mb-3">
            <label for="video" class="form-label fw-semibold">Vidéo à uploader</label>
            <input type="file" name="video" id="video" class="form-control" accept="video/*" required>
            <div class="form-text">Max 50 Mo – Formats : mp4, mov, avi, mkv</div>
          </div>

          <!-- Aperçu vidéo -->
          <div class="mb-3 d-none" id="previewContainer">
            <label class="form-label fw-semibold">Aperçu :</label>
            <video id="videoPreview" controls class="w-100" style="max-height: 300px;"></video>
          </div>

          <!-- Barre de progression -->
          <div class="mb-3">
            <div class="progress">
              <div id="progressBar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" style="width:0%">0%</div>
            </div>
          </div>

          <button type="submit" class="btn btn-success w-100 fw-bold">
            <i class="bi bi-upload me-1"></i> Publier maintenant
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
const videoInput = document.getElementById('video');
const videoPreview = document.getElementById('videoPreview');
const previewContainer = document.getElementById('previewContainer');
const progressBar = document.getElementById('progressBar');

videoInput.addEventListener('change', function() {
  const file = this.files[0];
  if (file) {
    const url = URL.createObjectURL(file);
    videoPreview.src = url;
    previewContainer.classList.remove('d-none');
  }
});

document.getElementById('uploadForm').addEventListener('submit', function(e) {
  const file = videoInput.files[0];
  if (file && file.size > 50 * 1024 * 1024) {
    e.preventDefault();
    alert("⚠️ Le fichier dépasse 50 Mo. Compressez-le avant de publier.");
    return;
  }

  let percent = 0;
  progressBar.style.width = '0%';
  progressBar.textContent = '0%';
  const interval = setInterval(() => {
    percent += 10;
    progressBar.style.width = percent + '%';
    progressBar.textContent = percent + '%';
    if (percent >= 100) clearInterval(interval);
  }, 100);
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
