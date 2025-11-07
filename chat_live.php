<?php
include 'config.php';
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['user_id'])) exit;

$live_id=intval($_POST['live_id'] ?? $_GET['live_id'] ?? 0);
$user_id=$_SESSION['user_id'];

// Envoi message
if($_SERVER['REQUEST_METHOD']==='POST' && !empty($_POST['message'])){
    $msg=trim($_POST['message']);
    $stmt=$pdo->prepare("INSERT INTO live_chat (live_id,user_id,message) VALUES (:live,:uid,:msg)");
    $stmt->execute(['live'=>$live_id,'uid'=>$user_id,'msg'=>$msg]);
    exit;
}

// Récupérer messages
$stmt=$pdo->prepare("SELECT lc.*, u.last_name AS pseudo FROM live_chat lc JOIN users u ON lc.user_id=u.id WHERE live_id=? ORDER BY date_envoi ASC");
$stmt->execute([$live_id]);
$messages=$stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($messages as $m){
    echo "<p><strong>@".htmlspecialchars($m['pseudo'])."</strong>: ".htmlspecialchars($m['message'])."</p>";
}
?>
