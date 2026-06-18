<?php
require_once __DIR__ . '/bootstrap.php';
authed_admin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    $total = (int) apidb()->query('SELECT COUNT(*) FROM clients')->fetchColumn();
    $pages = (int) max(1, ceil($total / PER_PAGE));
    $page = min($page, $pages);
    $offset = ($page - 1) * PER_PAGE;

    $stmt = apidb()->prepare('SELECT * FROM clients ORDER BY id DESC LIMIT :limit OFFSET :offset');
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
    $nationalId = trim($data['national_id'] ?? '');
    $telephone = trim($data['telephone'] ?? '');
    $address = trim($data['address'] ?? '');

    $errors = [];
    if ($names === '') {
        $errors['names'] = 'Names are required.';
    }
    if ($nationalId === '') {
        $errors['national_id'] = 'National ID is required.';
    }
    if ($telephone === '') {
        $errors['telephone'] = 'Telephone is required.';
    }
    if ($address === '') {
        $errors['address'] = 'Address is required.';
    }
    if ($errors) {
        respond(422, ['success' => false, 'message' => 'Validation failed.', 'errors' => $errors]);
    }

    $check = apidb()->prepare('SELECT id FROM clients WHERE national_id = ?');
    $check->execute([$nationalId]);
    if ($check->fetch()) {
        respond(409, ['success' => false, 'message' => 'A client with this national ID already exists.']);
    }

    $stmt = apidb()->prepare('INSERT INTO clients (names, national_id, telephone, address) VALUES (?, ?, ?, ?)');
    $stmt->execute([$names, $nationalId, $telephone, $address]);

    respond(201, [
        'success' => true,
        'message' => 'Client registered successfully.',
        'data' => ['id' => (int) apidb()->lastInsertId(), 'names' => $names, 'national_id' => $nationalId],
    ]);
}

respond(405, ['success' => false, 'message' => 'Method not allowed. Use GET or POST.']);
