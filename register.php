<?php
session_start();
include "includes/functions.php";

if (currentUser()) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $fullName = trim($_POST['full_name']);

    // Validaciones
    if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
        $error = "Todos los campos son obligatorios";
    } elseif ($password !== $confirmPassword) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email inválido";
    } else {
        $users = readJSON('data/users.json');
        
        // Verificar si el usuario ya existe
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                $error = "El nombre de usuario ya existe";
                break;
            }
            if ($user['email'] === $email) {
                $error = "El email ya está registrado";
                break;
            }
        }

        if (empty($error)) {
            $newUser = [
                'id' => uniqid(),
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'full_name' => $fullName,
                'bio' => '',
                'profile_pic' => 'default.jpg',
                'created_at' => time(),
                'last_active' => time()
            ];

            $users[] = $newUser;
            writeJSON('data/users.json', $users);
            
            $_SESSION['username'] = $username;
            header("Location: index.php");
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
    <title>Registro - Social Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-form">
            <h2>Crear cuenta</h2>
            
            <?php if ($error): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <input type="text" name="full_name" placeholder="Nombre completo" 
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <input type="text" name="username" placeholder="Nombre de usuario" 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <input type="email" name="email" placeholder="Email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>
                
                <div class="form-group">
                    <input type="password" name="confirm_password" placeholder="Confirmar contraseña" required>
                </div>
                
                <button type="submit" class="btn-primary">Registrarse</button>
            </form>

            <div class="auth-links">
                <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
            </div>
        </div>
    </div>
</body>
</html>
