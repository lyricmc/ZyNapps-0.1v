<?php
session_start();
include "includes/functions.php";

if (!currentUser()) {
    header("Location: login.php");
    exit;
}

$posts = readJSON('data/posts.json');
$likes = readJSON('data/likes.json');
$comments = readJSON('data/comments.json');
$friends = readJSON('data/friends.json');
$currentUsername = currentUser();

// Filtrar posts de amigos si es necesario
$userFriends = getUserFriends($currentUsername);
$filteredPosts = [];

foreach ($posts as $post) {
    if ($post['user'] === $currentUsername || in_array($post['user'], $userFriends) || $post['privacy'] === 'public') {
        $filteredPosts[] = $post;
    }
}

// Ordenar por timestamp descendente
usort($filteredPosts, function($a, $b) {
    return $b['timestamp'] - $a['timestamp'];
});
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <div class="container">
        <div class="main-content">
            <div class="create-post">
                <h3>¿Qué estás pensando?</h3>
                <form action="upload.php" method="POST" enctype="multipart/form-data">
                    <textarea name="description" placeholder="Escribe algo..." rows="3"></textarea>
                    <input type="file" name="media" accept="image/*,video/*">
                    <select name="privacy">
                        <option value="public">Público</option>
                        <option value="friends">Solo amigos</option>
                    </select>
                    <button type="submit">Publicar</button>
                </form>
            </div>

            <div class="feed">
                <?php foreach ($filteredPosts as $post): ?>
                    <div class="post" data-post-id="<?= $post['id'] ?>">
                        <div class="post-header">
                            <div class="user-info">
                                <img src="assets/uploads/profiles/<?= getUserProfile($post['user'])['profile_pic'] ?? 'default.jpg' ?>" 
                                     alt="Profile" class="profile-pic-small">
                                <span class="username"><?= htmlspecialchars($post['user']) ?></span>
                            </div>
                            <span class="timestamp"><?= timeAgo($post['timestamp']) ?></span>
                        </div>

                        <?php if (!empty($post['description'])): ?>
                            <div class="post-description">
                                <?= nl2br(htmlspecialchars($post['description'])) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($post['file'])): ?>
                            <div class="post-media">
                                <?php if ($post['type'] === 'image'): ?>
                                    <img src="assets/uploads/<?= $post['file'] ?>" alt="Post image">
                                <?php else: ?>
                                    <video controls>
                                        <source src="assets/uploads/<?= $post['file'] ?>" type="video/mp4">
                                    </video>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="post-actions">
                            <button class="like-btn <?= hasUserLiked($post['id'], $currentUsername) ? 'liked' : '' ?>" 
                                    onclick="toggleLike('<?= $post['id'] ?>')">
                                ❤️ <span class="like-count"><?= getLikeCount($post['id']) ?></span>
                            </button>
                            <button class="comment-btn" onclick="toggleComments('<?= $post['id'] ?>')">
                                💬 Comentar
                            </button>
                            <button class="share-btn">📤 Compartir</button>
                        </div>

                        <div class="comments-section" id="comments-<?= $post['id'] ?>" style="display: none;">
                            <div class="comments-list">
                                <?php 
                                $postComments = getPostComments($post['id']);
                                foreach ($postComments as $comment): 
                                ?>
                                    <div class="comment">
                                        <strong><?= htmlspecialchars($comment['user']) ?>:</strong>
                                        <?= nl2br(htmlspecialchars($comment['text'])) ?>
                                        <span class="comment-time"><?= timeAgo($comment['timestamp']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <form class="comment-form" onsubmit="addComment(event, '<?= $post['id'] ?>')">
                                <input type="text" placeholder="Escribe un comentario..." required>
                                <button type="submit">Enviar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="sidebar">
            <div class="friends-suggestions">
                <h4>Sugerencias de amistad</h4>
                <?php 
                $suggestions = getFriendSuggestions($currentUsername);
                foreach ($suggestions as $suggestion): 
                ?>
                    <div class="friend-suggestion">
                        <img src="assets/uploads/profiles/<?= $suggestion['profile_pic'] ?? 'default.jpg' ?>" 
                             alt="Profile" class="profile-pic-small">
                        <span><?= htmlspecialchars($suggestion['username']) ?></span>
                        <button onclick="sendFriendRequest('<?= $suggestion['username'] ?>')">Agregar</button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="online-friends">
                <h4>Amigos en línea</h4>
                <?php foreach ($userFriends as $friend): ?>
                    <div class="friend-item" onclick="openChat('<?= $friend ?>')">
                        <img src="assets/uploads/profiles/<?= getUserProfile($friend)['profile_pic'] ?? 'default.jpg' ?>" 
                             alt="Profile" class="profile-pic-small">
                        <span><?= htmlspecialchars($friend) ?></span>
                        <span class="online-indicator"></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
