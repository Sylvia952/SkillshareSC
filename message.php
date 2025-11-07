<?php
session_start();
include('includes/config.php'); // doit définir $pdo (PDO)

// Vérifier la session
if (!isset($_SESSION['users_id'])) {
    header("Location: login.php");
    exit;
}

// Utiliser une seule variable ($user_id) et forcer en int
$user_id = intval($_SESSION['users_id']);

// Récupérer la liste des amis acceptés (dans les deux sens)
$query = "
    SELECT u.id, u.username
    FROM amis a
    JOIN users u 
      ON ( (a.users_id = :uid AND a.amis_id = u.id)
         OR (a.amis_id = :uid AND a.users_id = u.id) )
    WHERE a.status = 'accepted'
    ORDER BY u.username ASC
";
$stmt = $pdo->prepare($query);
$stmt->execute(array(':uid' => $user_id));
$amis = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les messages avec un ami sélectionné
$messages = array();
$amis_id = isset($_GET['amis_id']) ? intval($_GET['amis_id']) : null;
if ($amis_id) {
    $query = "
        SELECT m.*, u.username AS sender_name
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = :me AND m.receiver_id = :ami)
           OR (m.sender_id = :ami AND m.receiver_id = :me)
        ORDER BY m.created_at ASC
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute(array(':me' => $user_id, ':amis' => $amis_id));
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Messagerie</title>
    <style>
        body { font-family: Arial, sans-serif; margin:0; background:#f4f4f9; }
        .container { display:flex; max-width:1000px; margin:20px auto; background:#fff; border-radius:8px; box-shadow:0 0 10px rgba(0,0,0,.08); overflow:hidden; }
        .amis-list { width:250px; background:#fafafa; border-right:1px solid #e6e6e6; padding:10px; }
        .amis-list h3 { margin:0 0 10px 0; color:#333; }
        .amis-list ul { list-style:none; padding:0; margin:0; }
        .amis-list li { padding:10px; border-bottom:1px solid #eee; cursor:pointer; }
        .amis-list li:hover { background:#f0f0f0; }
        .chat-area { flex:1; padding:20px; }
        .messages { height:400px; overflow-y:scroll; border:1px solid #ddd; padding:10px; margin-bottom:10px; border-radius:4px; background:#fff; }
        .message { margin-bottom:10px; padding:8px 12px; border-radius:6px; max-width:75%; }
        .message.me { background:#e6ffe6; margin-left:auto; text-align:right; }
        .message.other { background:#f1f1f1; margin-right:auto; text-align:left; }
        .message small { display:block; color:#888; margin-top:6px; font-size:12px; }
        .message-form textarea { width:100%; padding:10px; border:1px solid #ddd; border-radius:4px; resize:vertical; }
        .message-form button { background:#4CAF50; color:#fff; border:none; padding:10px 15px; border-radius:4px; cursor:pointer; margin-top:8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="amis-list">
            <h3>Amis</h3>
            <ul>
                <?php if (empty($amis)): ?>
                    <li>Aucun ami pour le moment.</li>
                <?php else: ?>
                    <?php foreach ($amis as $ami): ?>
                        <li onclick="window.location.href='messagerie.php?ami_id=<?php echo intval($ami['id']); ?>'">
                            <?php echo htmlspecialchars($ami['username'], ENT_QUOTES, 'UTF-8'); ?>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <div class="chat-area">
            <h3>Conversation</h3>
            <div class="messages" id="messages">
                <?php if (empty($messages)): ?>
                    <p style="color:#666;">Aucun message pour cette conversation.</p>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <?php
                        $isMe = intval($message['sender_id']) === $users_id;
                        $cls = $isMe ? 'me' : 'other';
                        ?>
                        <div class="message <?php echo $cls; ?>">
                            <strong><?php echo htmlspecialchars($message['sender_name'], ENT_QUOTES, 'UTF-8'); ?>:</strong>
                            <div><?php echo nl2br(htmlspecialchars($message['message'], ENT_QUOTES, 'UTF-8')); ?></div>
                            <small><?php echo htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8'); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php if ($amis_id): ?>
                <form action="envoyer_message.php" method="post" class="message-form">
                    <input type="hidden" name="receiver_id" value="<?php echo intval($ami_id); ?>">
                    <textarea name="message" rows="3" placeholder="Écrivez votre message..." required></textarea>
                    <button type="submit">Envoyer</button>
                </form>
            <?php else: ?>
                <p>Sélectionnez un ami pour envoyer un message.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
    // Scroll to bottom si des messages existent
    (function(){
      var box = document.getElementById('messages');
      if(box) box.scrollTop = box.scrollHeight;
    })();
    </script>
</body>
</html>
