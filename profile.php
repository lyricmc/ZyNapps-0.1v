<?php
session_start();
include "includes/functions.php";

if (!currentUser()) {
    header("Location: login.php");
    exit;
}

$currentUsername = currentUser();
$profileUsername = $_GET['user'] ?? $currentUsername;
$isOwnProfile = ($profileUsername === $currentUsername);

$userProfile = getUserProfile($profileUsername);
if (!$userProfile) {
    header("Location: index.php");
    exit;
}

$userPosts = getUserPosts($profileUsername);
$friends = getUserFriends($profileUsername);
$isFriend = in_array($profileUsername, getUserFriends($currentUsername));

// Manejar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isOwnProfile) {
    $fullName = trim($_POST['full_name'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    
    $users = readJSON('data/users.json');
    foreach ($users as $key => $user) {
        if ($user['username'] === $currentUsername) {
            $users[$key]['full_name'] = $fullName;
            $users[$key]['bio'] = $bio;
            
            // Manejar foto de perfil
            if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'assets/uploads/profiles/';
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                
                if (in_array($_FILES['profile_pic']['type'], $allowedTypes)) {
                    $extension = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
                    $filename = $currentUsername . '_' . uniqid() . '.' . $extension;
                    
                    if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadDir . $filename)) {
                        $users[$key]['profile_pic'] = $filename;
                    }
                }
            }
            
            writeJSON('data/users.json', $users);
            header("Location: profile.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de <?= htmlspecialchars($userProfile['full_name']) ?> - Social Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include "includes/header.php"; ?>
    
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-info">
                <img src="assets/uploads/profiles/<?= $userProfile['profile_pic'] ?? 'default.jpg' ?>" 
                     alt="Profile Picture" class="profile-pic-large">
                <div class="profile-details">
                    <h2><?= htmlspecialchars($userProfile['full_name']) ?></h2>
                    <p class="username">@<?= htmlspecialchars($userProfile['username']) ?></p>
                    <p class="bio"><?= nl2br(htmlspecialchars($userProfile['bio'])) ?></p>
                    
                    <div class="profile-stats">
                        <span><strong><?= count($userPosts) ?></strong> publicaciones</span>
                        <span><strong><?= count($friends) ?></strong> amigos</span>
                    </div>

                    <div class="profile-actions">
                        <?php if ($isOwnProfile): ?>
                            <button onclick="toggleEditProfile()" class="btn-secondary">Editar perfil</button>
                        <?php else: ?>
                            <?php if ($isFriend): ?>
                                <button onclick="location.href='chat.php?with=<?= $profileUsername ?>'" class="btn-primary">Enviar mensaje</button>
                                <button onclick="removeFriend('<?= $profileUsername ?>')" class="btn-secondary">Eliminar amigo</button>
                            <?php else: ?>
                                <button onclick="sendFriendRequest('<?= $profileUsername ?>')" class="btn-primary">Agregar amigo</button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($isOwnProfile): ?>
            <div id="editProfileForm" class="edit-profile-form" style="display: none;">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Foto de perfil:</label>
                        <input type="file" name="profile_pic" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label>Nombre completo:</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($userProfile['full_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Biografía:</label>
                        <textarea name="bio" rows="3"><?= htmlspecialchars($userProfile['bio']) ?></textarea>
                    </div>
                    <button type="submit" class="btn-primary">Guardar cambios</button>
                    <button type="button" onclick="toggleEditProfile()" class="btn-secondary">Cancelar</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="profile-posts">
            <h3>Publicaciones</h3>
            <div class="posts-grid">
                <?php foreach ($userPosts as $post): ?>
                    <div class="post-thumbnail" onclick="openPost('<?= $post['id'] ?>')">
                        <?php if (!empty($post['file'])): ?>
                            <?php if ($post['type'] === 'image'): ?>
                                <img src="assets/uploads/<?= $post['file'] ?>" alt="Post">
                            <?php else: ?>
                                <video>
                                    <source src="assets/uploads/<?= $post['file'] ?>" type="video/mp4">
                                </video>
                                <div class="video-overlay">▶️</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-post">
                                <p><?= htmlspecialchars(substr($post['description'], 0, 100)) ?>...</p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-stats">
                            <span>❤️ <?= getLikeCount($post['id']) ?></span>
                            <span>💬 <?= getCommentCount($post['id']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="assets/js/profile.js"></script>
</body>
</html>
