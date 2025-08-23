<?php
session_start();
include "../includes/functions.php";

header('Content-Type: application/json');

if (!currentUser()) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = $_POST['post_id'] ?? '';
    $currentUsername = currentUser();
    
    $likes = readJSON('../data/likes.json');
    $userLiked = false;
    $likeIndex = -1;
    
    // Buscar si el usuario ya dio like
    foreach ($likes as $index => $like) {
        if ($like['post_id'] === $postId && $like['user'] === $currentUsername) {
            $userLiked = true;
            $likeIndex = $index;
            break;
        }
    }
    
    if ($userLiked) {
        // Quitar like
        unset($likes[$likeIndex]);
        $likes = array_values($likes);
    } else {
        // Agregar like
        $likes[] = [
            'post_id' => $postId,
            'user' => $currentUsername,
            'timestamp' => time()
        ];
    }
    
    writeJSON('../data/likes.json', $likes);
    
    $likeCount = 0;
    foreach ($likes as $like) {
        if ($like['post_id'] === $postId) {
            $likeCount++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'liked' => !$userLiked,
        'count' => $likeCount
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
?>
