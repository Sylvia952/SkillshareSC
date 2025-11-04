<?php
// register.php (VERSION MISE À JOUR)

require 'db_config.php'; // Votre connexion PDO
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

$data = json_decode(file_get_contents("php://input"), true);

// 1. VÉRIFICATION DE TOUS LES CHAMPS REQUIS
if (!isset(
    $data['matricule_number'], $data['last_name'], $data['first_name'], $data['filiere'],
    $data['email'], $data['password'], $data['username']
)) {
    http_response_code(400);
    die(json_encode([
        'success' => false,
        'message' => 'Des données d\'inscription critiques sont manquantes.'
    ]));
}

// 2. NETTOYAGE DES DONNÉES (Sécurité basique)
$matricule = trim($data['matricule_number']);
$lastName = trim($data['last_name']);
$firstName = trim($data['first_name']);
$filiere = trim($data['filiere']);
$email = trim($data['email']);
$password = $data['password']; // Le mot de passe n'est pas "trimé"
$username = trim($data['username']);

// Les champs optionnels peuvent être null ou vides
$photoUrl = $data['profile_picture_url'] ?? NULL; // URL envoyée par React APRES l'upload
$bio = $data['bio'] ?? NULL;

// 3. HACHAGE SÉCURISÉ du mot de passe
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // 4. PRÉPARATION et EXÉCUTION de la requête INSERT
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
        'message' => 'Compte SkillShareSC créé avec succès !'
    ]);
    
} catch (PDOException $e) {
    // 5. Gestion des erreurs de contraintes UNIQUES (Matricule, Email ou Username déjà utilisés)
    if ($e->getCode() == '23000') { 
        http_response_code(409); // Conflit
        $message = "Erreur: Le matricule, l'email ou le nom d'utilisateur est déjà enregistré.";
    } else {
        http_response_code(500);
        $message = "Erreur serveur inattendue.";
        // Pour le debug: $message = "Erreur serveur : " . $e->getMessage();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
}
?>