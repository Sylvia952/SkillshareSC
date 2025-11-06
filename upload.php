<?php
include 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Gestion du formulaire d'upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);
    $fichier = $_FILES['video'];

    // Vérifier les champs
    if (!empty($titre) && isset($fichier['name']) && $fichier['error'] === 0) {
        $extensionsAutorisees = ['mp4', 'mov', 'avi', 'mkv'];
        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));

        if (in_array($extension, $extensionsAutorisees)) {
            // Créer le dossier s'il n'existe pas
            $dossier = "assets/videos/";
            if (!is_dir($dossier)) {
                mkdir($dossier, 0777, true);
            }

            // Nouveau nom unique
            $nouveauNom = uniqid("video_") . "." . $extension;
            $chemin = $dossier . $nouveauNom;

            // Déplacer le fichier
            if (move_uploaded_file($fichier['tmp_name'], $chemin)) {
                // Enregistrer en base de données
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
        } else {
            $error = "Format non supporté. Formats acceptés : mp4, mov, avi, mkv.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publier une vidéo</title>
</head>
<body>
    <?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h3 class="text-center fw-bold text-primary mb-4">📤 Publier une vidéo</h3>

        <?php if (isset($error)): ?>
          <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
          <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
          <div class="mb-3">
            <label for="titre" class="form-label">Titre de la vidéo</label>
            <input type="text" name="titre" id="titre" class="form-control" placeholder="Titre de votre vidéo" required>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Ajoutez une petite description..."></textarea>
          </div>

          <div class="mb-3">
            <label for="video" class="form-label">Choisir une vidéo</label>
            <input type="file" name="video" id="video" class="form-control" accept="video/*" required>
          </div>

          <button type="submit" class="btn btn-success w-100">Publier maintenant</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>