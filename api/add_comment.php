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
    $text = trim($_POST['text'] ?? '');
    $currentUsername = currentUser();
    
    if (empty($text)) {
        echo json_encode(['success' => false, 'error' => 'Comentario vacío']);
        exit;
    }
    
    $comment = [
        'id' => uniqid(),
        'post_id' => $postId,
        'user' => $currentUsername,
        'text' => $text,
        'timestamp' => time()
    ];
    
    $comments = readJSON('../data/comments.json');
    $comments[] = $comment;
    writeJSON('../data/comments.json', $comments);
    
    echo json_encode(['success' => true, 'comment' => $comment]);
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
?>
