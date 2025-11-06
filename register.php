<?php
include 'config.php';

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $mdp = trim($_POST['mdp']);
    $confirm = trim($_POST['confirm']);

    if (!empty($nom) && !empty($email) && !empty($mdp) && !empty($confirm)) {
        if ($mdp === $confirm) {
            // Vérifier si l'email existe déjà
            $check = $pdo->prepare("SELECT * FROM users WHERE email = :email");
            $check->execute(['email' => $email]);
            if ($check->rowCount() > 0) {
                $error = "Cet e-mail est déjà utilisé.";
            } else {
                // Insérer l’utilisateur
                $hashed = password_hash($mdp, PASSWORD_DEFAULT);
                $insert = $pdo->prepare("INSERT INTO users (last_name, email, mdp) VALUES (:last_name, :email, :mdp)");
                $insert->execute(['last_name' => $nom, 'email' => $email, 'mdp' => $hashed]);
                $success = "Compte créé avec succès. Vous pouvez maintenant vous connecter.";
            }
        } else {
            $error = "Les mots de passe ne correspondent pas.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h3 class="text-center text-success fw-bold mb-4">Créer un compte UniTok 🎓</h3>

        <?php if (isset($error)): ?>
          <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
          <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="mb-3">
            <label for="last_name" class="form-label">Nom complet</label>
            <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Votre nom complet" required>
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
