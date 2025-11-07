<?php
include 'config.php';
if(session_status()===PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Lives en direct - UniTok</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background: radial-gradient(circle,#0d1117,#000); color:#fff; font-family:Poppins,Arial,sans-serif; margin:0; padding:0;}
.feed-container { display:flex; flex-direction:column; gap:20px; align-items:center; padding-bottom:50px;}
.live-card { background:#111; border-radius:15px; padding:10px; width:400px; box-shadow:0 0 20px rgba(0,255,153,0.2); transition: transform 0.3s; position:relative;}
.live-card:hover { transform:translateY(-5px); }
.live-badge { position:absolute; top:10px; right:10px; background:red; color:#fff; padding:3px 8px; border-radius:12px; font-size:0.8rem; font-weight:bold; }
.btn-join { background:linear-gradient(90deg,#007bff,#00ff99); color:#fff; border:none; border-radius:50px; padding:8px 20px; font-weight:bold; transition:0.3s;}
.btn-join:hover { box-shadow:0 0 15px rgba(0,255,153,0.5);}
.live-title { font-weight:bold; font-size:1.1rem; margin:0;}
.live-user { color:#aaa; font-size:0.9rem;}
</style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<div class="container my-4">
<h1 class="text-center mb-4"><i class="bi bi-broadcast-pin"></i> Lives en direct</h1>
<div class="feed-container" id="liveFeed">
  <!-- Lives will be loaded here -->
</div>
</div>
<script>
// Fonction pour récupérer les lives actifs via AJAX
async function loadLives(){
    const res = await fetch('fetch_lives.php');
    const data = await res.json();
    const currentUserId = data.user_id;
    const lives = data.lives;

    const feed = document.getElementById('liveFeed');
    feed.innerHTML = '';
    if(lives.length === 0){
        feed.innerHTML = '<p class="text-center text-secondary">Aucun live en cours 😴</p>';
    } else {
        lives.forEach(live=>{
            const card = document.createElement('div');
            card.className = 'live-card';

            // Bouton "Arrêter le live" seulement pour l’utilisateur qui a démarré
            const stopBtn = (currentUserId === live.user_id) 
                ? `<button class="btn btn-danger btn-sm mt-2" onclick="stopLive(${live.id}, this)">⏹ Arrêter le live</button>` 
                : '';

            card.innerHTML = `
                <div class="live-badge">EN DIRECT</div>
                <p class="live-title">@${live.pseudo} - ${live.titre}</p>
                <p class="live-user">Depuis ${new Date(live.date_debut).toLocaleString('fr-FR')}</p>
                <a href="watch_live.php?live_id=${live.id}" class="btn btn-join mt-2"><i class="bi bi-eye"></i> Rejoindre</a>
                ${stopBtn}
            `;
            feed.appendChild(card);
        });
    }
}

// Arrêter le live via fetch
async function stopLive(liveId, btn){
    if(confirm("Voulez-vous vraiment arrêter ce live ?")){
        const res = await fetch('stop_live.php', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:`live_id=${liveId}`
        });
        const data = await res.text();
        if(data === 'ok'){
            btn.closest('.live-card').remove();
        } else {
            alert("Erreur lors de l'arrêt du live.");
        }
    }
}

// Initial load
loadLives();
// Rafraîchissement toutes les 5s
setInterval(loadLives,5000);
</script>
