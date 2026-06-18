<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('vehicles/linked.php');
}

$id = (int) ($_POST['id'] ?? 0);

$stmt = db()->prepare('UPDATE vehicles SET client_id = NULL, plate_number = NULL WHERE id = ? AND client_id IS NOT NULL');
$stmt->execute([$id]);

if ($stmt->rowCount() > 0) {
    set_flash('success', 'Vehicle unlinked successfully. It remains registered and can be linked again.');
} else {
    set_flash('danger', 'Linked record not found.');
}
redirect('vehicles/linked.php');
