<?php
include 'config.php';
if(session_status()===PHP_SESSION_NONE) session_start();

$live_id = intval($_POST['live_id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? 0;

if($live_id && $user_id){
    $stmt = $pdo->prepare("UPDATE lives SET active=0 WHERE id=? AND user_id=?");
    $stmt->execute([$live_id, $user_id]);
    if($stmt->rowCount() > 0){
        echo 'ok';
    } else {
        echo 'error';
    }
} else {
    echo 'error';
}
