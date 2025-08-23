<?php
session_start();
include "../includes/functions.php";

header('Content-Type: application/json');

if (!currentUser()) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$currentUsername = currentUser();
$chatWith = $_GET['with'] ?? '';

if (empty($chatWith)) {
    echo json_encode(['success' => false, 'error' => 'Usuario no especificado']);
    exit;
}

// Verificar que son amigos
$friends = getUserFriends($currentUsername);
if (!in_array($chatWith, $friends)) {
    echo json_encode(['success' => false, 'error' => 'No son amigos']);
    exit;
}

$messages = readJSON('../data/messages.json');
$chatMessages = [];

foreach ($messages as $message) {
    if (($message['from'] === $currentUsername && $message['to'] === $chatWith) ||
        ($message['from'] === $chatWith && $message['to'] === $currentUsername)) {
        $chatMessages[] = $message;
    }
}

// Ordenar por timestamp
usort($chatMessages, function($a, $b) {
    return $a['timestamp'] - $b['timestamp'];
});

echo json_encode(['success' => true, 'messages' => $chatMessages]);
?>
