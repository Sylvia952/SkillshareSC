<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Limites max côté PHP
$maxFileSize = 50 * 1024 * 1024; // 50 Mo

$success = '';
$error = '';

// Si formulaire soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titre = isset($_POST['titre']) ? trim($_POST['titre']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $fichier = isset($_FILES['video']) ? $_FILES['video'] : null;

    if (!$titre || !$fichier) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($fichier['error'] !== 0) {
        switch ($fichier['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error = "Le fichier est trop volumineux. Max 50 Mo.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $error = "Le fichier n'a été que partiellement téléchargé.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $error = "Aucun fichier téléchargé.";
                break;
            default:
                $error = "Erreur lors de l'upload du fichier.";
        }
    } elseif ($fichier['size'] > $maxFileSize) {
        $error = "Le fichier dépasse la limite de 50 Mo.";
    } else {
        $extensionsAutorisees = ['mp4','mov','avi','mkv'];
        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionsAutorisees)) {
            $error = "Format non supporté. Formats acceptés : mp4, mov, avi, mkv.";
        } else {
            $dossier = "assets/videos/";
            if (!is_dir($dossier)) mkdir($dossier, 0777, true);

            $nouveauNom = uniqid("video_") . "." . $extension;
            $chemin = $dossier . $nouveauNom;

            if (move_uploaded_file($fichier['tmp_name'], $chemin)) {
                $sql = "INSERT INTO videos (user_id, titre, description, fichier) VALUES (:user_id, :titre, :description, :fichier)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'user_id' => $_SESSION['user_id'],
                    'titre' => $titre,
                    'description' => $description,
                    'fichier' => $chemin
                ]);

                $success = "Votre vidéo a été publiée avec succès 🎉";
            } else {
                $error = "Erreur lors du transfert de la vidéo.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vidéos</title>
</head>
<body>
  
<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h3 class="text-center fw-bold text-primary mb-4">📤 Publier une vidéo</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form id="uploadForm" method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="titre" class="form-label">Titre de la vidéo</label>
            <input type="text" name="titre" id="titre" class="form-control" placeholder="Titre de votre vidéo" required>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Ajoutez une petite description..."></textarea>
          </div>

          <div class="mb-3">
            <label for="video" class="form-label">Choisir une vidéo (Max 50 Mo)</label>
            <input type="file" name="video" id="video" class="form-control" accept="video/*" required>
          </div>

          <!-- Aperçu vidéo -->
          <div class="mb-3 d-none" id="previewContainer">
            <label class="form-label">Aperçu :</label>
            <video id="videoPreview" controls class="w-100" style="max-height: 300px;"></video>
          </div>

          <!-- Barre de progression -->
          <div class="mb-3">
            <div class="progress" style="height: 25px;">
              <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width:0%">0%</div>
            </div>
          </div>

          <button type="submit" class="btn btn-success w-100">Publier maintenant</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
const videoInput = document.getElementById('video');
const videoPreview = document.getElementById('videoPreview');
const previewContainer = document.getElementById('previewContainer');

videoInput.addEventListener('change', function(){
    const file = this.files[0];
    if(file){
        const url = URL.createObjectURL(file);
        videoPreview.src = url;
        previewContainer.classList.remove('d-none');
    }
});

// Progression visuelle
const form = document.getElementById('uploadForm');
form.addEventListener('submit', function(e){
    const file = videoInput.files[0];
    if(file && file.size > 50 * 1024 * 1024){
        e.preventDefault();
        alert("Le fichier dépasse 50 Mo. Compressez-le ou choisissez un fichier plus petit.");
        return;
    }

    const progressBar = document.getElementById('progressBar');
    progressBar.style.width = '0%';
    progressBar.textContent = '0%';

    // Création d'un timer factice pour la barre de progression (vu que sans Ajax on ne peut pas suivre upload réel)
    let percent = 0;
    const interval = setInterval(()=>{
        percent += 10;
        if(percent > 100) percent = 100;
        progressBar.style.width = percent + '%';
        progressBar.textContent = percent + '%';
        if(percent === 100) clearInterval(interval);
    }, 100);
});
</script>

<?php include 'includes/footer.php'; ?>

</body>
</html>