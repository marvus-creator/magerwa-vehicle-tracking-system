<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function db(): PDO
{
    return Database::connect();
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function clean(?string $value): string
{
    return trim((string) $value);
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['admin_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('warning', 'Please log in to continue.');
        redirect('auth/login.php');
    }
}

function current_admin(): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    $stmt = db()->prepare('SELECT id, names, email FROM admins WHERE id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();
    return $admin ?: null;
}

function paginate(int $total, int $perPage, int $page): array
{
    $pages = (int) max(1, ceil($total / $perPage));
    $page = (int) min(max(1, $page), $pages);
    $offset = ($page - 1) * $perPage;
    return ['page' => $page, 'pages' => $pages, 'offset' => $offset, 'total' => $total];
}

function current_page(): int
{
    return isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
}
