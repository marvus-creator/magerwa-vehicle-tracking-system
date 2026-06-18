<?php
require_once __DIR__ . '/bootstrap.php';
authed_admin();

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = resource_id();
    $vehicle = apidb()->prepare('SELECT id FROM vehicles WHERE id = ? AND client_id IS NOT NULL');
    $vehicle->execute([$id]);
    if (!$vehicle->fetch()) {
        respond(404, ['success' => false, 'message' => 'Linked record not found.']);
    }

    $stmt = apidb()->prepare('UPDATE vehicles SET client_id = NULL, plate_number = NULL WHERE id = ?');
    $stmt->execute([$id]);

    respond(200, [
        'success' => true,
        'message' => 'Vehicle unlinked successfully. It remains registered and can be linked again.',
        'data' => ['vehicle_id' => $id],
    ]);
}

require_method('POST');

$data = body();
$vehicleId = (int) ($data['vehicle_id'] ?? 0);
$clientId = (int) ($data['client_id'] ?? 0);
$plate = strtoupper(trim($data['plate_number'] ?? ''));

$errors = [];
if ($vehicleId <= 0) {
    $errors['vehicle_id'] = 'vehicle_id is required.';
}
if ($clientId <= 0) {
    $errors['client_id'] = 'client_id is required.';
}
if ($plate === '') {
    $errors['plate_number'] = 'plate_number is required.';
}
if ($errors) {
    respond(422, ['success' => false, 'message' => 'Validation failed.', 'errors' => $errors]);
}

$vehicle = apidb()->prepare('SELECT id FROM vehicles WHERE id = ?');
$vehicle->execute([$vehicleId]);
if (!$vehicle->fetch()) {
    respond(404, ['success' => false, 'message' => 'Vehicle not found.']);
}

$client = apidb()->prepare('SELECT id FROM clients WHERE id = ?');
$client->execute([$clientId]);
if (!$client->fetch()) {
    respond(404, ['success' => false, 'message' => 'Client not found.']);
}

$check = apidb()->prepare('SELECT id FROM vehicles WHERE plate_number = ? AND id <> ?');
$check->execute([$plate, $vehicleId]);
if ($check->fetch()) {
    respond(409, ['success' => false, 'message' => 'This plate number is already assigned.']);
}

$stmt = apidb()->prepare('UPDATE vehicles SET client_id = ?, plate_number = ? WHERE id = ?');
$stmt->execute([$clientId, $plate, $vehicleId]);

respond(200, [
    'success' => true,
    'message' => 'Vehicle linked to client successfully.',
    'data' => ['vehicle_id' => $vehicleId, 'client_id' => $clientId, 'plate_number' => $plate],
]);
