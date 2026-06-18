<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function apidb(): PDO
{
    return Database::connect();
}

function body(): array
{
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (is_array($json)) {
        return $json;
    }
    return $_POST;
}

function respond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function require_method(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        respond(405, ['success' => false, 'message' => 'Method not allowed. Use ' . $method . '.']);
    }
}

function bearer_token(): ?string
{
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $auth = $headers['Authorization'] ?? $headers['authorization'] ?? ($_SERVER['HTTP_AUTHORIZATION'] ?? '');
    if (preg_match('/Bearer\s+(.+)/i', $auth, $m)) {
        return trim($m[1]);
    }
    return null;
}

function authed_admin(): array
{
    $token = bearer_token();
    if (!$token) {
        respond(401, ['success' => false, 'message' => 'Missing Bearer token. Log in via /api/login.php first.']);
    }
    $stmt = apidb()->prepare('SELECT id, names, email FROM admins WHERE api_token = ?');
    $stmt->execute([$token]);
    $admin = $stmt->fetch();
    if (!$admin) {
        respond(401, ['success' => false, 'message' => 'Invalid or expired token.']);
    }
    return $admin;
}
