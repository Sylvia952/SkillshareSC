<?php
// inscription.php - Gestion de l'inscription utilisateur (Utilise $_POST / FORM-DATA)

require 'config.php'; // Votre connexion PDO
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
    // Le message indique au développeur de vérifier le format d'envoi.
    die(json_encode([
        'success' => false,
        'message' => 'Des données d\'inscription critiques sont manquantes. (Vérifiez si l\'envoi est bien en FORM-DATA)'
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

// Les champs optionnels ne sont pas envoyés par notre test actuel, mais on les prépare
$photoUrl = null; 
$bio = null;

// 3. HACHAGE SÉCURISÉ du mot de passe
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // 4. VÉRIFICATION DES DOUBLONS (Email ou Matricule) - CRITIQUE POUR LA VÉRIFICATION UNI.)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM users 
        WHERE email = :email OR matricule_number = :matricule OR username = :username
    ");
    $stmt->execute(['email' => $email, 'matricule' => $matricule, 'username' => $username]);
    if ($stmt->fetchColumn() > 0) {
        http_response_code(409); // Conflict
        die(json_encode([
            'success' => false,
            'message' => "Erreur: Le matricule, l'email ou le nom d'utilisateur est déjà enregistré."
        ]));
    }


    // 5. PRÉPARATION et EXÉCUTION de la requête INSERT
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
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => "Erreur serveur inattendue : " . $e->getMessage()
    ]);
}
?>