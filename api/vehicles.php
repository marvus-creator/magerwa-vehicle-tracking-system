<?php
require_once __DIR__ . '/bootstrap.php';
authed_admin();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    $total = (int) apidb()->query('SELECT COUNT(*) FROM vehicles')->fetchColumn();
    $pages = (int) max(1, ceil($total / PER_PAGE));
    $page = min($page, $pages);
    $offset = ($page - 1) * PER_PAGE;

    $stmt = apidb()->prepare(
        'SELECT v.*, c.names AS client_name FROM vehicles v
         LEFT JOIN clients c ON v.client_id = c.id
         ORDER BY v.id DESC LIMIT :limit OFFSET :offset'
    );
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
    $chassis = trim($data['chassis_number'] ?? '');
    $company = trim($data['manufacture_company'] ?? '');
    $year = trim((string) ($data['manufacture_year'] ?? ''));
    $price = trim((string) ($data['price'] ?? ''));
    $model = trim($data['model_name'] ?? '');

    $errors = [];
    if ($chassis === '') {
        $errors['chassis_number'] = 'Chassis number is required.';
    }
    if ($company === '') {
        $errors['manufacture_company'] = 'Manufacture company is required.';
    }
    if (!ctype_digit($year) || (int) $year < 1900 || (int) $year > (int) date('Y') + 1) {
        $errors['manufacture_year'] = 'Valid manufacture year is required.';
    }
    if (!is_numeric($price) || (float) $price < 0) {
        $errors['price'] = 'Valid price is required.';
    }
    if ($model === '') {
        $errors['model_name'] = 'Model name is required.';
    }
    if ($errors) {
        respond(422, ['success' => false, 'message' => 'Validation failed.', 'errors' => $errors]);
    }

    $check = apidb()->prepare('SELECT id FROM vehicles WHERE chassis_number = ?');
    $check->execute([$chassis]);
    if ($check->fetch()) {
        respond(409, ['success' => false, 'message' => 'A vehicle with this chassis number already exists.']);
    }

    $stmt = apidb()->prepare('INSERT INTO vehicles (chassis_number, manufacture_company, manufacture_year, price, model_name) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$chassis, $company, (int) $year, (float) $price, $model]);

    respond(201, [
        'success' => true,
        'message' => 'Vehicle registered successfully.',
        'data' => ['id' => (int) apidb()->lastInsertId(), 'chassis_number' => $chassis, 'model_name' => $model],
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $id = resource_id();
    $existing = apidb()->prepare('SELECT id FROM vehicles WHERE id = ?');
    $existing->execute([$id]);
    if (!$existing->fetch()) {
        respond(404, ['success' => false, 'message' => 'Vehicle not found.']);
    }

    $data = body();
    $chassis = trim($data['chassis_number'] ?? '');
    $company = trim($data['manufacture_company'] ?? '');
    $year = trim((string) ($data['manufacture_year'] ?? ''));
    $price = trim((string) ($data['price'] ?? ''));
    $model = trim($data['model_name'] ?? '');

    $errors = [];
    if ($chassis === '') {
        $errors['chassis_number'] = 'Chassis number is required.';
    }
    if ($company === '') {
        $errors['manufacture_company'] = 'Manufacture company is required.';
    }
    if (!ctype_digit($year) || (int) $year < 1900 || (int) $year > (int) date('Y') + 1) {
        $errors['manufacture_year'] = 'Valid manufacture year is required.';
    }
    if (!is_numeric($price) || (float) $price < 0) {
        $errors['price'] = 'Valid price is required.';
    }
    if ($model === '') {
        $errors['model_name'] = 'Model name is required.';
    }
    if ($errors) {
        respond(422, ['success' => false, 'message' => 'Validation failed.', 'errors' => $errors]);
    }

    $check = apidb()->prepare('SELECT id FROM vehicles WHERE chassis_number = ? AND id <> ?');
    $check->execute([$chassis, $id]);
    if ($check->fetch()) {
        respond(409, ['success' => false, 'message' => 'Another vehicle with this chassis number already exists.']);
    }

    $stmt = apidb()->prepare('UPDATE vehicles SET chassis_number = ?, manufacture_company = ?, manufacture_year = ?, price = ?, model_name = ? WHERE id = ?');
    $stmt->execute([$chassis, $company, (int) $year, (float) $price, $model, $id]);

    respond(200, [
        'success' => true,
        'message' => 'Vehicle updated successfully.',
        'data' => ['id' => $id, 'chassis_number' => $chassis, 'model_name' => $model],
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = resource_id();
    $existing = apidb()->prepare('SELECT id FROM vehicles WHERE id = ?');
    $existing->execute([$id]);
    if (!$existing->fetch()) {
        respond(404, ['success' => false, 'message' => 'Vehicle not found.']);
    }

    $stmt = apidb()->prepare('DELETE FROM vehicles WHERE id = ?');
    $stmt->execute([$id]);

    respond(200, [
        'success' => true,
        'message' => 'Vehicle deleted successfully.',
        'data' => ['id' => $id],
    ]);
}

respond(405, ['success' => false, 'message' => 'Method not allowed. Use GET, POST, PUT or DELETE.']);
