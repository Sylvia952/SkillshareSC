<?php
// login.php

// Inclure la configuration de la base de données
require 'db_config.php'; 

// Définir le header pour les réponses JSON (API RESTful)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // IMPORTANT pour les tests React

// Récupérer et décoder les données JSON (attendu de React)
$data = json_decode(file_get_contents("php://input"), true);

// Vérification des champs requis
if (!isset($data['email'], $data['password'])) {
    http_response_code(400); 
    die(json_encode([
        'success' => false,
        'message' => 'Email ou mot de passe manquant.'
    ]));
}

$email = trim($data['email']);
$password = $data['password'];

try {
    // 1. CHERCHER L'UTILISATEUR PAR EMAIL
    $stmt = $pdo->prepare("
        SELECT user_id, password_hash, username 
        FROM users 
        WHERE email = :email
    ");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. VÉRIFIER L'EXISTENCE ET LE MOT DE PASSE
    if ($user && password_verify($password, $user['password_hash'])) {
        
        // 3. SUCCÈS : L'utilisateur est authentifié
        
        // --- SIMULATION JWT ---
        // En vrai, un JWT serait généré ici. Pour le MVP, on renvoie l'ID.
        $user_id = $user['user_id'];
        
        http_response_code(200); // OK
        echo json_encode([
            'success' => true,
            'message' => 'Connexion réussie!',
            'user_id' => $user_id, // L'ID pour identifier l'utilisateur
            'username' => $user['username'],
            'token_simule' => base64_encode($user_id . ':' . time()) // Un token simple pour la démo
        ]);

    } else {
        // 4. ÉCHEC : Email non trouvé ou mot de passe incorrect
        http_response_code(401); // Non autorisé
        echo json_encode([
            'success' => false,
            'message' => 'Email ou mot de passe incorrect.'
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Erreur serveur lors de la connexion."
    ]);
}
?>