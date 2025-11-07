<?php
//session_start();
include 'config.php';
include 'includes/header.php';
// --- Fonction pour générer un matricule automatiquement ---
function generate_matricule() {
    return 'LCS-' . random_int(10000000, 99999999);
}

// --- LOGIQUE D'UPLOAD DE PROFIL ---
function handle_profile_picture_upload() {
    $upload_dir = 'uploads/profiles/';
    $default_photo = 'assets/videos/img/default.png';

    // 1️⃣ Vérifier que le dossier existe, sinon le créer
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // 2️⃣ Vérifier si un fichier a été soumis
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['profile_picture']['tmp_name'];
        $file_name = $_FILES['profile_picture']['name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_type = $_FILES['profile_picture']['type'];

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024;

        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_file_name = uniqid('profile_') . '.' . $extension;
            $target_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_name, $target_path)) {
                return $target_path; // ✅ fichier uploadé avec succès
            }
        }
    }

    // 3️⃣ Sinon, retourner la photo par défaut
    return $default_photo;
}


// --- TRAITEMENT DU FORMULAIRE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['last_name']);
    $prenom = trim($_POST['first_name']);
    $filiere = trim($_POST['filiere']);
    $email = trim($_POST['email']);
    $mdp = trim($_POST['mdp']);
    $confirm = trim($_POST['confirm']);
    $surnom = trim($_POST['username']);

    $matricule = generate_matricule();
    $photo_url = handle_profile_picture_upload();

    if (!empty($nom) && !empty($prenom) && !empty($filiere) && !empty($email) && !empty($mdp) && !empty($confirm) && !empty($surnom)) {
        if ($mdp === $confirm) {
            $check = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $check->execute(['email' => $email]);
            if ($check->rowCount() > 0) {
                $error = "Cet e-mail est déjà utilisé.";
            } else {
                $hashed = password_hash($mdp, PASSWORD_DEFAULT);
                $insert = $pdo->prepare("INSERT INTO users (matricule, last_name, first_name, filiere, email, mdp, username, profile_picture_url) 
                                        VALUES (:matricule, :last_name, :first_name, :filiere, :email, :mdp, :username, :profile_picture_url)");
                $insert->execute([
                    'matricule' => $matricule, 
                    'last_name' => $nom, 
                    'first_name' => $prenom, 
                    'filiere' => $filiere, 
                    'email' => $email, 
                    'mdp' => $hashed, 
                    'username' => $surnom, 
                    'profile_picture_url' => $photo_url,
                ]);
                $success = "Compte créé avec succès ! Votre matricule : $matricule";
            }
        } else {
            $error = "Les mots de passe ne correspondent pas.";
        }
    } 
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inscription UniTok</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    background: radial-gradient(circle at top, #0d1117, #000);
    color: #fff;
    font-family: "Poppins", Arial, sans-serif;
}
h1, h5, p { margin: 0; }
.text-primary { color: #00b894 !important; }
.text-secondary { color: #aaa; }

.register-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 60px 0;
}
.register-card {
    background-color: #1c1c1e;
    padding: 30px;
    border-radius: 20px;
    width: 100%;
    max-width: 450px;
    box-shadow: 0 6px 25px rgba(0, 0, 0, 0.6);
}
.register-card h3 {
    font-size: 1.8rem;
    text-align: center;
    margin-bottom: 20px;
}
.form-control {
    background-color: #2c2c2e;
    color: #fff;
    border: none;
    border-radius: 10px;
}
.form-control:focus {
    box-shadow: none;
    border-color: #00b894;
}
.btn-tiktok {
    background: blue;
    color: #fff;
    font-weight: bold;
    border-radius: 50px;
    width: 100%;
    padding: 10px;
    font-size: 1rem;
    transition: 0.3s ease;
}
.btn-tiktok:hover {
    transform: scale(1.05);
}
.alert { border-radius: 10px; }
.user-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #00b894;
    display: block;
    margin: 0 auto 15px;
}
</style>
</head>
<body>

<div class="register-container">
    <div class="register-card">
        <img src="assets/videos/img/default.png" alt="Avatar par défaut" class="user-avatar">
        <h3 class="text-primary">Créer un compte UniTok 🎓</h3>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger mt-3"><?= $error ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="alert alert-success mt-3"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="mt-3">
            <div class="mb-3">
                <input type="text" name="last_name" class="form-control" placeholder="Nom" required>
            </div>
            <div class="mb-3">
                <input type="text" name="first_name" class="form-control" placeholder="Prénoms" required>
            </div>
            <div class="mb-3">
                <input type="text" name="filiere" class="form-control" placeholder="Filière" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="E-mail universitaire" required>
            </div>
            <div class="mb-3">
                <input type="password" name="mdp" class="form-control" placeholder="Mot de passe" required>
            </div>
            <div class="mb-3">
                <input type="password" name="confirm" class="form-control" placeholder="Confirmer le mot de passe" required>
            </div>
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Nom d'utilisateur" required>
            </div>
            <div class="mb-3">
                <label for="profile_picture_url" class="form-label">Photo de Profil (optionnelle)</label>
                <input type="file" id="profile_picture_url" name="profile_picture_url" accept="image/*" class="form-control">
            </div>
            <button type="submit" class="btn btn-tiktok mt-3">S'inscrire</button>
        </form>

        <p class="text-center mt-3 mb-0 text-secondary">
            Déjà un compte ? <a href="login.php" class="text-primary fw-bold">Connectez-vous</a>
        </p>
    </div>
</div>

</body>
</html>
