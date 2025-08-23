<?php
session_start();
include "includes/functions.php";

if (!currentUser()) {
    header("Location: login.php");
    exit;
}

$currentUsername = currentUser();
$chatWith = $_GET['with'] ?? '';
$friends = getUserFriends($currentUsername);

// Verificar que el usuario con quien chatear es amigo
if (!empty($chatWith) && !in_array($chatWith, $friends)) {
    $chatWith = '';
}

$messages = readJSON('data/messages.json');
$chatMessages = [];

if (!empty($chatWith)) {
    foreach ($messages as $message) {
        if (($message['from'] === $currentUsername && $message['to'] === $chatWith) ||
            ($message['from'] === $chatWith && $message['to'] === $currentUsername)) {
            $chatMessages[] = $message;
        }
    }
    
    // Ordenar por timestamp
    usort($chatMessages, function($a, $b) {
        return $a['timestamp'] - $b['timestamp'];
    });
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Social Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <div class="chat-container">
        <div class="chat-sidebar">
            <h3>Conversaciones</h3>
            <div class="friends-list">
                <?php foreach ($friends as $friend): ?>
                    <div class="friend-item <?= $friend === $chatWith ? 'active' : '' ?>" 
                         onclick="location.href='chat.php?with=<?= $friend ?>'">
                        <img src="assets/uploads/profiles/<?= getUserProfile($friend)['profile_pic'] ?? 'default.jpg' ?>" 
                             alt="Profile" class="profile-pic-small">
                        <div class="friend-info">
                            <span class="friend-name"><?= htmlspecialchars($friend) ?></span>
                            <span class="last-message">Última conexión: <?= timeAgo(getUserProfile($friend)['last_active'] ?? time()) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="chat-main">
            <?php if (!empty($chatWith)): ?>
                <div class="chat-header">
                    <img src="assets/uploads/profiles/<?= getUserProfile($chatWith)['profile_pic'] ?? 'default.jpg' ?>" 
                         alt="Profile" class="profile-pic-small">
                    <h3><?= htmlspecialchars($chatWith) ?></h3>
                </div>

                <div class="chat-messages" id="chatMessages">
                    <?php foreach ($chatMessages as $message): ?>
                        <div class="message <?= $message['from'] === $currentUsername ? 'sent' : 'received' ?>">
                            <div class="message-content">
                                <?php if (!empty($message['file'])): ?>
                                    <?php if ($message['type'] === 'image'): ?>
                                        <img src="assets/uploads/chat/<?= $message['file'] ?>" alt="Image" class="chat-image">
                                    <?php endif; ?>
                                <?php endif; ?>
                                <?php if (!empty($message['message'])): ?>
                                    <p><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                                <?php endif; ?>
                                <span class="message-time"><?= date('H:i', $message['timestamp']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <form class="chat-form" id="chatForm" enctype="multipart/form-data">
                    <input type="hidden" name="to" value="<?= htmlspecialchars($chatWith) ?>">
                    <input type="file" id="fileInput" accept="image/*" style="display: none;">
                    <button type="button" onclick="document.getElementById('fileInput').click()">📎</button>
                    <input type="text" name="message" placeholder="Escribe un mensaje..." required>
                    <button type="submit">Enviar</button>
                </form>
            <?php else: ?>
                <div class="no-chat-selected">
                    <h3>Selecciona una conversación</h3>
                    <p>Elige un amigo de la lista para comenzar a chatear</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/js/chat.js"></script>
</body>
</html>
