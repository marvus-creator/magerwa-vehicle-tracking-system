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

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $id = resource_id();
    $existing = apidb()->prepare('SELECT id FROM clients WHERE id = ?');
    $existing->execute([$id]);
    if (!$existing->fetch()) {
        respond(404, ['success' => false, 'message' => 'Client not found.']);
    }

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

    $check = apidb()->prepare('SELECT id FROM clients WHERE national_id = ? AND id <> ?');
    $check->execute([$nationalId, $id]);
    if ($check->fetch()) {
        respond(409, ['success' => false, 'message' => 'Another client with this national ID already exists.']);
    }

    $stmt = apidb()->prepare('UPDATE clients SET names = ?, national_id = ?, telephone = ?, address = ? WHERE id = ?');
    $stmt->execute([$names, $nationalId, $telephone, $address, $id]);

    respond(200, [
        'success' => true,
        'message' => 'Client updated successfully.',
        'data' => ['id' => $id, 'names' => $names, 'national_id' => $nationalId],
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = resource_id();
    $existing = apidb()->prepare('SELECT id FROM clients WHERE id = ?');
    $existing->execute([$id]);
    if (!$existing->fetch()) {
        respond(404, ['success' => false, 'message' => 'Client not found.']);
    }

    // Free the plate on any vehicles linked to this client; the FK nulls
    // client_id on delete, but plate_number would otherwise linger.
    $clear = apidb()->prepare('UPDATE vehicles SET plate_number = NULL WHERE client_id = ?');
    $clear->execute([$id]);

    $stmt = apidb()->prepare('DELETE FROM clients WHERE id = ?');
    $stmt->execute([$id]);

    respond(200, [
        'success' => true,
        'message' => 'Client deleted successfully. Any linked vehicles were unlinked.',
        'data' => ['id' => $id],
    ]);
}

respond(405, ['success' => false, 'message' => 'Method not allowed. Use GET, POST, PUT or DELETE.']);
