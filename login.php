<?php
session_start();
include "includes/functions.php";

if (currentUser()) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Todos los campos son obligatorios";
    } else {
        $users = readJSON('data/users.json');
        
        foreach ($users as $key => $user) {
            if (($user['username'] === $username || $user['email'] === $username) && 
                password_verify($password, $user['password'])) {
                
                // Actualizar última actividad
                $users[$key]['last_active'] = time();
                writeJSON('data/users.json', $users);
                
                $_SESSION['username'] = $user['username'];
                header("Location: index.php");
                exit;
            }
        }
        $error = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Social Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-form">
            <h2>Iniciar Sesión</h2>
            
            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Usuario o email" 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>
                
                <button type="submit" class="btn-primary">Iniciar Sesión</button>
            </form>

            <div class="auth-links">
                <p>¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
            </div>
        </div>
    </div>
</body>
</html>
