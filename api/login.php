<?php
require_once __DIR__ . '/bootstrap.php';
require_method('POST');

$data = body();
$email = trim($data['email'] ?? '');
$password = (string) ($data['password'] ?? '');

if ($email === '' || $password === '') {
    respond(422, ['success' => false, 'message' => 'Email and password are required.']);
}

$stmt = apidb()->prepare('SELECT * FROM admins WHERE email = ?');
$stmt->execute([$email]);
$admin = $stmt->fetch();

if (!$admin || !password_verify($password, $admin['password'])) {
    respond(401, ['success' => false, 'message' => 'Invalid email or password.']);
}

$token = bin2hex(random_bytes(32));
$update = apidb()->prepare('UPDATE admins SET api_token = ? WHERE id = ?');
$update->execute([$token, $admin['id']]);

respond(200, [
    'success' => true,
    'message' => 'Login successful.',
    'token' => $token,
    'admin' => ['id' => (int) $admin['id'], 'names' => $admin['names'], 'email' => $admin['email']],
]);
