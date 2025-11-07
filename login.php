<?php
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mdp = trim($_POST['mdp']);

    if (!empty($email) && !empty($mdp)) {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($mdp, $user['mdp'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['last_name']; 
            header("Location: index.php");
            exit;
        } else {
            $error = "Email ou mot de passe incorrect.";
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
  <title>Login</title>
</head>
<body>
  <?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h3 class="text-center text-primary fw-bold mb-4">Connexion à Skillshare 🎓</h3>

        <?php if (isset($error)): ?>
          <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
          <div class="mb-3">
            <label for="email" class="form-label">Adresse e-mail</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="ex: etudiant@sonou.edu" required>
          </div>

          <div class="mb-3">
            <label for="mdp" class="form-label">Mot de passe</label>
            <input type="password" name="mdp" id="mdp" class="form-control" placeholder="********" required>
          </div>

          <button type="submit" class="btn btn-primary w-100">Se connecter</button>

          <p class="text-center mt-3 mb-0">
            Pas encore de compte ? <a href="register.php" class="text-success fw-bold">Inscrivez-vous</a>
          </p>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>