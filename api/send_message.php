<?php
session_start();
include "../includes/functions.php";

header('Content-Type: application/json');

if (!currentUser()) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = $_POST['to'] ?? '';
    $message = trim($_POST['message'] ?? '');
    $currentUsername = currentUser();
    
    // Verificar que son amigos
    $friends = getUserFriends($currentUsername);
    if (!in_array($to, $friends)) {
        echo json_encode(['success' => false, 'error' => 'No son amigos']);
        exit;
    }
    
    $newMessage = [
        'id' => uniqid(),
        'from' => $currentUsername,
        'to' => $to,
        'message' => $message,
        'timestamp' => time(),
        'read' => false,
        'file' => '',
        'type' => ''
    ];
    
    // Manejar archivo si existe
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/uploads/chat/';
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        if (in_array($_FILES['file']['type'], $allowedTypes)) {
            $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $filename)) {
                $newMessage['file'] = $filename;
                $newMessage['type'] = 'image';
            }
        }
    }
    
    $messages = readJSON('../data/messages.json');
    $messages[] = $newMessage;
    writeJSON('../data/messages.json', $messages);
    
    echo json_encode(['success' => true, 'message' => $newMessage]);
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
?>
