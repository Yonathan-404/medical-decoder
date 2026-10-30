<?php

require_once '../config.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        $body = getJsonBody();
        if (empty($body['username']) || empty($body['email']) || empty($body['password'])) {
            sendResponse(false, 'Username, email, and password are required.', 400);
        }

        $username = trim($body['username']);
        $email    = trim($body['email']);
        $password = $body['password'];
        if (strlen($password) < 6) {
            sendResponse(false, 'Password must be at least 6 characters.', 400);
        }
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        if ($check->fetch()) {
            sendResponse(false, 'Username or email already taken.', 409);
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hash]);
        $userId = $pdo->lastInsertId();
        $profile = $pdo->prepare("INSERT INTO user_profiles (user_id) VALUES (?)");
        $profile->execute([$userId]);
        $_SESSION['user_id'] = $userId;

        sendResponse(true, [
            'id'       => $userId,
            'username' => $username,
            'email'    => $email,
            'message'  => 'Account created successfully!'
        ]);
        break;
    case 'login':
        $body = getJsonBody();

        if (empty($body['username']) || empty($body['password'])) {
            sendResponse(false, 'Username and password are required.', 400);
        }

        $username = trim($body['username']);
        $password = $body['password'];
        $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            sendResponse(false, 'Invalid username or password.', 401);
        }
        if (!password_verify($password, $user['password_hash'])) {
            sendResponse(false, 'Invalid username or password.', 401);
        }
        $_SESSION['user_id'] = $user['id'];

        sendResponse(true, [
            'id'       => $user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
            'message'  => 'Login successful!'
        ]);
        break;
    case 'logout':
        session_destroy();
        sendResponse(true, 'Logged out successfully.');
        break;
    case 'me':
        requireAuth();

        $stmt = $pdo->prepare("
            SELECT u.id, u.username, u.email, u.created_at,
                   p.age, p.gender, p.height_cm, p.weight_kg
            FROM users u
            LEFT JOIN user_profiles p ON u.id = p.user_id
            WHERE u.id = ?
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user) {
            sendResponse(false, 'User not found.', 404);
        }

        sendResponse(true, $user);
        break;

    default:
        sendResponse(false, 'Invalid action. Use: register, login, logout, me', 400);
}