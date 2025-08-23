<?php
session_start();
include "../includes/functions.php";

header('Content-Type: application/json');

if (!currentUser()) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode(['success' => false, 'error' => 'Query muy corto']);
    exit;
}

$users = searchUsers($query);
$currentUsername = currentUser();

// Filtrar el usuario actual de los resultados
$filteredUsers = array_filter($users, function($user) use ($currentUsername) {
    return $user['username'] !== $currentUsername;
});

echo json_encode(['success' => true, 'users' => array_values($filteredUsers)]);
?>
