<?php
session_start();
include "includes/functions.php";

if (!currentUser()) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $privacy = $_POST['privacy'] ?? 'public';
    $currentUsername = currentUser();
    
    $post = [
        'id' => uniqid(),
        'user' => $currentUsername,
        'description' => $description,
        'privacy' => $privacy,
        'timestamp' => time(),
        'file' => '',
        'type' => ''
    ];

    // Manejar archivo subido
    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'assets/uploads/';
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm'];
        
        if (in_array($_FILES['media']['type'], $allowedTypes)) {
            $extension = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            
            if (move_uploaded_file($_FILES['media']['tmp_name'], $uploadDir . $filename)) {
                $post['file'] = $filename;
                $post['type'] = explode('/', $_FILES['media']['type'])[0];
            }
        }
    }

    // Guardar post
    $posts = readJSON('data/posts.json');
    $posts[] = $post;
    writeJSON('data/posts.json', $posts);
    
    header("Location: index.php");
    exit;
}
?>
