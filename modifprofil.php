<?php
//session_start(); 
// Inclure la configuration de la base de données
include 'config.php';

// --- VÉRIFICATION DE LA CONNEXION ET SÉCURITÉ ---
// NOTE : Il est CRUCIAL de vérifier la session !
// Si vous n'utilisez pas de système de session, ce code DOIT être adapté.

// Si l'utilisateur n'est pas connecté, le rediriger vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// L'ID à modifier est celui de l'utilisateur connecté pour des raisons de sécurité.
$id_utilisateur = $_SESSION['user_id'];

// Initialiser les variables d'erreur et de succès
$error = '';
$success = '';
// -------------------------------------------------


// --- 1. TRAITEMENT DU FORMULAIRE (MÉTHODE POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupération des données POST (on utilise ?? '' pour éviter les notices si le champ est manquant)
    $nom = trim($_POST['last_name'] ?? '');
    $prenom = trim($_POST['first_name'] ?? '');
    $filiere = trim($_POST['filiere'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $surnom = trim($_POST['username'] ?? '');

    // Sécurité: Validation de base des champs
    if (empty($nom) || empty($prenom) || empty($email) || empty($surnom) || empty($filiere)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Le format de l'e-mail est invalide.";
    } else {
        // Sécurité: Vérifier si le nouvel e-mail n'est pas déjà utilisé par un autre utilisateur
        $check_email = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
        $check_email->execute(['email' => $email, 'id' => $id_utilisateur]);

        if ($check_email->rowCount() > 0) {
            $error = "Cet e-mail est déjà utilisé par un autre compte.";
        } else {
            // Mise à jour de la base de données
            $update_query = "UPDATE users SET last_name = :last_name, first_name = :first_name, filiere = :filiere, email = :email, username = :username WHERE id = :id";
            
            $stmt = $pdo->prepare($update_query);
            
            if ($stmt->execute([
                'last_name' => $nom, 
                'first_name' => $prenom, 
                'filiere' => $filiere, 
                'email' => $email, 
                'username' => $surnom, 
                'id' => $id_utilisateur
            ])) {
                $success = "Votre profil a été mis à jour avec succès !";
                // Mettre à jour les variables de session si nécessaire (ex: le nom d'utilisateur)
                $_SESSION['username'] = $surnom; 
            } else {
                $error = "Erreur lors de la mise à jour du profil.";
            }
        }
    }
}


// --- 2. RÉCUPÉRATION DES DONNÉES ACTUELLES POUR LE FORMULAIRE ---

// On sélectionne toutes les infos du profil pour les pré-remplir
$select_profile = $pdo->prepare("SELECT last_name, first_name, filiere, email, username, matricule FROM users WHERE id = :id");
$select_profile->execute(['id' => $id_utilisateur]);
$profile_data = $select_profile->fetch(PDO::FETCH_ASSOC);

// Si aucune donnée n'est trouvée (très improbable si la session est bonne)
if (!$profile_data) {
    // Rediriger ou afficher une erreur critique
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Profil</title>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h3 class="text-center text-primary fw-bold mb-4">Mettre à Jour Mon Profil 📝</h3>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        
                        <div class="mb-3">
                            <label for="matricule" class="form-label">Numéro Matricule (non modifiable)</label>
                            <input type="text" id="matricule" class="form-control" value="<?= htmlspecialchars($profile_data['matricule']) ?>" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Nom</label>
                            <input type="text" name="last_name" id="last_name" class="form-control" value="<?= htmlspecialchars($profile_data['last_name']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="first_name" class="form-label">Prénoms</label>
                            <input type="text" name="first_name" id="first_name" class="form-control" value="<?= htmlspecialchars($profile_data['first_name']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="filiere" class="form-label">Filière</label>
                            <input type="text" name="filiere" id="filiere" class="form-control" value="<?= htmlspecialchars($profile_data['filiere']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($profile_data['username']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse e-mail universitaire</label>
                            <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($profile_data['email']) ?>" required>
                        </div>
                        
                        <p class="text-muted mt-4">
                            Laissez les champs de mot de passe vides pour conserver votre mot de passe actuel.
                        </p>
                        
                        <a href="modifier_motdepasse.php" class="btn btn-secondary w-100 mb-3">Modifier le mot de passe</a>
                        
                        <button type="submit" class="btn btn-primary w-100">Enregistrer les modifications</button>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

</body>
</html>