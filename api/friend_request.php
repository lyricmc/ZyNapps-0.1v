<?php
session_start();
include "../includes/functions.php";

header('Content-Type: application/json');

if (!currentUser()) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $currentUsername = currentUser();
    
    switch ($action) {
        case 'send':
            $to = $_POST['to'] ?? '';
            if (sendFriendRequest($currentUsername, $to)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No se pudo enviar la solicitud']);
            }
            break;
            
        case 'accept':
            $from = $_POST['from'] ?? '';
            if (acceptFriendRequest($from, $currentUsername)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No se pudo aceptar la solicitud']);
            }
            break;
            
        case 'reject':
            $from = $_POST['from'] ?? '';
            if (rejectFriendRequest($from, $currentUsername)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No se pudo rechazar la solicitud']);
            }
            break;
            
        case 'remove':
            $user = $_POST['user'] ?? '';
            if (removeFriend($currentUsername, $user)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'No se pudo eliminar el amigo']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Acción no válida']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
?>
