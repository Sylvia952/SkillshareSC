<?php
// config.php - Configuration de la connexion à la base de données SkillShareSC

// ---------------------------------------------------------------------
// 1. PARAMÈTRES DE CONNEXION MySQL (XAMPP par défaut)
// ---------------------------------------------------------------------
$host = 'localhost';
$db_name = 'skillshare_db'; // ✅ Le nom est maintenant correct !
$username = 'root';        
$password = '';            

// ---------------------------------------------------------------------
// 2. TENTATIVE DE CONNEXION (avec gestion d'erreur)
// ---------------------------------------------------------------------
try {
    // Utilisation de utf8mb4 pour une meilleure compatibilité des caractères
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    
    // Configurer PDO pour lancer des exceptions en cas d'erreur (critique pour le debug)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Si la connexion échoue, renvoyer une réponse JSON (pour le frontend) et arrêter l'exécution
    http_response_code(500); 
    die(json_encode([
        'success' => false,
        'message' => "Erreur de connexion à la base de données : " . $e->getMessage()
    ]));
}

// L'objet $pdo est maintenant prêt à être utilisé par tous les scripts API.
?>