<?php
session_start(); // <-- DÉCOMMENTÉ : ESSENTIEL POUR UTILISER $_SESSION
include 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Récupérer l'ID du profil visité (via GET ou sinon c'est le profil connecté)
$profile_id = isset($_GET['profile_id']) ? intval($_GET['profile_id']) : $_SESSION['user_id'];

// Récupérer les infos de l'utilisateur visité
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $profile_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Si l'utilisateur n'existe pas, on redirige
if (!$user) {
    // Gérer l'erreur utilisateur non trouvé si nécessaire
    header("Location: index.php"); 
    exit;
}

// Récupérer les vidéos de l'utilisateur visité
$stmt = $pdo->prepare("SELECT * FROM videos WHERE user_id = :id ORDER BY date_publication DESC");
$stmt->execute(['id' => $profile_id]);
$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compter les abonnés
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM subscriptions WHERE subscribed_to_id = :id");
$stmt->execute(['id' => $profile_id]);
$subscribers_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Définir une variable de confort pour savoir si c'est VOTRE profil
$is_my_profile = ($profile_id === $_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        #subscribe-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: 0.3s;
        }
        #subscribe-btn.unsub {
            background-color: #dc3545;
        }
        #subscribe-btn:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center">
                    <img src="<?= !empty($user['photo']) ? htmlspecialchars($user['photo']) : 'assets/img/default-avatar.png' ?>" 
                         alt="Photo de profil" class="rounded-circle mb-3" width="120" height="120">
                    <h3 class="fw-bold text-primary"><?= htmlspecialchars($user['last_name']) ?></h3>
                    <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>

                    
        <?php if ($profile_id === $_SESSION['user_id']): ?>
            <a href="modifier_profil.php?id=<?= $profile_id ?>" class="btn btn-warning mb-2"> Modifier mon Profil</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Se déconnecter</a>
        <?php else: ?>
                 <p id="subscribers-count"><strong><?= $subscribers_count ?></strong>S'abonnés</p>
            <button id="subscribe-btn" 
            data-subscriber="<?= $_SESSION['user_id'] ?>" 
            data-target="<?= $profile_id ?>">Chargement...</button>
        <?php endif; ?>

                </div> 
              </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h4 class="fw-bold text-success mb-3">📽️ Vidéos publiées</h4>

                    <?php if (count($videos) > 0): ?>
                        <div class="row">
                            <?php foreach ($videos as $video): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <video class="card-img-top rounded" controls>
                                            <source src="<?= htmlspecialchars($video['fichier']) ?>" type="video/mp4">
                                            Votre navigateur ne supporte pas la lecture de vidéos.
                                        </video>
                                        <div class="card-body">
                                            <h6 class="fw-bold text-primary"><?= htmlspecialchars($video['titre']) ?></h6>
                                            <p class="small text-muted mb-1"><?= htmlspecialchars($video['description']) ?></p>
                                            <p class="text-muted small"><i class="bi bi-clock"></i> <?= $video['date_publication'] ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">Aucune vidéo publiée.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<?php if (!$is_my_profile): ?> <script>
const btn = document.getElementById('subscribe-btn');
const subscriberId = btn.getAttribute('data-subscriber');
const targetId = btn.getAttribute('data-target');
const countElem = document.getElementById('subscribers-count');

// Vérifier l'état au chargement
fetch('subscription.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `action=check&subscriber_id=${subscriberId}&target_id=${targetId}`
})
.then(res => res.text())
.then(response => {
    if(response.trim() === 'subscribed'){
        btn.textContent = 'Se désabonner';
        btn.classList.add('unsub');
    } else {
        btn.textContent = 'S’abonner';
    }
});

// Clic sur le bouton
btn.addEventListener('click', () => {
    const action = btn.textContent.trim() === 'S’abonner' ? 'subscribe' : 'unsubscribe';
    fetch('subscription.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=${action}&subscriber_id=${subscriberId}&target_id=${targetId}`
    })
    .then(res => res.text())
    .then(response => {
        if(response.includes('ok')){
            // Logique de mise à jour du texte et du compteur...
            const currentCount = parseInt(countElem.textContent.match(/\d+/)[0]);
            if(action === 'subscribe'){
                btn.textContent = 'Se désabonner';
                btn.classList.add('unsub');
                countElem.innerHTML = `<strong>${currentCount + 1}</strong> abonnés`;
            } else {
                btn.textContent = 'S’abonner';
                btn.classList.remove('unsub');
                countElem.innerHTML = `<strong>${currentCount - 1}</strong> abonnés`;
            }
        } else {
            alert('Erreur : ' + response);
        }
    });
});
</script>
<?php endif; ?>

</body>
</html>