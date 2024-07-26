<?php

function generateToken($userId) {
    $payload = [
        'user_id' => $userId,
        'exp' => time() + 3600  // Token expires in 1 hour
    ];
    return base64_encode(json_encode($payload));
}

function validateToken($token) {
    $payload = json_decode(base64_decode($token), true);
    if (!$payload || !isset($payload['user_id']) || !isset($payload['exp'])) {
        return false;
    }
    if ($payload['exp'] < time()) {
        return false;
    }
    return $payload['user_id'];
}

function authenticateRequest() {
    $headers = [];
    foreach ($_SERVER as $key => $value) {
        if (substr($key, 0, 5) === 'HTTP_') {
            $header = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
            $headers[$header] = $value;
        }
    }

    if (!isset($headers['Authorization'])) {
        return false;
    }

    $token = str_replace('Bearer ', '', $headers['Authorization']);
    return validateToken($token);
}

function authenticateUser($username, $password) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT id, password FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        return $user['id'];
    }
    return false;
}
