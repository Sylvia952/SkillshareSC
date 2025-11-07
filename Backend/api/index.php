<?php
// index.php (Ancien login.php) - Gère la connexion utilisateur

// S'assurer que le chemin vers config.php est correct (ex: require 'config.php';)
require 'config.php'; 

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

// VÉRIFICATION CRITIQUE : Si la connexion a échoué dans config.php, on arrête ici.
if (!isset($pdo) || !($pdo instanceof PDO)) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Erreur de configuration interne: La connexion à la DB ($pdo) a échoué. Vérifiez config.php.'
    ]));
}

// 1. UTILISE $_POST POUR LIRE LE FORM-DATA (pour la connexion)
if (!isset($_POST['email'], $_POST['password'])) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Email ou mot de passe manquant.']));
}

$email = trim($_POST['email']);
$password = $_POST['password'];

try {
    // 2. RÉCUPÉRATION DU HACHAGE DU MOT DE PASSE ET DE L'UTILISATEUR
    $stmt = $pdo->prepare("SELECT user_id, password_hash, username FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // 3. SUCCÈS : CONNEXION RÉUSSIE
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Connexion réussie!',
            'user_id' => $user['user_id'],
            'username' => $user['username']
        ]);
    } else {
        // 4. ÉCHEC : AUCUN UTILISATEUR TROUVÉ OU MOT DE PASSE INCORRECT
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Identifiants invalides.']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "Erreur de base de données : " . $e->getMessage()]);
}
?>