<?php
require_once __DIR__ . '/bootstrap.php';
require_method('POST');

$data = body();
$names = trim($data['names'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$nationalId = trim($data['national_id'] ?? '');
$password = (string) ($data['password'] ?? '');

$errors = [];
if ($names === '') {
    $errors['names'] = 'Names are required.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'A valid email is required.';
}
if ($phone === '') {
    $errors['phone'] = 'Phone is required.';
}
if ($nationalId === '') {
    $errors['national_id'] = 'National ID is required.';
}
if (strlen($password) < 6) {
    $errors['password'] = 'Password must be at least 6 characters.';
}

if ($errors) {
    respond(422, ['success' => false, 'message' => 'Validation failed.', 'errors' => $errors]);
}

$check = apidb()->prepare('SELECT id FROM admins WHERE email = ? OR national_id = ?');
$check->execute([$email, $nationalId]);
if ($check->fetch()) {
    respond(409, ['success' => false, 'message' => 'An admin with this email or national ID already exists.']);
}

$stmt = apidb()->prepare('INSERT INTO admins (names, email, phone, national_id, password) VALUES (?, ?, ?, ?, ?)');
$stmt->execute([$names, $email, $phone, $nationalId, password_hash($password, PASSWORD_DEFAULT)]);

respond(201, [
    'success' => true,
    'message' => 'Admin registered successfully.',
    'data' => ['id' => (int) apidb()->lastInsertId(), 'names' => $names, 'email' => $email],
]);
