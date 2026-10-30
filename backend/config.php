<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
$host     = 'localhost';
$dbname   = 'medical_decoder';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

function sendResponse(bool $success, $data = null, int $code = 200): void {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'data'    => $data,
        'timestamp' => date('c')
    ]);
    exit();
}

function getJsonBody(): array {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function requireAuth(): void {
    if (empty($_SESSION['user_id'])) {
        sendResponse(false, 'Authentication required. Please log in.', 401);
    }
}