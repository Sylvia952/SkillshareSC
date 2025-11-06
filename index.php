<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .feed-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 40px;
        }

        .video-card {
            position: relative;
            width: 400px;
            height: 700px;
            overflow: hidden;
            border-radius: 15px;
            background: #000;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
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
            margin: 0;
            font-size: 14px;
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
            background: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .video-actions button:hover {
            background-color: #0d6efd;
            color: white;
        }
    </style>
</head>

<body>
    <?php include 'config.php'; ?>
    <?php include 'includes/header.php'; ?>

    

    <div class="text-center mb-4">
        <h1 class="fw-bold text-primary">🎬 Bienvenue sur UniTok</h1>
        <p class="text-secondary">Découvre les vidéos des étudiants de SONOU</p>
    </div>

    <div class="feed-container">
        <!-- Vidéo 1 -->
        <div class="video-card">
            <video autoplay loop muted controls>
                <source src="assets/videos/exemple1.mp4" type="video/mp4">
            </video>
            <div class="video-info">
                <h5>@etudiant1</h5>
                <p>Présentation de l’université</p>
            </div>
            <div class="video-actions">
                <button title="J’aime ❤️">❤️</button>
                <button title="Commentaire 💬">💬</button>
                <button title="Partager 🔁">🔁</button>
            </div>
        </div>

        <!-- Vidéo 2 -->
        <div class="video-card">
            <video autoplay loop muted controls>
                <source src="assets/videos/exemple2.mp4" type="video/mp4">
            </video>
            <div class="video-info">
                <h5>@etudiante2</h5>
                <p>Vie sociale à SONOU</p>
            </div>
            <div class="video-actions">
                <button title="J’aime ❤️">❤️</button>
                <button title="Commentaire 💬">💬</button>
                <button title="Partager 🔁">🔁</button>
            </div>
        </div>

        <!-- Vidéo 3 -->
        <div class="video-card">
            <video autoplay loop muted controls>
                <source src="assets/videos/exemple3.mp4" type="video/mp4">
            </video>
            <div class="video-info">
                <h5>@tuteurSONOU</h5>
                <p>Comment gérer son temps ?</p>
            </div>
            <div class="video-actions">
                <button title="J’aime ❤️">❤️</button>
                <button title="Commentaire 💬">💬</button>
                <button title="Partager 🔁">🔁</button>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

</body>

</html>