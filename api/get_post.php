<?php
session_start();
include "../includes/functions.php";

header('Content-Type: application/json');

if (!currentUser()) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$postId = $_GET['id'] ?? '';
$currentUsername = currentUser();

if (empty($postId)) {
    echo json_encode(['success' => false, 'error' => 'ID de post no especificado']);
    exit;
}

$posts = readJSON('../data/posts.json');
$post = null;

foreach ($posts as $p) {
    if ($p['id'] === $postId) {
        $post = $p;
        break;
    }
}

if (!$post) {
    echo json_encode(['success' => false, 'error' => 'Post no encontrado']);
    exit;
}

// Enriquecer post con información adicional
$userProfile = getUserProfile($post['user']);
$post['user_profile_pic'] = $userProfile['profile_pic'] ?? 'default.jpg';
$post['time_ago'] = timeAgo($post['timestamp']);
$post['like_count'] = getLikeCount($postId);
$post['comment_count'] = getCommentCount($postId);
$post['user_liked'] = hasUserLiked($postId, $currentUsername);

// Obtener comentarios
$comments = getPostComments($postId);
$post['comments'] = array_map(function($comment) {
    $comment['time_ago'] = timeAgo($comment['timestamp']);
    return $comment;
}, $comments);

echo json_encode(['success' => true, 'post' => $post]);
?>
