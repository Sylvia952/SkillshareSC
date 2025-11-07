<?php
include 'config.php';
include 'includes/header.php';

// Récupérer les lives actifs avec les informations utilisateur
$stmt = $pdo->query("
    SELECT l.*, u.last_name, u.first_name, u.profile_picture, u.username
    FROM lives l 
    JOIN users u ON l.user_id = u.id 
    WHERE l.is_live = TRUE 
    ORDER BY l.viewers_count DESC, l.created_at DESC
");
$active_lives = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les lives récents (terminés)
$stmt = $pdo->query("
    SELECT l.*, u.last_name, u.first_name, u.profile_picture, u.username
    FROM lives l 
    JOIN users u ON l.user_id = u.id 
    WHERE l.is_live = FALSE 
    ORDER BY l.created_at DESC 
    LIMIT 12
");
$recent_lives = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lives - UniTok</title>
    <style>
        .live-card {
            transition: all 0.3s ease;
            border: 3px solid #dc3545;
            border-radius: 15px;
            overflow: hidden;
        }
        .live-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(220, 53, 69, 0.3);
        }
        .live-badge {
            position: absolute;
            top: 12px;
            left: 12px;
            background: linear-gradient(45deg, #dc3545, #ff6b7a);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            z-index: 10;
        }
        .viewer-badge {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.8em;
        }
        .ended-card {
            opacity: 0.85;
            border-color: #6c757d;
        }
        .ended-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
        }
        .profile-img {
            width: 40px;
            height: 40px;
            object-fit: cover;
        }
        .card-img-container {
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        .card-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .live-card:hover .card-img-container img {
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="fw-bold text-danger mb-3">
                <i class="fas fa-broadcast-tower"></i> Lives en Direct
            </h1>
            <p class="text-secondary fs-5">Rejoignez les lives en cours et interagissez en temps réel avec la communauté</p>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="start_live.php" class="btn btn-danger btn-lg px-4 py-2">
                    <i class="fas fa-video me-2"></i> Démarrer un Live
                </a>
            <?php else: ?>
                <div class="alert alert-warning d-inline-block">
                    <a href="login.php" class="alert-link">Connectez-vous</a> pour démarrer votre propre live !
                </div>
            <?php endif; ?>
        </div>

        <!-- Lives actifs -->
        <div class="row mb-5">
            <div class="col-12">
                <h3 class="mb-4 text-success">
                    <i class="fas fa-circle me-2"></i> En Direct Maintenant
                    <span class="badge bg-danger ms-2"><?= count($active_lives) ?> live(s)</span>
                </h3>
                
                <?php if (count($active_lives) > 0): ?>
                    <div class="row">
                        <?php foreach ($active_lives as $live): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="card live-card shadow-sm h-100">
                                    <div class="card-img-container position-relative">
                                        <img src="<?= !empty($live['profile_picture']) ? htmlspecialchars($live['profile_picture']) : 'assets/img/default-avatar.png' ?>" 
                                             alt="Live de <?= htmlspecialchars($live['last_name']) ?>">
                                        <div class="live-badge">
                                            <i class="fas fa-circle me-1"></i> EN DIRECT
                                        </div>
                                        <div class="viewer-badge">
                                            <i class="fas fa-users me-1"></i> <?= $live['viewers_count'] ?>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-3">
                                            <img src="<?= !empty($live['profile_picture']) ? htmlspecialchars($live['profile_picture']) : 'assets/img/default-avatar.png' ?>" 
                                                 alt="Profile" class="profile-img rounded-circle me-3">
                                            <div>
                                                <h6 class="mb-0 fw-bold">@<?= htmlspecialchars($live['last_name']) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($live['first_name']) ?></small>
                                            </div>
                                        </div>
                                        <h5 class="card-title"><?= htmlspecialchars($live['title']) ?></h5>
                                        <?php if($live['description']): ?>
                                            <p class="card-text text-muted"><?= htmlspecialchars(substr($live['description'], 0, 120)) ?><?= strlen($live['description']) > 120 ? '...' : '' ?></p>
                                        <?php endif; ?>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-1"></i>
                                                Début: <?= date('H:i', strtotime($live['created_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-0 pt-0">
                                        <a href="watch_live.php?stream_key=<?= $live['stream_key'] ?>" 
                                           class="btn btn-danger w-100 py-2">
                                            <i class="fas fa-play me-2"></i> Regarder le Live
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center py-4">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <h4>Aucun live en cours pour le moment</h4>
                        <p class="mb-0">
                            <?php if(isset($_SESSION['user_id'])): ?>
                                Soyez le premier à <a href="start_live.php" class="alert-link">démarrer un live</a> !
                            <?php else: ?>
                                <a href="login.php" class="alert-link">Connectez-vous</a> pour être le premier à lancer un live.
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Lives récents (terminés) -->
        <?php if (count($recent_lives) > 0): ?>
        <div class="row">
            <div class="col-12">
                <h3 class="mb-4 text-secondary">
                    <i class="fas fa-history me-2"></i> Lives Récents
                    <span class="badge bg-secondary ms-2"><?= count($recent_lives) ?></span>
                </h3>
                <div class="row">
                    <?php foreach ($recent_lives as $live): ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                            <div class="card ended-card h-100">
                                <div class="card-img-container">
                                    <img src="<?= !empty($live['profile_picture']) ? htmlspecialchars($live['profile_picture']) : 'assets/img/default-avatar.png' ?>" 
                                         alt="Live de <?= htmlspecialchars($live['last_name']) ?>">
                                    <div class="viewer-badge bg-secondary">
                                        <i class="fas fa-users me-1"></i> <?= $live['viewers_count'] ?>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h6 class="card-title"><?= htmlspecialchars($live['title']) ?></h6>
                                    <div class="d-flex align-items-center mb-2">
                                        <img src="<?= !empty($live['profile_picture']) ? htmlspecialchars($live['profile_picture']) : 'assets/img/default-avatar.png' ?>" 
                                             alt="Profile" class="profile-img rounded-circle me-2">
                                        <small class="text-muted">@<?= htmlspecialchars($live['last_name']) ?></small>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted small">
                                        <span>
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d/m/Y', strtotime($live['created_at'])) ?>
                                        </span>
                                        <span>
                                            <i class="fas fa-clock me-1"></i>
                                            <?= date('H:i', strtotime($live['created_at'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>