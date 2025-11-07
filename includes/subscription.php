<?php
require 'config.php'; // fichier de connexion à ta base de données

// Vérifie que les données sont envoyées
if (!isset($_POST['action'], $_POST['subscriber_id'], $_POST['target_id'])) {
    echo "Paramètres manquants";
    exit;
}

$action = $_POST['action'];
$subscriber_id = intval($_POST['subscriber_id']);
$target_id = intval($_POST['target_id']);

// Sécurité : un utilisateur ne peut pas s’abonner à lui-même
if ($subscriber_id === $target_id) {
    echo "Impossible de s’abonner à soi-même";
    exit;
}

try {
    if ($action === 'subscribe') {
        // Vérifie si l'abonnement existe déjà
        $check = $pdo->prepare("SELECT * FROM subscriptions WHERE subscriber_id = ? AND target_id = ?");
        $check->execute([$subscriber_id, $target_id]);

        if ($check->rowCount() === 0) {
            // Insère un nouvel abonnement
            $stmt = $pdo->prepare("INSERT INTO subscriptions (subscriber_id, target_id) VALUES (?, ?)");
            $stmt->execute([$subscriber_id, $target_id]);
        }
        echo "ok";
    } elseif ($action === 'unsubscribe') {
        // Supprime l’abonnement existant
        $stmt = $pdo->prepare("DELETE FROM subscriptions WHERE subscriber_id = ? AND target_id = ?");
        $stmt->execute([$subscriber_id, $target_id]);
        echo "ok";
    } else {
        echo "Action inconnue";
    }
} catch (PDOException $e) {
    echo "Erreur SQL : " . $e->getMessage();
}
