<?php
// db_config.php

// ---------------------------------------------------------------------
// 1. PARAMÈTRES DE CONNEXION MySQL (XAMPP par défaut)
// ---------------------------------------------------------------------
$host = 'localhost';
$db_name = 'skillshare_db'; // 🎯 Nom de la base de données à créer
$username = 'root';        // Utilisateur par défaut de XAMPP
$password = '';            // Mot de passe par défaut de XAMPP

// ---------------------------------------------------------------------
// 2. TENTATIVE DE CONNEXION
// ---------------------------------------------------------------------
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    
    // Configurer PDO pour lancer des exceptions en cas d'erreur
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Si la connexion échoue, afficher une erreur et arrêter l'exécution
    http_response_code(500); // Erreur de serveur
    die(json_encode([
        'success' => false,
        'message' => "Erreur de connexion à la base de données : " . $e->getMessage()
    ]));
}

// L'objet $pdo est maintenant prêt à être utilisé pour toutes les requêtes SQL.