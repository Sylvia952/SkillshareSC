<?php
// inscription.php - Gestion de l'inscription utilisateur (Utilise $_POST / FORM-DATA)

// ASSUREZ-VOUS QUE 'config.php' est au même niveau que ce fichier ou que le chemin est correct.
require 'config.php'; 

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, OPTIONS');

// Gérer la requête OPTIONS (nécessaire pour CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 1. VÉRIFICATION DE TOUS LES CHAMPS REQUIS DANS $_POST
if (!isset(
    $_POST['matricule_number'], $_POST['last_name'], $_POST['first_name'], $_POST['filiere'],
    $_POST['email'], $_POST['password'], $_POST['username']
)) {
    http_response_code(400);
    die(json_encode([
        'success' => false,
        'message' => 'Des données d\'inscription critiques sont manquantes. L\'envoi doit être en FORM-DATA.'
    ]));
}

// 2. NETTOYAGE ET ASSIGNATION (Utilisation de $_POST !)
$matricule = trim($_POST['matricule_number']);
$lastName = trim($_POST['last_name']);
$firstName = trim($_POST['first_name']);
$filiere = trim($_POST['filiere']);
$email = trim($_POST['email']);
$password = $_POST['password']; 
$username = trim($_POST['username']);

// Les champs optionnels (laisser null s'ils ne sont pas envoyés)
$photoUrl = null; 
$bio = null;

// 3. HACHAGE SÉCURISÉ du mot de passe
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // 4. PRÉPARATION et EXÉCUTION de la requête INSERT (Méthode directe)
    $stmt = $pdo->prepare("
        INSERT INTO users (
            matricule_number, last_name, first_name, filiere,
            email, password_hash, username, profile_picture_url, bio
        ) 
        VALUES (
            :matricule, :lastName, :firstName, :filiere,
            :email, :password_hash, :username, :photoUrl, :bio
        )
    ");
    
    $stmt->execute([
        'matricule' => $matricule,
        'lastName' => $lastName,
        'firstName' => $firstName,
        'filiere' => $filiere,
        'email' => $email,
        'password_hash' => $password_hash,
        'username' => $username,
        'photoUrl' => $photoUrl,
        'bio' => $bio
    ]);
    
    http_response_code(201); // Créé
    echo json_encode([
        'success' => true,
        'message' => 'Compte SkillShareSC créé avec succès !',
        'user_id' => $pdo->lastInsertId()
    ]);
    
} catch (PDOException $e) {
    // Gestion des erreurs
    if ($e->getCode() === '23000') { 
        // 23000 est l'erreur pour violation de clé UNIQUE (doublon)
        http_response_code(409); // Conflit
        $message = "Erreur: Le matricule, l'email ou le nom d'utilisateur est déjà enregistré.";
    } else {
        http_response_code(500);
        // Afficher l'erreur exacte pour le débug si ce n'est pas un doublon
        $message = "Erreur serveur : " . $e->getMessage(); 
    }
    
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
}
?>