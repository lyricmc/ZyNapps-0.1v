<?php
session_start();
include "includes/functions.php";

if (!currentUser()) {
    header("Location: login.php");
    exit;
}

$currentUsername = currentUser();
$friends = getUserFriends($currentUsername);
$friendRequests = getFriendRequests($currentUsername);
$sentRequests = getSentFriendRequests($currentUsername);

// Manejar acciones de amistad
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $targetUser = $_POST['user'];
    
    switch ($action) {
        case 'send_request':
            sendFriendRequest($currentUsername, $targetUser);
            break;
        case 'accept_request':
            acceptFriendRequest($targetUser, $currentUsername);
            break;
        case 'reject_request':
            rejectFriendRequest($targetUser, $currentUsername);
            break;
        case 'remove_friend':
            removeFriend($currentUsername, $targetUser);
            break;
    }
    
    header("Location: friends.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amigos - Social Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <div class="friends-container">
        <div class="friends-tabs">
            <button class="tab-btn active" onclick="showTab('friends')">Mis Amigos</button>
            <button class="tab-btn" onclick="showTab('requests')">Solicitudes (<?= count($friendRequests) ?>)</button>
            <button class="tab-btn" onclick="showTab('sent')">Enviadas (<?= count($sentRequests) ?>)</button>
            <button class="tab-btn" onclick="showTab('search')">Buscar</button>
        </div>

        <div id="friends" class="tab-content active">
            <h3>Mis Amigos (<?= count($friends) ?>)</h3>
            <div class="friends-grid">
                <?php foreach ($friends as $friend): ?>
                    <?php $friendProfile = getUserProfile($friend); ?>
                    <div class="friend-card">
                        <img src="assets/uploads/profiles/<?= $friendProfile['profile_pic'] ?? 'default.jpg' ?>" 
                             alt="Profile" class="profile-pic-medium">
                        <h4><?= htmlspecialchars($friendProfile['full_name']) ?></h4>
                        <p>@<?= htmlspecialchars($friend) ?></p>
                        <div class="friend-actions">
                            <button onclick="location.href='profile.php?user=<?= $friend ?>'" class="btn-secondary">Ver perfil</button>
                            <button onclick="location.href='chat.php?with=<?= $friend ?>'" class="btn-primary">Mensaje</button>
                            <button onclick="removeFriend('<?= $friend ?>')" class="btn-danger">Eliminar</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="requests" class="tab-content">
            <h3>Solicitudes de amistad</h3>
            <div class="requests-list">
                <?php foreach ($friendRequests as $request): ?>
                    <?php $requesterProfile = getUserProfile($request['from']); ?>
                    <div class="request-item">
                        <img src="assets/uploads/profiles/<?= $requesterProfile['profile_pic'] ?? 'default.jpg' ?>" 
                             alt="Profile" class="profile-pic-small">
                        <div class="request-info">
                            <h4><?= htmlspecialchars($requesterProfile['full_name']) ?></h4>
                            <p>@<?= htmlspecialchars($request['from']) ?></p>
                            <span class="request-time"><?= timeAgo($request['timestamp']) ?></span>
                        </div>
                        <div class="request-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="accept_request">
                                <input type="hidden" name="user" value="<?= $request['from'] ?>">
                                <button type="submit" class="btn-primary">Aceptar</button>
                            </form>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="reject_request">
                                <input type="hidden" name="user" value="<?= $request['from'] ?>">
                                <button type="submit" class="btn-secondary">Rechazar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="sent" class="tab-content">
            <h3>Solicitudes enviadas</h3>
            <div class="requests-list">
                <?php foreach ($sentRequests as $request): ?>
                    <?php $targetProfile = getUserProfile($request['to']); ?>
                    <div class="request-item">
                        <img src="assets/uploads/profiles/<?= $targetProfile['profile_pic'] ?? 'default.jpg' ?>" 
                             alt="Profile" class="profile-pic-small">
                        <div class="request-info">
                            <h4><?= htmlspecialchars($targetProfile['full_name']) ?></h4>
                            <p>@<?= htmlspecialchars($request['to']) ?></p>
                            <span class="request-time">Enviada <?= timeAgo($request['timestamp']) ?></span>
                        </div>
                        <div class="request-actions">
                            <span class="pending-status">Pendiente</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="search" class="tab-content">
            <h3>Buscar usuarios</h3>
            <div class="search-form">
                <input type="text" id="searchInput" placeholder="Buscar por nombre o usuario..." onkeyup="searchUsers()">
            </div>
            <div id="searchResults" class="search-results"></div>
        </div>
    </div>

    <script src="assets/js/friends.js"></script>
</body>
</html>
