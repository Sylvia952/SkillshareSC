<?php

session_start();
include 'config.php';

// --- LOGIQUE D'UPLOAD DE FICHIER SÉCURISÉE ---
function handle_profile_picture_upload() {
    // 1. Définir le répertoire de stockage (à créer dans C:\xampp\htdocs\SkillshareSC\uploads\profiles\)
    $upload_dir = 'uploads/profiles/'; 
    // Chemin par défaut en cas d'échec ou d'absence de photo
    $default_photo = 'uploads/default.png'; 

    // Vérifier si le fichier a bien été soumis et qu'il n'y a pas d'erreur d'upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        
        $file_tmp_name = $_FILES['profile_picture']['tmp_name'];
        $file_name = $_FILES['profile_picture']['name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_type = $_FILES['profile_picture']['type'];
        
        // Sécurité: Définir les types et la taille max
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5 Mo

        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            
            // Sécurité: Générer un nom unique pour le fichier
            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_file_name = uniqid('profile_') . '.' . $extension;
            $target_path = $upload_dir . $new_file_name;

            // Déplacer le fichier de son emplacement temporaire
            if (move_uploaded_file($file_tmp_name, $target_path)) {
                return $target_path; // Retourne le chemin d'accès pour la DB
            }
        }
        // Si l'upload échoue (taille/type non valide ou erreur de déplacement)
        return $default_photo; 
    }
    
    // Si aucun fichier n'a été soumis
    return $default_photo; 
}
// ---------------------------------------------


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matricule = trim($_POST['matricule']);
    $nom = trim($_POST['last_name']);
    $prenom = trim($_POST['first_name']);
    $filiere = trim($_POST['filiere']);
    $email = trim($_POST['email']);
    $mdp = trim($_POST['mdp']);
    $confirm = trim($_POST['confirm']);
    $surnom = trim($_POST['username']);
    // $photo = trim($_POST['profile_picture']); <-- SUPPRIMÉ / REMPLACÉ PAR L'UPLOAD
  

    // LOGIQUE DE GESTION DE LA PHOTO DE PROFIL
    // Si une photo est soumise, elle sera gérée ici et nous aurons le chemin.
    $photo_url = handle_profile_picture_upload();
    // ----------------------------------------


    // La vérification !empty() doit être ajustée car la photo est optionnelle (ou gérée par la fonction)
    // Nous avons enlevé $photo de la liste des !empty() car la fonction retourne une URL par défaut
    if (!empty($matricule) && !empty($nom) && !empty($prenom) && !empty($filiere) && !empty($email) && !empty($mdp) && !empty($confirm) && !empty($surnom)) {
        if ($mdp === $confirm) {
            $check = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $check->execute(['email' => $email]);
            if ($check->rowCount() > 0) {
                $error = "Cet e-mail est déjà utilisé.";
            } else {
                $hashed = password_hash($mdp, PASSWORD_DEFAULT);
                
                $insert = $pdo->prepare("INSERT INTO users (matricule, last_name, first_name, filiere, email, mdp, username, profile_picture) 
                                        VALUES (:matricule, :last_name, :first_name, :filiere, :email, :mdp, :username, :profile_picture)");
                
                $insert->execute([
                    'matricule' => $matricule, 
                    'last_name' => $nom, 
                    'first_name' => $prenom, 
                    'filiere' => $filiere, 
                    'email' => $email, 
                    'mdp' => $hashed, 
                    'username' => $surnom, 
                    'profile_picture' => $photo_url, // <-- UTILISATION DU CHEMIN OBTENU PAR L'UPLOAD
                ]);
                
                $success = "Compte créé avec succès. Vous pouvez maintenant vous connecter.";
            }
        } else {
            $error = "Les mots de passe ne correspondent pas.";
        }
    } 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Régister</title>
</head>
<body>
  <?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h3 class="text-center text-success fw-bold mb-4">Créer un compte Skillshare 🎓</h3>

        <?php if (isset($error)): ?>
          <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
          <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
         <div class="mb-3">
            <label for="matricule" class="form-label">Numéro matricule</label>
            <input type="number" name="matricule" id="matricule" class="form-control" placeholder="00000000000" required>
          </div>

          <div class="mb-3">
            <label for="last_name" class="form-label">Nom</label>
            <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Votre nom complet" required>
          </div>

         

          <div class="mb-3">
            <label for="first_name" class="form-label">Prénoms</label>
            <input type="text" name="first_name" id="first_name" class="form-control" placeholder="Vos prénoms" required>
          </div>

<div class="mb-3">
            <label for="filiere" class="form-label">Filière</label>
            <input type="text" name="filiere" id="filiere" class="form-control" placeholder="SIL" required>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Adresse e-mail universitaire</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="ex: etudiant@sonou.edu" required>
          </div>

          <div class="mb-3">
            <label for="mdp" class="form-label">Mot de passe</label>
            <input type="password" name="mdp" id="mdp" class="form-control" placeholder="********" required>
          </div>

          <div class="mb-3">
            <label for="confirm" class="form-label">Confirmer le mot de passe</label>
            <input type="password" name="confirm" id="confirm" class="form-control" placeholder="********" required>
          </div>

         <div class="mb-3">
            <label for="username" class="form-label">Nom d'utilisateur</label>
            <input type="text" name="username" id="username" class="form-control" placeholder="Sylv123" required>
          </div>

          <div class="mb-3">
           <label for="profile_picture">Photo de Profil :</label>
           <input type="file" id="profile_picture" name="profile_picture" accept="image/*" required>
          </div>

          <button type="submit" class="btn btn-success w-100">S'inscrire</button>

          <p class="text-center mt-3 mb-0">
            Déjà un compte ? <a href="login.php" class="text-primary fw-bold">Connectez-vous</a>
          </p>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>