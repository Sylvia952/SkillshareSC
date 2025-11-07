<?php
include 'config.php';
include 'includes/header.php';

// Récupérer toutes les vidéos avec le pseudo utilisateur
$stmt = $pdo->query("
    SELECT v.*, u.last_name AS pseudo
    FROM videos v 
    JOIN users u ON v.user_id = u.id 
    ORDER BY RAND()
");

$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniTok</title>
    <style>
        body {
            background: #111;
            color: #fff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .feed-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 40px;
            padding-bottom: 40px;
        }

        .video-card {
            position: relative;
            width: 400px;
            height: 700px;
            overflow: hidden;
            border-radius: 15px;
            background: #000;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
        }

        .video-card video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-info {
            position: absolute;
            bottom: 20px;
            left: 15px;
            color: white;
        }

        .video-info h5 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .video-info p {
            margin: 2px 0;
            font-size: 14px;
        }

        .video-info small {
            font-size: 12px;
            color: #ccc;
        }

        .video-actions {
            position: absolute;
            right: 10px;
            bottom: 100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .video-actions button {
            background: rgb(89, 89, 243);
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .video-actions button:hover {
            background-color: #0d6efd;
            color: white;
        }

        .text-center {
            text-align: center;
            margin-top: 20px;
        }

        .text-primary {
            color: #0d6efd;
        }

        .text-secondary {
            color: #aaa;
        }
    </style>
</head>
<body>

<div class="text-center mb-4">
    <h1 class="fw-bold text-primary">🎬 Bienvenue sur Skillshare</h1>
    <p class="text-secondary">Découvre l'univers LES COURS SONOU</p>
</div>

<div class="feed-container">
    <?php foreach ($videos as $video): ?>
        <div class="video-card">
            <video autoplay controls>
                <source src="<?= htmlspecialchars($video['fichier']) ?>" type="video/mp4">
                Votre navigateur ne supporte pas la lecture vidéo.
            </video>
            <div class="video-info">
                <h5>@<?= htmlspecialchars($video['pseudo']) ?></h5>
                <p><?= htmlspecialchars($video['titre']) ?></p>
                <?php if($video['description']): ?>
                    <small><?= htmlspecialchars($video['description']) ?></small>
                <?php endif; ?>
            </div>
            <div class="video-actions">
                <button title="J’aime ❤️">❤️</button>
                <button title="Commentaire 💬">💬</button>
                <button title="Partager 🔁">🔁</button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>
