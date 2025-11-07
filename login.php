<?php
include 'config.php';
include 'includes/header.php';

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
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion UniTok</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    background: radial-gradient(circle at top, #0d1117, #000);
    color: #fff;
    font-family: "Poppins", Arial, sans-serif;
    height: 100vh;
}

.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    padding: 20px;
}

.login-card {
    background-color: #1c1c1e;
    padding: 30px;
    border-radius: 20px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 6px 25px rgba(0, 0, 0, 0.6);
}

.login-card h3 {
    font-size: 1.8rem;
    text-align: center;
    margin-bottom: 20px;
    color: #00b894;
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

.alert { border-radius: 10px; text-align: center; }

.text-center a { text-decoration: none; }

</style>
</head>
<body>

<div class="row justify-content-center">
  <div class="col-md-5">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h3 class="text-center text-primary fw-bold mb-4">Connexion à Skillshare 🎓</h3>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger mt-3"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="mt-3">
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Adresse e-mail universitaire" required>
            </div>
            <div class="mb-3">
                <input type="password" name="mdp" class="form-control" placeholder="Mot de passe" required>
            </div>
            <button type="submit" class="btn btn-tiktok mt-3">Se connecter</button>
        </form>

        <p class="text-center mt-3 text-secondary">
            Pas encore de compte ? <a href="register.php" class="text-primary fw-bold">Inscrivez-vous</a>
        </p>
    </div>
</div>

</body>
</html>
