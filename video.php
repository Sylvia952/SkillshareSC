<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$maxFileSize = 50 * 1024 * 1024;
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = isset($_POST['titre']) ? trim($_POST['titre']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $fichier = isset($_FILES['video']) ? $_FILES['video'] : null;

    if (!$titre || !$fichier) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($fichier['error'] !== 0) {
        $error = "Erreur d’upload (code " . $fichier['error'] . ")";
    } elseif ($fichier['size'] > $maxFileSize) {
        $error = "Le fichier dépasse la limite de 50 Mo.";
    } else {
        $ext = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));
        $ok = ['mp4','mov','avi','mkv'];

        if (!in_array($ext, $ok)) {
            $error = "Format non supporté (mp4, mov, avi, mkv uniquement).";
        } else {
            $folder = "assets/videos/";
            if (!is_dir($folder)) mkdir($folder, 0777, true);

            $name = uniqid("video_") . "." . $ext;
            $path = $folder . $name;

            if (move_uploaded_file($fichier['tmp_name'], $path)) {
                $stmt = $pdo->prepare("INSERT INTO videos (user_id, titre, description, fichier) VALUES (:uid, :titre, :desc, :file)");
                $stmt->execute([
                    'uid' => $_SESSION['user_id'],
                    'titre' => $titre,
                    'desc' => $description,
                    'file' => $path
                ]);
                $success = "🎉 Vidéo publiée avec succès !";
            } else {
                $error = "Erreur lors du transfert.";
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
    /* 🌑 Fond général sombre avec effet TikTok */
    body {
      background: radial-gradient(circle at top, #0d1117, #000);
      color: #fff;
      font-family: "Poppins", Arial, sans-serif;
      margin: 0;
      padding: 0;
      overflow-x: hidden;
      animation: fadeIn 1.2s ease;
    }

    /* ✨ Animation d'apparition */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* 💡 Carte moderne avec halo */
    .card {
         width: 500px;
         margin-left: 55px;
      height: 700px;
      color: #fff;
      border: 1px solid rgba(255,255,255,0.05);
      border-radius: 16px;
      box-shadow: 0 0 20px rgba(0,255,153,0.1);
      transition: transform 0.3s, box-shadow 0.3s;
      animation: zoomIn 0.8s ease;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 0 25px rgba(0,255,153,0.2);
    }

    @keyframes zoomIn {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }

    /* 📝 Champs de texte */
    .form-control, textarea {
      background-color: #222;
      color: #fff;
      border: 1px solid #333;
      border-radius: 8px;
      transition: all 0.3s;
    }

    .form-control:focus {
      border-color: #00ff99;
      box-shadow: 0 0 10px #00ff99;
    }

    .form-control::placeholder {
      color: #999;
    }

    /* 🎯 Bouton principal */
    .btn-success {
      background: linear-gradient(90deg, #007bff, #00ff99);
      border: none;
      font-weight: 600;
      border-radius: 10px;
      transition: all 0.3s ease;
    }

    .btn-success:hover {
      background: linear-gradient(90deg, #00ff99, #007bff);
      transform: scale(1.05);
      box-shadow: 0 0 15px rgba(0,255,153,0.4);
    }

    /* 📊 Barre de progression animée */
    .progress {
      background-color: #222;
      height: 25px;
      border-radius: 50px;
      overflow: hidden;
    }

    .progress-bar {
      background: linear-gradient(90deg, #007bff, #00ff99);
      font-weight: bold;
      animation: progressGlow 1.5s infinite alternate;
    }

    @keyframes progressGlow {
      from { box-shadow: 0 0 5px #00ff99; }
      to { box-shadow: 0 0 20px #00ff99; }
    }

    video {
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,255,153,0.2);
    }

    /* 💬 Alertes plus stylées */
    .alert {
      border: none;
      border-radius: 10px;
      font-weight: 500;
    }
  </style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container my-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-lg">
        <div class="card-body">
          <h3 class="text-center fw-bold text-info mb-4">
            <i class="bi bi-upload"></i> Publier une vidéo
          </h3>

          <?php if ($error): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= $error ?></div>
          <?php endif; ?>

          <?php if ($success): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= $success ?></div>
          <?php endif; ?>

          <form id="uploadForm" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label for="titre" class="form-label"><i class="bi bi-pencil-square"></i> Titre</label>
              <input type="text" name="titre" id="titre" class="form-control" placeholder="Titre de votre vidéo" required>
            </div>

            <div class="mb-3">
              <label for="description" class="form-label"><i class="bi bi-text-paragraph"></i> Description</label>
              <textarea name="description" id="description" class="form-control" rows="3" placeholder="Ajoutez une petite description..."></textarea>
            </div>

            <div class="mb-3">
              <label for="video" class="form-label"><i class="bi bi-camera-video"></i> Choisir une vidéo (Max 50 Mo)</label>
              <input type="file" name="video" id="video" class="form-control" accept="video/*" required>
            </div>

            <div class="mb-3 d-none" id="previewContainer">
              <label class="form-label"><i class="bi bi-eye"></i> Aperçu :</label>
              <video id="videoPreview" controls class="w-100" style="max-height: 300px;"></video>
            </div>

            <div class="mb-3">
              <div class="progress">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%">0%</div>
              </div>
            </div>

            <button type="submit" class="btn btn-success w-100">
              <i class="bi bi-cloud-upload"></i> Publier maintenant
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const videoInput = document.getElementById('video');
const videoPreview = document.getElementById('videoPreview');
const previewContainer = document.getElementById('previewContainer');
const progressBar = document.getElementById('progressBar');

videoInput.addEventListener('change', function(){
    const file = this.files[0];
    if(file){
        const url = URL.createObjectURL(file);
        videoPreview.src = url;
        previewContainer.classList.remove('d-none');
    }
});

document.getElementById('uploadForm').addEventListener('submit', function(e){
    const file = videoInput.files[0];
    if(file && file.size > 50 * 1024 * 1024){
        e.preventDefault();
        alert("Le fichier dépasse 50 Mo. Compressez-le ou choisissez un fichier plus petit.");
        return;
    }

    let percent = 0;
    progressBar.style.width = '0%';
    progressBar.textContent = '0%';
    const interval = setInterval(()=>{
        percent += 10;
        progressBar.style.width = percent + '%';
        progressBar.textContent = percent + '%';
        if(percent >= 100) clearInterval(interval);
    }, 150);
});
</script>

<?php include 'includes/footer.php'; ?>

</body>
</html>
