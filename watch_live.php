<?php
include 'config.php';
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['user_id'])){ header("Location: login.php"); exit; }

$live_id = intval($_GET['live_id'] ?? 0);
$stmt=$pdo->prepare("SELECT l.*, u.last_name AS pseudo, l.video_path FROM lives l JOIN users u ON l.user_id=u.id WHERE l.id=? AND l.active=1");
$stmt->execute([$live_id]);
$live=$stmt->fetch(PDO::FETCH_ASSOC);
if(!$live){ die("Live introuvable ou terminé"); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Live - <?=$live['titre']?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body {
    background: radial-gradient(circle,#0d1117,#000);
    color:#fff;
    font-family:Poppins,Arial,sans-serif;
    margin:0;
    padding:0;
}
.live-container {
    max-width:800px;
    margin:40px auto;
}
video {
    width:100%;
    border-radius:10px;
    background:#000;
}
#chatBox {
    background:#111;
    padding:10px;
    height:300px;
    overflow-y:auto;
    border-radius:10px;
    margin-top:15px;
    margin-bottom:10px;
}
#chatBox .message {
    margin-bottom:8px;
}
#chatBox .message .user {
    font-weight:bold;
    color:#00ff99;
}
#chatBox .message .text {
    margin-left:5px;
    color:#fff;
}
#chatInput {
    border-radius:50px;
    background:#222;
    color:#fff;
    border:1px solid #333;
}
#sendBtn {
    border-radius:50px;
}
</style>
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="container live-container">
    <h3>@<?=$live['pseudo']?> - <?=$live['titre']?></h3>
    
    <!-- Vidéo du live -->
    <video id="remoteVideo" controls autoplay playsinline>
        <source src="<?=htmlspecialchars($live['video_path'])?>" type="video/mp4">
        Votre navigateur ne supporte pas la lecture vidéo.
    </video>

    <!-- Chat -->
    <h5 class="mt-3">Chat en direct</h5>
    <div id="chatBox"></div>
    <div class="input-group">
        <input type="text" id="chatInput" class="form-control" placeholder="Écrire un message...">
        <button class="btn btn-success" id="sendBtn"><i class="bi bi-send-fill"></i> Envoyer</button>
    </div>
</div>

<script>
const liveId = <?=$live['id']?>;
const chatBox = document.getElementById('chatBox');
const chatInput = document.getElementById('chatInput');
const sendBtn = document.getElementById('sendBtn');

// Charger les messages via AJAX
async function loadChat(){
    const res = await fetch('chat_live.php?live_id='+liveId);
    const data = await res.json(); // on suppose JSON [{user,message,time},...]
    chatBox.innerHTML = '';
    data.forEach(msg=>{
        const div = document.createElement('div');
        div.className = 'message';
        div.innerHTML = `<span class="user">@${msg.user}</span>: <span class="text">${msg.message}</span>`;
        chatBox.appendChild(div);
    });
    chatBox.scrollTop = chatBox.scrollHeight;
}

// Envoi d'un message
sendBtn.addEventListener('click', async()=>{
    const msg = chatInput.value.trim();
    if(msg){
        await fetch('chat_live.php', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:`live_id=${liveId}&message=${encodeURIComponent(msg)}`
        });
        chatInput.value='';
        loadChat();
    }
});

// Charger messages toutes les 2 secondes
loadChat();
setInterval(loadChat,2000);

// Envoi avec Enter
chatInput.addEventListener('keypress', (e)=>{
    if(e.key==='Enter'){ sendBtn.click(); e.preventDefault(); }
});
</script>

<?php include 'includes/footer.php'; ?>
</body>
</html>
