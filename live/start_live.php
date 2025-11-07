<?php
include 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    
    if (empty($title)) {
        $error = "Le titre est obligatoire";
    } else {
        // Générer une clé de stream unique
        $stream_key = 'live_' . uniqid() . '_' . $user_id;
        
        try {
            $sql = "INSERT INTO lives (user_id, title, description, stream_key, is_live) 
                    VALUES (:user_id, :title, :description, :stream_key, TRUE)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'title' => $title,
                'description' => $description,
                'stream_key' => $stream_key
            ]);
            
            $live_id = $pdo->lastInsertId();
            $_SESSION['current_live_id'] = $live_id;
            $_SESSION['stream_key'] = $stream_key;
            
            header("Location: broadcast.php?stream_key=" . $stream_key);
            exit;
            
        } catch (PDOException $e) {
            $error = "Erreur lors du démarrage du live: " . $e->getMessage();
        }
    }
}

// Vérifier si l'utilisateur a déjà un live en cours
$stmt = $pdo->prepare("SELECT * FROM lives WHERE user_id = :user_id AND is_live = TRUE");
$stmt->execute(['user_id' => $user_id]);
$current_live = $stmt->fetch(PDO::FETCH_ASSOC);

if ($current_live) {
    header("Location: broadcast.php?stream_key=" . $current_live['stream_key']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Démarrer un Live - UniTok</title>
    <style>
        .live-preview {
            background: #000;
            border-radius: 10px;
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-bottom: 20px;
            position: relative;
        }
        #videoPreview {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }
        .preview-placeholder {
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg border-0">
                    <div class="card-header bg-danger text-white">
                        <h3 class="mb-0"><i class="fas fa-broadcast-tower"></i> Démarrer un Live</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <div class="live-preview">
                            <video id="videoPreview" autoplay muted style="display: none;"></video>
                            <div class="preview-placeholder" id="placeholder">
                                <i class="fas fa-video fa-3x mb-3"></i>
                                <p>Aperçu de la caméra</p>
                                <small>La caméra s'activera quand vous démarrerez le live</small>
                            </div>
                        </div>

                        <form method="POST" id="liveForm">
                            <div class="mb-3">
                                <label for="title" class="form-label">Titre du live *</label>
                                <input type="text" class="form-control" id="title" name="title" 
                                       placeholder="Donnez un titre à votre live" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="3" placeholder="Décrivez votre live..."></textarea>
                            </div>

                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Instructions :</h6>
                                <ul class="mb-0">
                                    <li>Assurez-vous d'autoriser l'accès à votre caméra</li>
                                    <li>Utilisez un fond propre et un éclairage correct</li>
                                    <li>Vérifiez votre connexion internet</li>
                                    <li>Le live sera accessible à tous les utilisateurs</li>
                                </ul>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger btn-lg" id="startButton">
                                    <i class="fas fa-broadcast-tower"></i> Démarrer le Live
                                </button>
                                <a href="lives.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Retour aux lives
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Prévisualisation de la caméra
        const videoPreview = document.getElementById('videoPreview');
        const placeholder = document.getElementById('placeholder');
        const startButton = document.getElementById('startButton');

        async function startCameraPreview() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { width: 1280, height: 720 },
                    audio: true 
                });
                
                videoPreview.srcObject = stream;
                videoPreview.style.display = 'block';
                placeholder.style.display = 'none';
                
            } catch (error) {
                console.error('Erreur caméra:', error);
                startButton.disabled = true;
                startButton.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Caméra non disponible';
                startButton.classList.add('btn-secondary');
            }
        }

        // Démarrer la prévisualisation au chargement
        startCameraPreview();

        // Empêcher la double soumission
        document.getElementById('liveForm').addEventListener('submit', function(e) {
            const button = this.querySelector('button[type="submit"]');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Démarrage...';
        });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>