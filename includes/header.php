<?php
$currentUsername = currentUser();
$userProfile = getUserProfile($currentUsername);
?>

<header class="main-header">
    <div class="header-container">
        <div class="logo">
            <a href="index.php">
                <h1>SocialPlatform</h1>
            </a>
        </div>

        <nav class="main-nav">
            <a href="index.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                🏠 Inicio
            </a>
            <a href="friends.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'friends.php' ? 'active' : '' ?>">
                👥 Amigos
            </a>
            <a href="chat.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'chat.php' ? 'active' : '' ?>">
                💬 Chat
            </a>
            <a href="profile.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : '' ?>">
                👤 Perfil
            </a>
        </nav>

        <div class="header-user">
            <div class="user-info">
                <img src="assets/uploads/profiles/<?= $userProfile['profile_pic'] ?? 'default.jpg' ?>" 
                     alt="Profile" class="profile-pic-small">
                <span class="username"><?= htmlspecialchars($currentUsername) ?></span>
            </div>
            <div class="user-menu">
                <button onclick="toggleUserMenu()" class="menu-btn">⚙️</button>
                <div id="userMenu" class="dropdown-menu">
                    <a href="profile.php">Mi perfil</a>
                    <a href="settings.php">Configuración</a>
                    <a href="logout.php">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </div>
</header>
