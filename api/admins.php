<?php
require_once __DIR__ . '/bootstrap.php';
$current = authed_admin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    $total = (int) apidb()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    $pages = (int) max(1, ceil($total / PER_PAGE));
    $page = min($page, $pages);
    $offset = ($page - 1) * PER_PAGE;

    $stmt = apidb()->prepare('SELECT id, names, email, phone, national_id, created_at FROM admins ORDER BY id DESC LIMIT :limit OFFSET :offset');
    $stmt->bindValue(':limit', PER_PAGE, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    respond(200, [
        'success' => true,
        'pagination' => ['page' => $page, 'per_page' => PER_PAGE, 'total' => $total, 'pages' => $pages],
        'data' => $stmt->fetchAll(),
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        'message' => 'Staff member added successfully.',
        'data' => ['id' => (int) apidb()->lastInsertId(), 'names' => $names, 'email' => $email],
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $id = resource_id();
    $existing = apidb()->prepare('SELECT id FROM admins WHERE id = ?');
    $existing->execute([$id]);
    if (!$existing->fetch()) {
        respond(404, ['success' => false, 'message' => 'Staff member not found.']);
    }

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
    if ($password !== '' && strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }
    if ($errors) {
        respond(422, ['success' => false, 'message' => 'Validation failed.', 'errors' => $errors]);
    }

    $check = apidb()->prepare('SELECT id FROM admins WHERE (email = ? OR national_id = ?) AND id <> ?');
    $check->execute([$email, $nationalId, $id]);
    if ($check->fetch()) {
        respond(409, ['success' => false, 'message' => 'Another admin with this email or national ID already exists.']);
    }

    if ($password !== '') {
        $stmt = apidb()->prepare('UPDATE admins SET names = ?, email = ?, phone = ?, national_id = ?, password = ? WHERE id = ?');
        $stmt->execute([$names, $email, $phone, $nationalId, password_hash($password, PASSWORD_DEFAULT), $id]);
    } else {
        $stmt = apidb()->prepare('UPDATE admins SET names = ?, email = ?, phone = ?, national_id = ? WHERE id = ?');
        $stmt->execute([$names, $email, $phone, $nationalId, $id]);
    }

    respond(200, [
        'success' => true,
        'message' => 'Staff member updated successfully.',
        'data' => ['id' => $id, 'names' => $names, 'email' => $email],
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = resource_id();

    if ($id === (int) $current['id']) {
        respond(409, ['success' => false, 'message' => 'You cannot delete your own account.']);
    }

    $existing = apidb()->prepare('SELECT id FROM admins WHERE id = ?');
    $existing->execute([$id]);
    if (!$existing->fetch()) {
        respond(404, ['success' => false, 'message' => 'Staff member not found.']);
    }

    $total = (int) apidb()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    if ($total <= 1) {
        respond(409, ['success' => false, 'message' => 'At least one staff account must remain.']);
    }

    $stmt = apidb()->prepare('DELETE FROM admins WHERE id = ?');
    $stmt->execute([$id]);

    respond(200, [
        'success' => true,
        'message' => 'Staff member deleted successfully.',
        'data' => ['id' => $id],
    ]);
}

respond(405, ['success' => false, 'message' => 'Method not allowed. Use GET, POST, PUT or DELETE.']);
