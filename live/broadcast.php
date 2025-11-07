<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['stream_key'])) {
    header("Location: login.php");
    exit;
}

$stream_key = $_GET['stream_key'];
$user_id = $_SESSION['user_id'];

// Vérifier que l'utilisateur possède ce live
$stmt = $pdo->prepare("SELECT l.*, u.last_name, u.profile_picture 
                      FROM lives l 
                      JOIN users u ON l.user_id = u.id 
                      WHERE l.stream_key = :stream_key AND l.user_id = :user_id");
$stmt->execute(['stream_key' => $stream_key, 'user_id' => $user_id]);
$live = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$live) {
    header("Location: start_live.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broadcast Live - <?= htmlspecialchars($live['title']) ?></title>
    <style>
        body {
            background: #000;
            color: white;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .broadcast-container {
            max-width: 100%;
            height: 100vh;
            position: relative;
            background: #000;
        }
        #videoPreview {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .broadcast-controls {
            position: absolute;
            bottom: 30px;
            left: 0;
            right: 0;
            text-align: center;
            z-index: 100;
        }
        .stats {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(0,0,0,0.8);
            padding: 15px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
            z-index: 100;
        }
        .viewer-count {
            font-size: 1.2em;
            font-weight: bold;
            color: #ff4444;
        }
        .live-time {
            font-size: 1.1em;
        }
        .btn-broadcast {
            padding: 12px 30px;
            font-size: 1.1em;
            border-radius: 25px;
        }
        .quality-indicator {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(0,0,0,0.8);
            padding: 10px 15px;
            border-radius: 10px;
            z-index: 100;
        }
    </style>
</head>
<body>
    <div class="broadcast-container">
        <video id="videoPreview" autoplay muted></video>
        
        <div class="stats">
            <h4><?= htmlspecialchars($live['title']) ?></h4>
            <div class="viewer-count">
                <i class="fas fa-users"></i> <span id="viewerCount"><?= $live['viewers_count'] ?></span> spectateurs
            </div>
            <div class="live-time">
                <i class="fas fa-clock"></i> <span id="liveTime">00:00:00</span>
            </div>
            <?php if($live['description']): ?>
                <div class="mt-2">
                    <small><?= htmlspecialchars($live['description']) ?></small>
                </div>
            <?php endif; ?>
        </div>

        <div class="quality-indicator">
            <span id="qualityStatus">
                <i class="fas fa-circle text-success"></i> Qualité bonne
            </span>
        </div>

        <div class="broadcast-controls">
            <button id="endLive" class="btn btn-danger btn-broadcast">
                <i class="fas fa-stop"></i> Terminer le Live
            </button>
            <div class="mt-3">
                <small class="text-muted">Partagez ce lien : 
                    <span id="shareLink"><?= "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/watch_live.php?stream_key=' . $stream_key ?></span>
                    <button onclick="copyShareLink()" class="btn btn-sm btn-outline-light ms-2">
                        <i class="fas fa-copy"></i>
                    </button>
                </small>
            </div>
        </div>
    </div>

    <script>
        let stream;
        let startTime;
        let viewerUpdateInterval;
        let timeUpdateInterval;

        // Démarrer la caméra et le streaming
        async function startBroadcast() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { 
                        width: { ideal: 1280 },
                        height: { ideal: 720 },
                        frameRate: { ideal: 30 }
                    },
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        autoGainControl: true
                    }
                });
                
                const videoPreview = document.getElementById('videoPreview');
                videoPreview.srcObject = stream;
                
                startTime = new Date();
                startViewerUpdates();
                startTimeUpdates();
                
                // Simulation de qualité réseau
                simulateNetworkQuality();
                
            } catch (error) {
                console.error('Erreur de broadcast:', error);
                alert('Erreur lors du démarrage du broadcast: ' + error.message);
                window.location.href = 'start_live.php';
            }
        }

        function startTimeUpdates() {
            timeUpdateInterval = setInterval(updateLiveTime, 1000);
        }

        function updateLiveTime() {
            const now = new Date();
            const diff = Math.floor((now - startTime) / 1000);
            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;
            
            document.getElementById('liveTime').textContent = 
                `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        }

        function startViewerUpdates() {
            // Mettre à jour les viewers toutes les 5 secondes
            updateViewerCount();
            viewerUpdateInterval = setInterval(updateViewerCount, 5000);
        }

        function updateViewerCount() {
            // Compter les viewers réels (dans un vrai système, ça viendrait d'une base de données)
            const baseViewers = <?= $live['viewers_count'] ?>;
            const randomChange = Math.floor(Math.random() * 3) - 1; // -1, 0, ou 1
            const newViewers = Math.max(1, baseViewers + randomChange);
            
            document.getElementById('viewerCount').textContent = newViewers;
            
            // Mettre à jour en base de données
            fetch('update_live_viewers.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'stream_key=<?= $stream_key ?>&viewers_count=' + newViewers
            });
        }

        function simulateNetworkQuality() {
            setInterval(() => {
                const qualities = [
                    { text: 'Excellente', class: 'text-success', icon: 'fa-signal' },
                    { text: 'Bonne', class: 'text-success', icon: 'fa-signal' },
                    { text: 'Moyenne', class: 'text-warning', icon: 'fa-signal' },
                    { text: 'Faible', class: 'text-danger', icon: 'fa-signal' }
                ];
                const randomQuality = qualities[Math.floor(Math.random() * 2)]; // Toujours bonne ou excellente
                
                const qualityElement = document.getElementById('qualityStatus');
                qualityElement.innerHTML = 
                    `<i class="fas ${randomQuality.icon} ${randomQuality.class}"></i> Qualité ${randomQuality.text}`;
            }, 10000);
        }

        function copyShareLink() {
            const shareLink = document.getElementById('shareLink').textContent;
            navigator.clipboard.writeText(shareLink).then(() => {
                alert('Lien copié dans le presse-papier !');
            });
        }

        // Terminer le live
        document.getElementById('endLive').addEventListener('click', function() {
            if (confirm('Voulez-vous vraiment terminer le live ?')) {
                // Arrêter tous les flux
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                }
                
                // Arrêter les intervalles
                clearInterval(viewerUpdateInterval);
                clearInterval(timeUpdateInterval);
                
                // Marquer le live comme terminé
                fetch('end_live.php', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'stream_key=<?= $stream_key ?>'
                }).then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          window.location.href = 'profile.php';
                      }
                  });
            }
        });

        // Démarrer le broadcast au chargement
        startBroadcast();

        // Empêcher la fermeture accidentelle
        window.addEventListener('beforeunload', function(e) {
            if (stream) {
                e.preventDefault();
                e.returnValue = 'Votre live est en cours. Voulez-vous vraiment quitter ?';
                return e.returnValue;
            }
        });
    </script>
</body>
</html>