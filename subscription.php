<?php
// Connexion MySQL
$host = "localhost";
$dbname = "unitok";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur connexion : " . $e->getMessage());
}

$action = $_POST['action'] ?? '';
$subscriber_id = intval($_POST['subscriber_id'] ?? 0);
$target_id = intval($_POST['target_id'] ?? 0);

if ($subscriber_id <= 0 || $target_id <= 0) {
    die("Paramètres invalides");
}
if ($subscriber_id === $target_id) {
    die("Impossible de s’abonner à soi-même");
}

switch ($action) {
    case 'subscribe':
        $check = $pdo->prepare("SELECT * FROM subscriptions WHERE subscriber_id=? AND subscribed_to_id=?");
        $check->execute([$subscriber_id, $target_id]);
        if ($check->rowCount() == 0) {
            $stmt = $pdo->prepare("INSERT INTO subscriptions (subscriber_id, subscribed_to_id) VALUES (?, ?)");
            $stmt->execute([$subscriber_id, $target_id]);
            echo "ok";
        } else {
            echo "déjà abonné";
        }
        break;

    case 'unsubscribe':
        $stmt = $pdo->prepare("DELETE FROM subscriptions WHERE subscriber_id=? AND subscribed_to_id=?");
        $stmt->execute([$subscriber_id, $target_id]);
        echo "ok";
        break;

    case 'check':
        $stmt = $pdo->prepare("SELECT 1 FROM subscriptions WHERE subscriber_id=? AND subscribed_to_id=?");
        $stmt->execute([$subscriber_id, $target_id]);
        echo $stmt->rowCount() > 0 ? "subscribed" : "not_subscribed";
        break;

    default:
        echo "Action invalide";
}
?>
