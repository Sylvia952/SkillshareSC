<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    if (!$titre) $error = "Le titre est obligatoire.";
    else {
        $stmt = $pdo->prepare("INSERT INTO lives (user_id, titre, active, date_debut) VALUES (:uid, :titre, 1, NOW())");
        $stmt->execute(['uid' => $_SESSION['user_id'], 'titre' => $titre]);
        $live_id = $pdo->lastInsertId();
        $success = "🎉 Live lancé !";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Lancer un Live - UniTok</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: radial-gradient(circle, #0d1117, #000);
            color: #fff;
            font-family: Poppins, Arial, sans-serif;
        }

        .card {
            background: #111;
            padding: 20px;
            border-radius: 15px;
            max-width: 600px;
            margin: 30px auto;
            box-shadow: 0 0 20px rgba(0, 255, 153, 0.2);
        }

        .form-control {
            background: #222;
            color: #fff;
            border: 1px solid #333;
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: #00ff99;
            box-shadow: 0 0 10px #00ff99;
        }

        .btn-live {
            display: block;
            margin: 20px auto;
            background: linear-gradient(90deg, #007bff, #00ff99);
            color: #fff;
            border: none;
            border-radius: 50px;
            padding: 12px 25px;
            font-weight: bold;
        }

        .btn-live:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 255, 153, 0.5);
        }

        video {
            border-radius: 10px;
            width: 100%;
            max-height: 400px;
        }

        .alert {
            border-radius: 10px;
        }

        #chatBox {
            background: #111;
            padding: 10px;
            height: 200px;
            overflow-y: auto;
            border-radius: 10px;
            margin-top: 15px;
        }

        .input-group {
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>
    <div class="card">
        <h3><i class="bi bi-camera-video"></i> Lancer un Live</h3>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
        <form method="POST">
            <label>Titre du Live</label>
            <input type="text" name="titre" class="form-control" placeholder="Titre du live" required>
            <button type="submit" class="btn-live"><i class="bi bi-play-circle"></i> Démarrer</button>
        </form>

        <hr>
        <h5>Aperçu webcam</h5>
        <video id="localVideo" autoplay muted playsinline></video>

        <!-- Chat live -->
        <h5>Chat en direct</h5>
        <div id="chatBox"></div>
        <div class="input-group">
            <input type="text" id="chatInput" class="form-control" placeholder="Tapez un message...">
            <button class="btn btn-success" id="sendBtn"><i class="bi bi-send"></i> Envoyer</button>
        </div>
    </div>

    <script>
        const localVideo = document.getElementById('localVideo');
        navigator.mediaDevices.getUserMedia({
                video: true,
                audio: true
            })
            .then(stream => {
                localVideo.srcObject = stream;
            })
            .catch(err => alert("Impossible d'accéder à la webcam: " + err));

        const chatBox = document.getElementById('chatBox');
        const chatInput = document.getElementById('chatInput');
        const sendBtn = document.getElementById('sendBtn');
        const liveId = <?= $live_id ?? 0 ?>;

        async function loadChat() {
            if (!liveId) return;
            const res = await fetch('chat_live.php?live_id=' + liveId);
            const data = await res.text();
            chatBox.innerHTML = data;
            chatBox.scrollTop = chatBox.scrollHeight;
        }
        loadChat();
        setInterval(loadChat, 2000);

        sendBtn.addEventListener('click', async () => {
            const msg = chatInput.value.trim();
            if (msg && liveId) {
                await fetch('chat_live.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `live_id=${liveId}&message=${encodeURIComponent(msg)}`
                });
                chatInput.value = '';
                loadChat();
            }
        });

        chatInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') sendBtn.click();
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>

</html>