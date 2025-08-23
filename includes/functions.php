<?php
function readJSON($file) {
    if (!file_exists($file)) {
        return [];
    }
    $content = file_get_contents($file);
    return json_decode($content, true) ?: [];
}

function writeJSON($file, $data) {
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function currentUser() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

function getUserByUsername($username) {
    $users = readJSON('data/users.json');
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            return $user;
        }
    }
    return null;
}

function getUserProfile($username) {
    return getUserByUsername($username);
}

function getUserPosts($username) {
    $posts = readJSON('data/posts.json');
    $userPosts = [];
    
    foreach ($posts as $post) {
        if ($post['user'] === $username) {
            $userPosts[] = $post;
        }
    }
    
    // Ordenar por timestamp descendente
    usort($userPosts, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
    
    return $userPosts;
}

function getUserFriends($username) {
    $friendships = readJSON('data/friends.json');
    $friends = [];
    
    foreach ($friendships as $friendship) {
        if ($friendship['status'] === 'accepted') {
            if ($friendship['user1'] === $username) {
                $friends[] = $friendship['user2'];
            } elseif ($friendship['user2'] === $username) {
                $friends[] = $friendship['user1'];
            }
        }
    }
    
    return $friends;
}

function getFriendRequests($username) {
    $friendships = readJSON('data/friends.json');
    $requests = [];
    
    foreach ($friendships as $friendship) {
        if ($friendship['status'] === 'pending' && $friendship['user2'] === $username) {
            $requests[] = [
                'from' => $friendship['user1'],
                'timestamp' => $friendship['timestamp']
            ];
        }
    }
    
    return $requests;
}

function getSentFriendRequests($username) {
    $friendships = readJSON('data/friends.json');
    $requests = [];
    
    foreach ($friendships as $friendship) {
        if ($friendship['status'] === 'pending' && $friendship['user1'] === $username) {
            $requests[] = [
                'to' => $friendship['user2'],
                'timestamp' => $friendship['timestamp']
            ];
        }
    }
    
    return $requests;
}

function sendFriendRequest($from, $to) {
    if ($from === $to) return false;
    
    $friendships = readJSON('data/friends.json');
    
    // Verificar si ya existe una relación
    foreach ($friendships as $friendship) {
        if (($friendship['user1'] === $from && $friendship['user2'] === $to) ||
            ($friendship['user1'] === $to && $friendship['user2'] === $from)) {
            return false; // Ya existe
        }
    }
    
    $friendships[] = [
        'user1' => $from,
        'user2' => $to,
        'status' => 'pending',
        'timestamp' => time()
    ];
    
    writeJSON('data/friends.json', $friendships);
    return true;
}

function acceptFriendRequest($from, $to) {
    $friendships = readJSON('data/friends.json');
    
    foreach ($friendships as $key => $friendship) {
        if ($friendship['user1'] === $from && $friendship['user2'] === $to && 
            $friendship['status'] === 'pending') {
            $friendships[$key]['status'] = 'accepted';
            writeJSON('data/friends.json', $friendships);
            return true;
        }
    }
    
    return false;
}

function rejectFriendRequest($from, $to) {
    $friendships = readJSON('data/friends.json');
    
    foreach ($friendships as $key => $friendship) {
        if ($friendship['user1'] === $from && $friendship['user2'] === $to && 
            $friendship['status'] === 'pending') {
            unset($friendships[$key]);
            writeJSON('data/friends.json', array_values($friendships));
            return true;
        }
    }
    
    return false;
}

function removeFriend($user1, $user2) {
    $friendships = readJSON('data/friends.json');
    
    foreach ($friendships as $key => $friendship) {
        if (($friendship['user1'] === $user1 && $friendship['user2'] === $user2) ||
            ($friendship['user1'] === $user2 && $friendship['user2'] === $user1)) {
            unset($friendships[$key]);
            writeJSON('data/friends.json', array_values($friendships));
            return true;
        }
    }
    
    return false;
}

function hasUserLiked($postId, $username) {
    $likes = readJSON('data/likes.json');
    
    foreach ($likes as $like) {
        if ($like['post_id'] === $postId && $like['user'] === $username) {
            return true;
        }
    }
    
    return false;
}

function getLikeCount($postId) {
    $likes = readJSON('data/likes.json');
    $count = 0;
    
    foreach ($likes as $like) {
        if ($like['post_id'] === $postId) {
            $count++;
        }
    }
    
    return $count;
}

function getPostComments($postId) {
    $comments = readJSON('data/comments.json');
    $postComments = [];
    
    foreach ($comments as $comment) {
        if ($comment['post_id'] === $postId) {
            $postComments[] = $comment;
        }
    }
    
    // Ordenar por timestamp
    usort($postComments, function($a, $b) {
        return $a['timestamp'] - $b['timestamp'];
    });
    
    return $postComments;
}

function getCommentCount($postId) {
    $comments = readJSON('data/comments.json');
    $count = 0;
    
    foreach ($comments as $comment) {
        if ($comment['post_id'] === $postId) {
            $count++;
        }
    }
    
    return $count;
}

function getFriendSuggestions($username) {
    $users = readJSON('data/users.json');
    $friends = getUserFriends($username);
    $suggestions = [];
    
    foreach ($users as $user) {
        if ($user['username'] !== $username && !in_array($user['username'], $friends)) {
            $suggestions[] = $user;
        }
    }
    
    // Limitar a 5 sugerencias
    return array_slice($suggestions, 0, 5);
}

function timeAgo($timestamp) {
    $time = time() - $timestamp;
    
    if ($time < 60) return 'hace ' . $time . ' segundos';
    if ($time < 3600) return 'hace ' . floor($time/60) . ' minutos';
    if ($time < 86400) return 'hace ' . floor($time/3600) . ' horas';
    if ($time < 2592000) return 'hace ' . floor($time/86400) . ' días';
    if ($time < 31536000) return 'hace ' . floor($time/2592000) . ' meses';
    return 'hace ' . floor($time/31536000) . ' años';
}

function searchUsers($query) {
    $users = readJSON('data/users.json');
    $results = [];
    
    foreach ($users as $user) {
        if (stripos($user['username'], $query) !== false || 
            stripos($user['full_name'], $query) !== false) {
            $results[] = $user;
        }
    }
    
    return $results;
}
?>
